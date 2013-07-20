<?php

/**
 * TechDivision\ServletContainer\ReactReceiver
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\InitialContext;
use TechDivision\ApplicationServer\AbstractReceiver;
use TechDivision\ServletContainer\Exceptions\BadRequestException;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\ServletContainer\Http\HttpResponse;
use TechDivision\ServletContainer\Http\ReactHttpRequest;

/**
 * @package     TechDivision\ApplicationServer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */
class ReactReceiver extends AbstractReceiver {

    /**
     * Sets the reference to the container instance.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ContainerInterface $container The container instance
     */
    public function __construct($container) {

        // set the container instance
        $this->container = $container;

        // load the receiver configuration
        $configuration = $this->getContainer()->getReceiverConfiguration();

        // set the receiver configuration
        $this->setConfiguration($configuration);

        // set the configuration in the initial context
        InitialContext::get()->setAttribute(get_class($this), $configuration);

        // enable garbage collector and initialize configuration
        $this->gcEnable()->checkConfiguration();
    }

    /**
     * @see TechDivision\ApplicationServer\Interfaces\ReceiverInterface::start()
     */
    public function start() {

        try {

            $app = function ($request, $response) {

                try {

                    // initialize servlet request and response
                    $req = new ReactHttpRequest($request);
                    $req->setResponse($res = new HttpResponse());

                    // load the application to handle the request
                    $application = $this->findApplication($req);

                    // try to locate a servlet which could service the current request
                    $servlet = $application->locate($req);

                    // let the servlet process the request and store the result in the response
                    $servlet->service($req, $res);

                    $headers = $res->getHeaders();
                    unset($headers[HttpResponse::HEADER_NAME_STATUS]);

                    $response->writeHead(200, $headers);
                    $response->write($res->getContent());
                    $response->end();

                } catch (\Exception $e) {

                    ob_start();

                    debug_print_backtrace();

                    $response->writeHead(500, $res->getHeaders());
                    $response->write(get_class($e) . "\n\n" . $e . "\n\n" . ob_get_clean());
                    $response->end();
                }
            };

            $loop = \React\EventLoop\Factory::create();
            $socket = new \React\Socket\Server($loop);
            $http = new \React\Http\Server($socket);

            $http->on('request', $app);

            // load the receiver params
            $parameters = $this->getContainer()->getParameters();

            $socket->listen($parameters->getPort(), $parameters->getAddress());
            $loop->run();

        } catch (\Exception $ge) {
            error_log($ge->__toString());
        }
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