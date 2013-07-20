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
        return $this->getContainer()->getApplications();
    }

    /**
     * @see \TechDivision\ServletContainer\Application::findApplication($servletRequest)
     */
    public function findApplication($servletRequest) {
        return $this->getContainer()->findApplication($servletRequest);
    }
    
    /**
     * @see \Worker::run()
     */
    public function run() {

        // register class loader again, because we are in a thread
        $classLoader = new SplClassLoader();
        $classLoader->register();
    }
}