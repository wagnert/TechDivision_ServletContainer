<?php

/**
 * TechDivision\ServletContainer\Servlets\DefaultShutdownHandler
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ShutdownHandler;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Http\ServletResponse;

/**
 * Default shutdown handler implementations.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class DefaultShutdownHandler implements ShutdownHandler
{

    /**
     * The Http client that handles the request.
     *
     * @var \TechDivision\ServletContainer\Interfaces\HttpClientInterface
     *
     */
    public $client;

    /**
     * The Http servlet response instance.
     *
     * @var \TechDivision\ServletContainer\Http\ServletResponse
     *
     */
    public $servletResponse;

    /**
     * Constructor
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client          The Http client
     * @param \TechDivision\ServletContainer\Http\ServletResponse           $servletResponse The Http response instance
     *
     * @return void
     */
    public function __construct(HttpClientInterface $client, ServletResponse $servletResponse)
    {
        $this->client = $client;
        $this->servletResponse = $servletResponse;
    }

    /**
     * It registers a shutdown function callback on the given servlet object.
     * So every servlet implementation can handle the shutdown on its own.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet The servlet instance
     *
     * @return void
     */
    public function register(Servlet $servlet)
    {
        ob_start();
        register_shutdown_function(
            array(&$servlet, 'shutdown'),
            $this->client,
            $this->servletResponse
        );
    }
}
