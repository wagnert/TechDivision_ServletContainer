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

use TechDivision\ServletContainer\ThreadRequest;
use TechDivision\ServletContainer\Container;
use TechDivision\SplClassLoader;

/**
 * The thread implementation that handles the request.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */
class ThreadRequestAcceptor extends \Thread {

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
        // start loop
        while (true) {
            // accept client connection
            if ($clientSocket = socket_accept($this->resource)) {
                // init new ThreadRequest instance
                $request = new ThreadRequest($this->container, $clientSocket);
                // start async thread
                $request->start();
            }
        }
    }

}