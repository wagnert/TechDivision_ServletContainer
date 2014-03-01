<?php

/**
 * TechDivision\ServletContainer\Interfaces\Servlet
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
 * @subpackage Interfaces
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;

/**
 * Interface for all servlets.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface Servlet
{

    /**
     * Initializes the servlet with the passed configuration.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletConfig $config The configuration to initialize the servlet with
     *
     * @throws \TechDivision\ServletContainer\Exceptions\ServletException Is thrown if the configuration has errors
     * @return void
     */
    public function init(ServletConfig $config);

    /**
     * Return's the servlet's configuration.
     *
     * @return \TechDivision\ServletContainer\Interfaces\ServletConfig The servlet's configuration
     */
    public function getServletConfig();

    /**
     * Returns the servlet manager instance (context)
     * 
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance
     */
    public function getServletManager();

    /**
     * Delegates to http method specific functions like doPost() for POST e.g.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response sent back to the client
     * 
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not available
     */
    public function service(ServletRequest $servletRequest, ServletResponse $servletResponse);

    /**
     * Returns an array with the server variables.
     *
     * @return array The server variables
     */
    public function getServletInfo();

    /**
     * Will be invoked by the PHP when the servlets destruct method or exit() or die() has been invoked.
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client          The Http client that handles the request
     * @param \TechDivision\ServletContainer\Http\ServletResponse           $servletResponse The response sent back to the client
     *
     * @return void
     */
    public function shutdown(HttpClientInterface $client, ServletResponse $servletResponse);

    /**
     * Destroys the object on shutdown.
     *
     * @return void
     */
    public function destroy();
}
