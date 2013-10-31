<?php

/**
 * TechDivision\ServletContainer\Interfaces\Servlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Interfaces;

use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;

/**
 * Interface for all servlets.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
interface Servlet {

    /**
     * Initializes the servlet with the passed configuration.
     *
     * @param \TechDivision\ServletContainer\Servlets\ServletConfig $config The configuration to initialize the servlet with
     * @throws \TechDivision\ServletContainer\Exceptions\ServletException Is thrown if the configuration has errors
     * @return void
     */
    public function init(ServletConfig $config);

    /**
     * Return's the servlet's configuration.
     *
     * @return \TechDivision\ServletContainer\Servlets\ServletConfig The servlet's configuration
     */
    public function getServletConfig();

    /**
     * Returns the servlet manager instance (context)
     * 
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance
     */
    public function getServletManager();

    /**
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request
     * @param \TechDivision\ServletContainer\Interfaces\Response $response The response sent back to the client
     * @throws ServletException
     * @throws IOException
     * @return mixed
     */
    public function service(Request $req, Response $res);

    /**
     * Returns an array with the server variables.
     *
     * @return array The server variables
     */
    public function getServletInfo();

    /**
     * Will be invoked by the PHP when the servlets destruct method or exit() or die() has been invoked.
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client The Http client that handles the request
     * @param \TechDivision\ServletContainer\Interfaces\Response $response The response sent back to the client
     * @return void
     */
    public function shutdown(HttpClientInterface $client, Response $response);

    /**
     * @todo Document this method
     */
    public function destroy();
}
