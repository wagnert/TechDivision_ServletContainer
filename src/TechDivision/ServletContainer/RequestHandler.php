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

use TechDivision\SplClassLoader;
use TechDivision\ServletContainer\Exceptions\BadRequestException;

/**
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class RequestHandler extends \Worker {

    /**
     * A reference to the container instance.
     *
     * @var \TechDivision\ServletContainer\Container
     */
    protected $container;

    /**
     * Array with the available applications.
     * @var array
     */
    protected $applications;

    /**
     * Passes a reference to the container instance.
     *
     * @param \TechDivision\ServletContainer\Container $container The container instance
     * @return void
     */
    public function __construct($container) {
        $this->container = $container;
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
        return $this->applications;
    }

    /**
     * Tries to find and return the application for the passed request.
     *
     * @param string $request The request to find and return the application instance for
     * @return \TechDivision\ServletContainer\Application The application instance
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application can be found for the passed application name
     */
    public function findApplication($request) {

        // load the servlet request
        // if ($servletRequest = $request->getRequest()) {
        if ($servletRequest = $request) {

            // load the path info
            $pathInfo = $servletRequest->getPathInfo();

            // strip the leading slash and explode the application name
            list ($applicationName, $path) = explode('/', substr($pathInfo, 1));

            // load the array with the applications
            $applications = $this->getApplications();

            // check if the requested application has been deployed
            if (array_key_exists($applicationName, $applications)) {
                return $applications[$applicationName];
            }
        }

        // if not throw an exception
        throw new BadRequestException("Can\'t find application for '$applicationName'");
    }
    
    /**
     * @see \Worker::run()
     */
    public function run() {
        
        // register class loader again, because we are in a thread
        $classLoader = new SplClassLoader();
        $classLoader->register();

        // initialize the array for the applications
        $applications = array();

        // load the available applications from the container
        foreach ($this->getContainer()->getApplications() as $name => $application) {
            // set the applications and connect the entity manager
            $applications[$name] = $application->connect();
        }

        // set the applications in the worker instance
        $this->applications = $applications;
    }
}