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

use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\ServletContainer\Http\HttpResponse;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\SplClassLoader;
use TechDivision\ServletContainer\Container;
use TechDivision\ServletContainer\Exceptions\BadRequestException;
use TechDivision\SocketException;

/**
 * The thread implementation that handles the request.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */
class ThreadRequest extends \Thread {

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
     * Initializes the request with the client socket.
     *
     * @param Container $container The ServletContainer
     * @param resource $resource The client socket instance
     * @return void
     */
    public function __construct($container, $resource) {
        $this->container = $container;
        $this->resource = $resource;
    }

    /**
     * @see \Thread::run()
     */
    public function run() {

        // register class loader again, because we are in a thread
        $classLoader = new SplClassLoader();
        $classLoader->register();

        // initialize a new client socket
        $client = new HttpClient();

        // set the client socket resource
        $client->setResource($this->resource);

        // read a line from the client
        $request = new HttpRequest($client->readLine());

        try {

            // initialize response container
            $request->setResponse($response = new HttpResponse());

            // load the application to handle the request
            $application = $this->findApplication($request);

            // try to locate a servlet which could service the current request
            $servlet = $application->locate($request);

            // let the servlet process the request and store the result in the response
            $servlet->service($request, $response);

        } catch (\Exception $e) {

            ob_start();

            debug_print_backtrace();

            $response->setContent(get_class($e) . "\n\n" . $e . "\n\n" . ob_get_clean());
        }

        // prepare the headers
        $headers = $this->prepareHeader($response);

        // return the string representation of the response content to the client
        $client->send($headers . "\r\n" . $response->getContent());

        // try to shutdown the socket connection to the client
        try {
            $client->shutdown();
        } catch (SocketException $se) {
            // connection reset by peer before.
        }

        unset($client);
    }

    /**
     * Prepares the headers for the given response and returns them.
     *
     * @param Response $response The response to prepare the header for
     * @return string The headers
     * @todo This is a dummy implementation, headers has to be handled in request/response
     */
    public function prepareHeader(Response $response)
    {
        // prepare the content length
        $contentLength = strlen($response->getContent());

        // prepare the dynamic headers
        $response->addHeader("Content-Length", $contentLength);

        // return the headers
        return $response->getHeadersAsString();
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
     * Tries to find and return the application for the passed request.
     *
     * @param string $request The request to find and return the application instance for
     * @return \TechDivision\ServletContainer\Application The application instance
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application can be found for the passed application name
     */
    public function findApplication($servletRequest) {

        // load the server name
        $serverName = $servletRequest->getServerName();

        // load the array with the applications
        $applications = $this->getApplications();

        // iterate over the applications and check if one of the VHosts match the request
        foreach ($applications as $application) {
            if ($application->isVhostOf($serverName)) {
                $servletRequest->setServerVar('DOCUMENT_ROOT', $application->getWebappPath());
                $servletRequest->setServerVar('SERVER_SOFTWARE', $application->getServerSoftware());
                $servletRequest->setServerVar('SERVER_ADMIN', $application->getServerAdmin());
                return $application;
            }
        }

        // load path information
        $pathInfo = $servletRequest->getPathInfo();

        // strip the leading slash and explode the application name
        list ($applicationName, $path) = explode('/', substr($pathInfo, 1));

        // if not, check if the request matches a folder
        if (array_key_exists($applicationName, $applications)) {
            $servletRequest->setServerVar('DOCUMENT_ROOT', $applications[$applicationName]->getAppBase());
            $servletRequest->setServerVar('SERVER_SOFTWARE', $applications[$applicationName]->getServerSoftware());
            $servletRequest->setServerVar('SERVER_ADMIN', $applications[$applicationName]->getServerAdmin());
            return $applications[$applicationName];
        }

        // if not throw an exception
        throw new BadRequestException("Can\'t find application for '$applicationName'");
    }
}