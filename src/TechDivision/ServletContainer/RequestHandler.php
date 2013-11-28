<?php

/**
 * TechDivision\ServletContainer\RequestHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractContextThread;
use TechDivision\ServletContainer\Http\AccessLogger;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\ServletContainer\Http\HttpResponse;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Exceptions\ConnectionClosedByPeerException;
use TechDivision\SocketException;
use TechDivision\StreamException;

/**
 * The thread implementation that handles the request.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class RequestHandler extends AbstractContextThread
{

    /**
     * Holds the container instance
     *
     * @var Container
     */
    public $container;

    /**
     * Holds the main socket resource
     *
     * @var resource
     */
    public $resource;

    /**
     * The HTTP client to handle the request.
     *
     * @var The HTTP client to handle the request
     */
    protected $client;

    /**
     * Initializes the request with the client socket.
     *
     * @param \TechDivision\ServletContainer\Container $container
     *            The ServletContainer
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client
     *            The HTTP client to handle the request
     * @param resource $resource
     *            The client socket instance
     * @return void
     */
    public function init(Container $container, $client, $resource)
    {
        $this->container = $container;
        $this->client = $client;
        $this->resource = $resource;
    }

    /**
     * Sends the response of the request back to the passed client.
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client
     *            The HTTP client to handle the request
     * @param \TechDivision\ServletContainer\Interfaces\Response $response
     *            The response to send to the client
     * @return void
     */
    public function send(HttpClientInterface $client, Response $response)
    {
        // prepare the headers
        $response->prepareHeaders();
        
        // return the string representation of the response content to the client
        $client->send($response->getHeadersAsString() . "\r\n" . $response->getContent());
    }

    /**
     * Shutdown and close the client connection.
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client
     *            The HTTP client to handle the request
     * @return void
     */
    public function close(HttpClientInterface $client)
    {
        // try to shutdown client socket
        try {
            $client->shutdown();
            $client->close();
        } catch (\Exception $e) {
            // $client->close();
            error_log($e->getMessage());
        }
    }

    /**
     *
     * @see AbstractThread::main()
     */
    public function main()
    {
        try {
            
            // initialize variables to handle persistent HTTP/1.1 connections
            $counter = 1;
            $connectionOpen = true;
            $startTime = time();
            $availableRequests = 5;
            
            // set the client socket resource and timeout
            $client = $this->client;
            $client->setResource($this->resource);
            $client->setReceiveTimeout($receiveTimeout = 5);
            
            do { // let socket open as long as max request or socket timeout is not reached
                
                // receive request object from client
                $request = $client->receive();
                
                // initialize response, set the actual date and add accepted encoding methods
                $responseDate = gmdate('D, d M Y H:i:s \G\M\T', time());
                $response = $request->getResponse();
                $response->initHeaders();
                $response->setAcceptedEncodings($request->getAcceptedEncodings());
                $response->addHeader(HttpResponse::HEADER_NAME_STATUS, "{$request->getVersion()} 200 OK");
                
                // load the Connection Header (keep-alive/close)
                $connection = strtolower($request->getHeader('Connection'));
                
                // check protocol version
                if ($connection === 'keep-alive' && $request->getVersion() === 'HTTP/1.1') {
                    
                    // lower the request counter and the TTL
                    $availableRequests --;
                    
                    // check if this will be the last requests handled by this thread
                    if ($availableRequests > 0) {
                        $response->addHeader('Keep-Alive', "max=$availableRequests, timeout=$receiveTimeout, thread={$this->getThreadId()}");
                    }
                } else { // set request counter and TTL to 0
                    $availableRequests = 0;
                }
                
                // log the request
                $this->getAccessLogger()->log($request, $response);
                
                // load the application to handle the request
                $application = $this->findApplication($request);
                
                // try to locate a servlet which could service the current request
                $servlet = $application->locate($request);
                
                // inject shutdown handler
                $servlet->injectShutdownHandler($this->newInstance('TechDivision\ServletContainer\Servlets\DefaultShutdownHandler', array(
                    $client,
                    $response
                )));
                
                // let the servlet process the request send it back to the client
                $servlet->service($request, $response);
                $this->send($client, $response);
                
                // check if this is the last request
                if ($availableRequests < 1) {
                    $connectionOpen = false;
                }
                
            } while ($connectionOpen);
            
        } catch (ConnectionClosedByPeerException $ccbpe) { // socket closed by client/browser
            $this->getInitialContext()->getSystemLogger()->addError($ccbpe);
            $this->close($client);
        } catch (SocketException $soe) { // socket timeout reached
            $this->getInitialContext()->getSystemLogger()->addError($soe);
        } catch (StreamException $ste) { // streaming socket timeout reached
            $this->getInitialContext()->getSystemLogger()->addError($ste);
        } catch (\Exception $e) { // something bad happened
            $this->getInitialContext()->getSystemLogger()->addError($e);
            $response->setContent($e->__toString());
            $this->send($client, $response);
        }
    }

    /**
     * Returns the access logger instance.
     *
     * @return \TechDivision\ServletContainer\Http\AccessLogger The initialized access logger instance
     */
    public function getAccessLogger()
    {
        return $this->getContainer()->getAccessLogger();
    }

    /**
     * Returns the container instance.
     *
     * @return \TechDivision\ServletContainer\Container The container instance
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns the array with the available applications.
     *
     * @return array The available applications
     */
    public function getApplications()
    {
        return $this->getContainer()->getApplications();
    }

    /**
     * Tries to find the application that has to handle the
     * passed request.
     *
     * @see \TechDivision\ServletContainer\Application::findApplication($servletRequest)
     */
    public function findApplication($servletRequest)
    {
        return $this->getContainer()->findApplication($servletRequest);
    }
}