<?php

/**
 * TechDivision\ServletContainer\RequestHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\SocketException;
use TechDivision\StreamException;
use TechDivision\ApplicationServer\AbstractContextThread;
use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Http\AccessLogger;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\ServletContainer\Http\HttpResponse;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Exceptions\ConnectionClosedByPeerException;
use TechDivision\ServletContainer\Exceptions\BadRequestException;

/**
 * The thread implementation that handles the request.
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
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
     * Holds the array with the available applications.
     *
     * @var array
     */
    public $applications;
    
    /**
     * Holds the access logger instance.
     * 
     * @var \TechDivision\ServletContainer\Http\AccessLogger
     */
    public $accessLogger;

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
        
        // load applications and the access logger from container
        $this->applications = $container->getApplications();
        $this->accessLogger = $container->getAccessLogger();
        
        // initialize socket client/resource
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
     * Tries to find an application that matches the passed request.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance to locate the application for
     * 
     * @return array The application info that matches the request
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application matches the request
     */
    public function locate(Request $request)
    {
        
        // prepare the URI to be matched
        $url = $request->getServerName() . $request->getUri();
        
        // try to find the application by match it one of the prepared patterns
        foreach ($this->getApplications() as $pattern => $applicationInfo) {
        
            // try to match a registered application with the passed request
            if (preg_match($pattern, $url) === 1) {
                return $applicationInfo;
            }
        }
        
        // if not throw a bad request exception
        throw new BadRequestException(
            sprintf(
                "Can't find application for URI %s",
                $request->getUri()
            )
        );
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
            
            // initialize the access logger
            $accessLogger = $this->getAccessLogger();
            
            // set the client socket resource and timeout
            $client = $this->getClient();
            $client->setResource($resource = $this->getResource());
            $client->setReceiveTimeout($receiveTimeout = RequestHandler::RECEIVE_TIMEOUT);
            
            // initialize the session manager itself
            $sessionManager = $this->newInstance(
                'TechDivision\ServletContainer\Session\PersistentSessionManager',
                array($this->getInitialContext())
            );
            
            // initialize the authentication manager
            $authenticationManager = $this->newInstance(
                'TechDivision\ServletContainer\AuthenticationManager'
            );
            
            do { // let socket open as long as max request or socket timeout is not reached
                
                // receive request object from client
                $request = $client->receive();
                
                // initialize response, set the actual date and add accepted encoding methods
                $responseDate = gmdate('D, d M Y H:i:s \G\M\T', time());
                $response = $request->getResponse();
                $response->initHeaders();
                $response->setAcceptedEncodings($request->getAcceptedEncodings());
                $response->addHeader(Header::HEADER_NAME_STATUS, "{$request->getVersion()} 200 OK");
                
                // load the Connection Header (keep-alive/close)
                $connection = strtolower($request->getHeader(Header::HEADER_NAME_CONNECTION));
                
                // check protocol version
                if ($connection === 'keep-alive' && $request->getVersion() === 'HTTP/1.1') {
                    
                    // lower the request counter and the TTL
                    $availableRequests --;
                    
                    // check if this will be the last requests handled by this thread
                    if ($availableRequests >= 0) {
                        
                        // set the ttl (how long the connection will still be open before closed by the server)
                        $ttl = ($startTime + $receiveTimeout) - time();
                        
                        // add the apropriate response header
                        $response->addHeader(Header::HEADER_NAME_KEEP_ALIVE, "max=$availableRequests, timeout=$ttl, thread={$this->getThreadId()}");
                    }
                    
                } else { // set request counter and TTL to 0
                    $availableRequests = 0;
                }
                
                // log the request
                $accessLogger->log($request, $response);

                /* --------------------------------------------------------------------- *
                 * Up from where we're a servlet container, so we have to instanciate a  *
                 * servlet request and a servlet response. Servlet request and response  *
                 * --------------------------------------------------------------------- */
                
                // try to locate the application and the servlet that could service the current request
                $applicationInfo = $this->locate($request);
                
                // explode the application information
                list ($application, $documentRoot, $isVhost) = $applicationInfo;

                // intialize servlet request/response
                $servletRequest = $this->newInstance('TechDivision\ServletContainer\Http\HttpServletRequest', array($request));
                $servletResponse = $this->newInstance('TechDivision\ServletContainer\Http\HttpServletResponse', array($response));
                
                // inject servlet response and session manager
                $servletRequest->injectSessionManager($sessionManager);
                $servletRequest->injectServletResponse($servletResponse);
                
                // set the application context path + Http document root (for legacy applications)
                $servletRequest->setContextPath($contextPath = '/' . $application->getName());
                $servletRequest->setServerVar('DOCUMENT_ROOT', $documentRoot);
                
                // prepare the path info for the servlet request
                if ($isVhost === true) {
                    $servletRequest->setPathInfo($request->getUri());
                } else {
                    // strip the context path if we're NOT in a vhost
                    $servletRequest->setPathInfo(
                        substr_replace($request->getUri(), '', 0, strlen($contextPath))
                    );
                }
                
                // locate the servlet that has to handle the request
                $servlet = $application->locate($servletRequest);
                
                // set the servlet path
                $servletRequest->setServletPath(get_class($servlet));
                
                // inject shutdown handler
                $servlet->injectShutdownHandler(
                    $this->newInstance(
                        'TechDivision\ServletContainer\Servlets\DefaultShutdownHandler',
                        array(
                            $client,
                            $servletResponse
                        )
                    )
                );

                // inject authentication manager
                $servlet->injectAuthenticationManager($authenticationManager);

                // let the servlet process the request send it back to the client
                $servlet->service($servletRequest, $servletResponse);
                
                /* --------------------------------------------------------------------- *
                 * Down from here we proceed with web server behaviour what means, we do *
                 * not longer deal with servlet request and response.                    *
                 * --------------------------------------------------------------------- */
                
                // check if this is the last request
                if ($availableRequests < 1) {
                    
                    // add the Connection: close header
                    $response->addHeader(Header::HEADER_NAME_CONNECTION, 'close');
                    
                    // set the flag to close the connection
                    $connectionOpen = false;
                }
                
                // send the data back to the client
                $this->send($client, $response);
                
            } while ($connectionOpen);

            // shutdown + close the client connection
            $client->shutdown();
            $client->close();
            
        } catch (ConnectionClosedByPeerException $ccbpe) { // socket closed by client/browser
            $this->getInitialContext()->getSystemLogger()->debug($ccbpe);
        } catch (SocketException $soe) { // socket timeout reached
            $this->getInitialContext()->getSystemLogger()->debug($soe);
        } catch (StreamException $ste) { // streaming socket timeout reached
            $this->getInitialContext()->getSystemLogger()->debug($ste);
        } catch (BadRequestException $bre) {
            // application can not be found
            $this->getInitialContext()->getSystemLogger()->error($bre);
            // if the resource is available, send the stacktrace back to the client
            if (is_resource($resource)) {
                // prepare stacktrace and send it back
                $response->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 400 Bad Request');
                $response->setContent(
                    sprintf(
                        '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
                         <html>
                            <head><title>400 Bad Request</title></head>
                            <body>
                                <h1>Bad Request</h1>
                                <p>The request with URL %s could not be understood by the server due to malformed syntax.</p>
                            </body>
                         </html>',
                        $request->getUri()
                    )
                );
                $this->send($client, $response);
                // shutdown + close the client connection
                $client->shutdown();
                $client->close();
            }
        } catch (\Exception $e) {
            // a servlet throws an exception -> pass it through to the client!
            $this->getInitialContext()->getSystemLogger()->error($e);
            // if the resource is available, send the stacktrace back to the client
            if (is_resource($resource)) {
                // prepare stacktrace and send it back
                $response->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 500 Internal Server Error');
                $response->setContent(
                    sprintf(
                        '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
                         <html>
                             <head><title>505 Internal Server Error</title></head>
                             <body>
                                 <h1>Internal Server Error</h1>
                                 <p>%s</p>
                             </body>
                         </html>',
                        $e->__toString()
                    )
                );
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
        return $this->accessLogger;
    }

    /**
     * Returns the array with the available applications.
     *
     * @return array The available applications
     */
    public function getApplications()
    {
        return $this->applications;
    }
}
