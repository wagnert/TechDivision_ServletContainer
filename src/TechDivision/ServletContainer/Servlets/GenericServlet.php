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

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\ShutdownHandler;
use TechDivision\ServletContainer\Socket\HttpClient;

/**
 * Abstract servlet implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 */
abstract class GenericServlet implements Servlet {

    /**
     * The host configuration.
     * @var ServletConfig
     */
    protected $config;

    /**
     * @param ServletConfig $config
     * @throws ServletException;
     * @return mixed
     */
    public function init(ServletConfig $config) {
        $this->config = $config;
    }

    /**
     * @return ServletConfig
     */
    public function getServletConfig() {
        return $this->config;
    }

    /**
     * @return mixed|void
     */
    public function getServletInfo() {
        return $this->getServletConfig()->getServerVars();
    }

    /**
     * @param ShutdownHandler $shutdownHandler
     */
    public function injectShutdownHandler(ShutdownHandler $shutdownHandler) {
        $shutdownHandler->register($this);
    }

    /**
     * @param HttpClient $client
     * @param Response $response
     * @return mixed|void
     */
    public function shutdown(HttpClient $client, Response $response)
    {

        if (is_resource($client->getResource())) {

            $content = '';

            // check of output buffer has content
            if (ob_get_length()) {
                // set content with output buffer
                $content = ob_get_clean();
            }

            // set content to response
            $response->setContent($content);

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
     * @return mixed|void
     * @todo Implement destroy() method
     */
    public function destroy() {
    }
}