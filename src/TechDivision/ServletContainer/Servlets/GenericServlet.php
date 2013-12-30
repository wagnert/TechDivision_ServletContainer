<?php

/**
 * TechDivision\ServletContainer\GenericServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\AuthenticationtManager;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\ShutdownHandler;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Interfaces\QueryParser;
use TechDivision\ServletContainer\Socket\HttpClient;
use TechDivision\ServletContainer\AuthenticationManager;

/**
 * Abstract servlet implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 * @author      Johann Zelger <jz@techdivision.com>
 */
abstract class GenericServlet implements Servlet {

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
    protected $securityConfiguration;

    /**
     * @see \TechDivision\ServletContainer\Interfaces\Servlet::init(ServletConfig $config)
     */
    public function init(ServletConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @see \TechDivision\ServletContainer\Interfaces\Servlet::getServletConfig()
     */
    public function getServletConfig()
    {
        return $this->config;
    }

    /**
     * @see \TechDivision\ServletContainer\Interfaces\Servlet::getServletManager()
     */
    public function getServletManager()
    {
        return $this->getServletConfig()->getServletManager();
    }

    /**
     * @see \TechDivision\ServletContainer\Interfaces\Servlet::getServletInfo()
     */
    public function getServletInfo()
    {
        return $this->getServletConfig()->getServerVars();
    }

    /**
     * Injects the shutdown handler.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ShutdownHandler $shutdownHandler The shutdown handler
     */
    public function injectShutdownHandler(ShutdownHandler $shutdownHandler)
    {
        $shutdownHandler->register($this);
    }

    /**
     * Injects a queryparser
     *
     * @param QueryParser $queryParser
     * @return void
     */
    public function injectQueryParser(QueryParser $queryParser)
    {
        $this->queryParser = $queryParser;
    }

    /**
     * Injects the authentication manager.
     *
     * @param AuthenticationManager $authenticationManager
     */
    public function injectAuthenticationManager(AuthenticationManager $authenticationManager)
    {
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * Injects the security configuration.
     *
     * @param array $configuration
     */
    public function injectSecurityConfiguration($configuration)
    {
        $this->securityConfiguration = $configuration;
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
     * @param $authenticationRequired
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
        return $this->authenticationRequired;
    }

    /**
     * Returns the security configuration.
     *
     * @return array
     */
    public function getSecurityConfiguration()
    {
        return $this->securityConfiguration;
    }

    /**
     * @see \TechDivision\ServletContainer\Interfaces\Servlet::shutdown(HttpClientInterface $client, Response $response)
     */
    public function shutdown(HttpClientInterface $client, Response $response)
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
            $response->setContent($content);

            // prepare the content to be ready for sending to client
            $response->prepareContent();

            // prepare the headers
            $response->prepareHeaders();

            // return the string representation of the response content to the client
            $client->send($response->getHeadersAsString() . "\r\n" . $response->getContent());

            // try to shutdown client socket
            try {
                $client->shutdown();
                $client->close();
            } catch (\Exception $e) {
                $client->close();
            }
        }

        unset($client);
    }

    /**
     * @see \TechDivision\ServletContainer\Interfaces\Servlet::destroy()
     */
    public function destroy()
    {
    }

}