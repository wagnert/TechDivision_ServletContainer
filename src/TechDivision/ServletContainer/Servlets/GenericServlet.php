<?php

/**
 * TechDivision\ServletContainer\Servlets\GenericServlet
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
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\AuthenticationManager;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\ShutdownHandler;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Interfaces\QueryParser;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Socket\HttpClient;

/**
 * Abstract servlet implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class GenericServlet implements Servlet
{

    /**
     * The unique servlet name.
     *
     * @var string
     */
    protected $name;

    /**
     * The servlet configuration.
     *
     * @var \TechDivision\ServletContainer\Interfaces\ServletConfig
     */
    protected $config;

    /**
     * Holds a queryparser object
     *
     * @var QueryParser
     */
    protected $queryParser;

    /**
     * Holds the authentication manager
     *
     * @var AuthenticationManager
     */
    protected $authenticationManager;

    /**
     * Holds the flag if authentication is required for specific servlet.
     *
     * @var bool
     */
    protected $authenticationRequired;

    /**
     * Holds the configured security configuration.
     *
     * @var
     */
    protected $securedUrlConfig;

    /**
     * Initializes the servlet with the passed configuration.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletConfig $config The configuration to
     *                                                                        initialize the servlet with
     *
     * @throws \TechDivision\ServletContainer\Exceptions\ServletException Is thrown if the configuration has errors
     * @return void
     */
    public function init(ServletConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Return's the servlet's configuration.
     *
     * @return \TechDivision\ServletContainer\Interfaces\ServletConfig The servlet's configuration
     */
    public function getServletConfig()
    {
        return $this->config;
    }

    /**
     * Returns the servlet manager instance (context)
     *
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance
     */
    public function getServletManager()
    {
        return $this->getServletConfig()->getServletManager();
    }

    /**
     * Returns an array with the server variables.
     *
     * @return array The server variables
     */
    public function getServletInfo()
    {
        return $this->getServletConfig()->getServerVars();
    }

    /**
     * Injects the shutdown handler.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ShutdownHandler $shutdownHandler The shutdown handler
     *
     * @return void
     */
    public function injectShutdownHandler(ShutdownHandler $shutdownHandler)
    {
        $shutdownHandler->register($this);
    }

    /**
     * Injects a queryparser
     *
     * @param \TechDivision\ServletContainer\Interfaces\QueryParser $queryParser A query parser instance
     *
     * @return void
     */
    public function injectQueryParser(QueryParser $queryParser)
    {
        $this->queryParser = $queryParser;
    }

    /**
     * Injects the authentication manager.
     *
     * @param \TechDivision\ServletContainer\AuthenticationManager $authenticationManager An authentication
     *                                                                                    manager instance
     *
     * @return void
     */
    public function injectAuthenticationManager(AuthenticationManager $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * Injects the security configuration.
     *
     * @param array $configuration The configuration array
     *
     * @return void
     */
    public function injectSecuredUrlConfig($configuration)
    {
        $this->securedUrlConfig = $configuration;
    }

    /**
     * Returns the injected query parser object
     *
     * @return QueryParser
     */
    public function getQueryParser()
    {
        return $this->queryParser;
    }

    /**
     * Returns the injected authentication manager.
     *
     * @return \TechDivision\ServletContainer\AuthenticationManager
     */
    public function getAuthenticationManager()
    {
        return $this->authenticationManager;
    }

    /**
     * Sets the authentication required flag.
     *
     * @param bool $authenticationRequired The flag if authentication is required
     *
     * @return void
     */
    public function setAuthenticationRequired($authenticationRequired)
    {
        $this->authenticationRequired = $authenticationRequired;
    }

    /**
     * Returns the authentication required flag.
     *
     * @return bool
     */
    public function getAuthenticationRequired()
    {
        // This might not be set by default, so we will return false as our default
        if (!isset($this->authenticationRequired)) {
            return false;
        } else {
            return $this->authenticationRequired;
        }
    }

    /**
     * Returns the security configuration.
     *
     * @return array
     */
    public function getSecuredUrlConfig()
    {
        return $this->securedUrlConfig;
    }

    /**
     * Will be invoked by the PHP when the servlets destruct method or exit() or die() has been invoked.
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client          The Http client that handles the request
     * @param \TechDivision\ServletContainer\Http\ServletResponse           $servletResponse The response sent back to the client
     *
     * @return void
     */
    public function shutdown(HttpClientInterface $client, ServletResponse $servletResponse)
    {

        // check if the client has a connected socket
        if (is_resource($client->getResource())) {

            $content = '';

            // check of output buffer has content
            if (ob_get_length()) {
                // set content with output buffer
                $content = ob_get_clean();
            }

            // set content to response
            $servletResponse->setContent($content);

            // prepare the content to be ready for sending to client
            $servletResponse->prepareContent();

            // prepare the headers
            $servletResponse->prepareHeaders();

            // try to shutdown client socket
            try {

                // return the string representation of the response content to the client
                $client->send($servletResponse->getHeadersAsString() . "\r\n" . $servletResponse->getContent());

                $client->shutdown();
                $client->close();

            } catch (\Exception $e) {
                $client->close();
            }
        }

        unset($client);
    }

    /**
     * Destroys the object on shutdown
     *
     * @return void
     */
    public function destroy()
    {
    }
}
