<?php

/**
 * TechDivision\ServletContainer\ThreadRequest
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
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\ServletContainer\Container;

/**
 * The thread implementation that handles the request.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
abstract class AbstractRequest extends AbstractContextThread {

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
     * Holds access logger instance
     *
     * @var AccessLogger
     */
    protected $accessLogger;

    /**
     * Initializes the request with the client socket.
     *
     * @param Container $container The ServletContainer
     * @param resource $resource The client socket instance
     * @return void
     */
    public function init($container, $resource) {
        $this->container = $container;
        $this->resource = $resource;
    }

    /**
     * @param $client
     * @param $response
     */
    public function send($client, $response)
    {
        // prepare the headers
        $response->prepareHeaders();

        // return the string representation of the response content to the client
        $client->send($response->getHeadersAsString() . "\r\n" . $response->getContent());

        // try to shutdown client socket
        try {
            $client->shutdown();
            $client->close();
        } catch (\Exception $e) {
            $client->close();
        }

        unset($client);
    }
    
    /**
     * Returns the HttpClient class to be used for handling the request.
     * 
     * @return string
     */
    abstract protected function getHttpClientClass();
    
    /**
     * @see AbstractThread::main()
     */
    public function main() {
        
        // initialize a new client socket
        $client = $this->newInstance($this->getHttpClientClass());
        $client->injectHttpRequest($this->newInstance('TechDivision\ServletContainer\Http\HttpRequest'));
        $client->setNewLine("\r\n\r\n");

        // set the client socket resource
        $client->setResource($this->resource);

        // initialize the response
        $response = $this->newInstance('TechDivision\ServletContainer\Http\HttpResponse');

        try {

            /** @var HttpRequest $request */
            // receive Request Object from client
            $request = $client->receive();
            
            // inject the request with the session manager
            $sessionManager = $this->newInstance('TechDivision\ServletContainer\Session\PersistentSessionManager', array($this->initialContext));
            $request->injectSessionManager($sessionManager);

            // initialize response container
            $request->setResponse($response);

            // load the application to handle the request
            $application = $this->findApplication($request);

            // try to locate a servlet which could service the current request
            $servlet = $application->locate($request);

            $servlet->injectShutdownHandler(
                $this->newInstance(
                    'TechDivision\ServletContainer\Servlets\DefaultShutdownHandler', array($client, $response))
            );

            // let the servlet process the request and store the result in the response
            $servlet->service($request, $response);



        } catch (\Exception $e) {

            error_log($e->__toString());

            $response->setContent($e->__toString());
        }

        $this->send($client, $response);
    }

    /**
     * Returns and inits an accesslogger
     *
     * @return AccessLogger
     */
    public function getAccessLogger()
    {
        if (!$this->accessLogger) {
            $this->accessLogger = $this->newInstance('TechDivision\ServletContainer\Http\AccessLogger');
        }
        return $this->accessLogger;
    }

    /**
     * Returns the container instance.
     *
     * @return \TechDivision\ServletContainer\Container The container instance
     */
    public function getContainer() {
        return $this->container;
    }

    /**
     * Returns the array with the available applications.
     *
     * @return array The available applications
     */
    public function getApplications() {
        return $this->getContainer()->getApplications();
    }

    /**
     * @see \TechDivision\ServletContainer\Application::findApplication($servletRequest)
     */
    public function findApplication($servletRequest) {
        return $this->getContainer()->findApplication($servletRequest);
    }
}