<?php

/**
 * TechDivision\ServletContainer\ServletConfiguration
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Interfaces\ServletConfig;

/**
 * Servlet configuration.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Markus Stockbauer <ms@techdivision.com>
 * @author Tim Wagner <tw@techdivision.com>
 */
class ServletConfiguration implements ServletConfig
{

    /**
     * The application instance.
     * 
     * @var \TechDivision\ServletContainer\ServletManager
     */
    protected $servletManager;
    
    /**
     * Array with the servlet's init parameters found in the web.xml.
     * 
     * @var array
     */
    protected $initParameter = array();

    /**
     * Initializes the servlet configuration with the servlet manager instance.
     *
     * @param \TechDivision\ServletContainer\ServletManager $servletManager
     *            The servlet manager instance
     * @return void
     */
    public function __construct($servletManager)
    {
        $this->servletManager = $servletManager;
    }

    /**
     * Returns the servlet manager instance.
     *
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance
     */
    public function getServletManager()
    {
        return $this->servletManager;
    }

    /**
     * Returns the application instance.
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function getApplication()
    {
        return $this->getServletManager()->getApplication();
    }

    /**
     * Returns the host configuration.
     *
     * @return \TechDivision\ApplicationServer\Configuration The host configuration
     */
    public function getConfiguration()
    {
        return $this->getApplication()->getConfiguration();
    }

    /**
     * Returns the webapp base path.
     *
     * @return string The webapp base path
     */
    public function getWebappPath()
    {
        return $this->getApplication()->getWebappPath();
    }

    /**
     * Returns the path to the appserver webapp base directory.
     *
     * @return string The path to the appserver webapp base directory
     */
    public function getAppBase()
    {
        return $this->getApplication()->getAppBase();
    }
    
    /**
     * Register's the init parameter under the passed name.
     * 
     * @param string $name Name to register the init parameter with
     * @param string $value The value of the init parameter
     */
    public function addInitParameter($name, $value)
    {
        $this->initParameter[$name] = $value;
    }
    
    /**
     * Return's the init parameter with the passed name.
     * 
     * @param string $name Name of the init parameter to return
     */
    public function getInitParameter($name)
    {
        if (array_key_exists($name, $this->initParameter)) {
            return $this->initParameter[$name];
        }
    }

    /**
     * Returns the server variables.
     *
     * @return array The array with the server variables
     */
    public function getServerVars()
    {
        return array(
            'SERVER_ADMIN' => $this->getConfiguration()->getServerAdmin(),
            'SERVER_SOFTWARE' => $this->getConfiguration()->getServerAdmin(),
            'DOCUMENT_ROOT' => $this->getWebappPath()
        );
    }
}