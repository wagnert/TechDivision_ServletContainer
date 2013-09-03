<?php

/**
 * TechDivision\ServletContainer\Servlets\DefaultShutdownHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ShutdownHandler;

/**
 * Default shutdown handler implementations.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class DefaultShutdownHandler implements ShutdownHandler
{

    /**
     * The Http client that handles the request
     * @var unknown
     */
    public $client;

    /**
     * The Http response instance.
     * @var \TechDivision\ServletContainer\Http\HttpResponse
     */
    public $response;

    /**
     * Constructor
     *
     * @param HttpClient $client            
     * @param \TechDivision\ServletContainer\Http\HttpResponse $response The Http response instance
     * @return void            
     */
    public function __construct($client, Response $response)
    {
        $this->client = $client;
        $this->response = $response;
    }

    /**
     * It registers a shutdown function callback on the given servlet object.
     * So every servlet implementation can handle the shutdown on its own.
     *
     * @return void
     */
    public function register(Servlet $servlet)
    {
        ob_start();
        register_shutdown_function(array(
            &$servlet,
            "shutdown"
        ), $this->client, $this->response);
    }
}