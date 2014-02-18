<?php
/**
 * TechDivision\ServletContainer\RequestHandler
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
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
use TechDivision\ServletContainer\Exceptions\BadRequestException;

/**
 * The thread implementation that handles the request.
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class RequestHandler extends AbstractContextThread
{
    
    /**
     * The maximum number of requests handled by the Keep-Alive functionality.
     * 
     * @var integer
     */
    const AVAILABLE_REQUESTS = 5;
    
    /**
     * The timeout before the Keep-Alive functionality closes the socket connection.
     * 
     * @var integer
     */
    const RECEIVE_TIMEOUT = 5;

    /**
     * Holds the container instance.
     *
     * @var Container
     */
    public $container;

    /**
     * Holds the main socket resource.
     *
     * @var resource
     */
    public $resource;

    /**
     * The HTTP client to handle the request.
     *
     * @var \TechDivision\ServletContainer\Interfaces\HttpClientInterface
     */
    protected $client;

    /**
     * Initializes the request with the client socket.
     *
     * @param \TechDivision\ServletContainer\Container                      $container The ServletContainer
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client    The HTTP client to handle the request
     * @param resource                                                      $resource  The client socket instance
     *
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
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client   The HTTP client to handle the request
     * @param \TechDivision\ServletContainer\Interfaces\Response            $response The response to send to the client
     *
     * @return void
     */
    public function send(HttpClientInterface $client, Response $response)
    {
        // prepare the content to be ready for sending to client
        $response->prepareContent();

        // prepare the headers
        $response->prepareHeaders();

        // return the string representation of the response content to the client
        $client->send($response->getHeadersAsString() . "\r\n" . $response->getContent());
    }

    /**
     * The thread implementation main method which will be called from run in abstractness
     *
     * @return void
     */
    public function main()
    {
        try {
            
            // initialize variables to handle persistent HTTP/1.1 connections
            $counter = 1;
            $connectionOpen = true;
            $startTime = time();
            $availableRequests = RequestHandler::AVAILABLE_REQUESTS;
            
            // set the client socket resource and timeout
            $client = $this->getClient();
            $client->setResource($resource = $this->getResource());
            $client->setReceiveTimeout($receiveTimeout = RequestHandler::RECEIVE_TIMEOUT);
            
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
                    if ($availableRequests >= 0) {
                        
                        // set the ttl (how long the connection will still be open before closed by the server)
                        $ttl = ($startTime + $receiveTimeout) - time();
                        
                        // add the apropriate response header
                        $response->addHeader('Keep-Alive', "max=$availableRequests, timeout=$ttl, thread={$this->getThreadId()}");
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

                // inject authentication manager
                $servlet->injectAuthenticationManager($this->newInstance('TechDivision\ServletContainer\AuthenticationManager', array()));

                // let the servlet process the request send it back to the client
                $servlet->service($request, $response);
                $this->send($client, $response);
                
                // check if this is the last request
                if ($availableRequests < 1) {
                    $connectionOpen = false;
                }
                
            } while ($connectionOpen);
            
        } catch (ConnectionClosedByPeerException $ccbpe) { // socket closed by client/browser
            $this->getInitialContext()->getSystemLogger()->debug($ccbpe);
        } catch (SocketException $soe) { // socket timeout reached
            $this->getInitialContext()->getSystemLogger()->debug($soe);
        } catch (StreamException $ste) { // streaming socket timeout reached
            $this->getInitialContext()->getSystemLogger()->debug($ste);
        } catch (BadRequestException $bre) { // servlet can not be found
            $this->getInitialContext()->getSystemLogger()->error($bre);
            // if the resource is available, send the stacktrace back to the client
            if (is_resource($resource)) {
                // prepare stacktrace and send it back
                $response->setContent('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL ' . $request->getUri() . ' was not found on this server.</p></body></html>');
                $response->addHeader(HttpResponse::HEADER_NAME_STATUS, 'HTTP/1.1 404 Not Found');
                $this->send($client, $response);
                // shutdown + close the client connection
                $client->shutdown();
                $client->close();
            }
        } catch (\Exception $e) { // a servlet throws an exception -> pass it through to the client!
            $this->getInitialContext()->getSystemLogger()->error($e);
            // if the resource is available, send the stacktrace back to the client
            if (is_resource($resource)) {
                // prepare stacktrace and send it back
                $response->setContent('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN"><html><head><title>505 Internal Server Error</title></head><body><h1>Internal Server Error</h1><p>' . $e->__toString() . '</p></body></html>');
                $response->addHeader(HttpResponse::HEADER_NAME_STATUS, 'HTTP/1.1 500 Internal Server Error');
                $this->send($client, $response);
                // shutdown + close the client connection
                $client->shutdown();
                $client->close();
            }
        }
    }
    
    /**
     * Returns the main socket resource.
     * 
     * @return resource The main socket resource
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * Returns the HTTP client to handle the request.
     * 
     * @return \TechDivision\ServletContainer\Interfaces\HttpClientInterface The client instance
     */
    public function getClient()
    {
        return $this->client;
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
     * Tries to find and return the application for the passed request.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $servletRequest The request to find and return the application instance for
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application can be found for the passed application name
     */
    public function findApplication($servletRequest)
    {
        return $this->getContainer()->findApplication($servletRequest);
    }
}
