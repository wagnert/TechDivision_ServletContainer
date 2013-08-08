<?php

/**
 * TechDivision\ServletContainer\Application
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
    
namespace TechDivision\ServletContainer;

use TechDivision\ServletContainer\ServletManager;
use TechDivision\ServletContainer\Service\Locator\ServletLocator;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ApplicationServer\Configuration;
use TechDivision\ApplicationServer\InitialContext;

/**
 * The application instance holds all information about the deployed application
 * and provides a reference to the servlet manager and the initial context.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Application {

    /**
     * Path to the container's host configuration.
     * @var string
     */
    const CONTAINER_HOST = '/container/host';

    /**
     * Path to the container's base directory.
     * @var string
     */
    const CONTAINER_BASE_DIRECTORY = '/container/baseDirectory';

    /**
     * Path to the container's VHost configuration.
     * @var string
     */
    const CONTAINER_VHOSTS = '/container/host/vhosts/vhost';

    /**
     * Path to the container's VHost alias configuration.
     * @var string
     */
    const CONTAINER_ALIAS = '/vhost/aliases/alias';
    
    /**
     * The unique application name.
     * @var string
     */
    protected $name;

    /**
     * The servlet manager.
     * @var \TechDivision\ServletContainer\ServletManager
     */
    protected $servletManager;

    /**
     * The host configuration.
     * @var \TechDivision\ApplicationServer\Configuration
     */
    protected $configuration;

    /**
     * Array with available VHost configurations.
     * @array
     */
    protected $vhosts = array();
    
    /**
     * Passes the application name That has to be the class namespace.
     * 
     * @param type $name The application name
     */
    public function __construct($initialContext, $name) {
        $this->initialContext = $initialContext;
        $this->name = $name;
    }
    
    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     * 
     * @return \TechDivision\ServletContainer\Application The connected application
     */
    public function connect() {

        // prepare the VHost configurations
        foreach ($this->getConfiguration()->getChilds(self::CONTAINER_VHOSTS) as $vhost) {

            // check if vhost configuration belongs to application
            if ($this->getName() == ltrim($vhost->getAppBase(), '/')) {

                // prepare the aliases if available
                $aliases = array();
                foreach ($vhost->getChilds(self::CONTAINER_ALIAS) as $alias) {
                    $aliases[] = $alias->getValue();
                }

                // initialize VHost classname and parameters
                $vhostClassname = '\TechDivision\ServletContainer\Vhost';
                $vhostParameter = array($vhost->getName(), $vhost->getAppBase(), $aliases);

                // register VHost in array with app base folder
                $this->vhosts[] = $this->newInstance($vhostClassname, $vhostParameter);
            }
        }
        
        // initialize the servlet manager instance
        $servletManager = new ServletManager($this);
        $servletManager->initialize();
        
        // set the entity manager
        $this->setServletManager($servletManager);
        
        // return the instance itself
        return $this;
    }
    
    /**
     * Returns the application name (that has to be the class namespace, 
     * e. g. TechDivision\Example).
     * 
     * @return string The application name
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set's the host configuration.
     *
     * @param \TechDivision\ApplicationServer\Configuration $configuration The host configuration
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setConfiguration($configuration) {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * Returns the host configuration.
     *
     * @return \TechDivision\ApplicationServer\Configuration The host configuration
     */
    public function getConfiguration() {
        return $this->configuration;
    }

    /**
     * Returns the path to the appserver webapp base directory.
     *
     * @return string The path to the appserver webapp base directory
     */
    public function getAppBase() {
        $baseDir = $this->getConfiguration()->getChild(self::CONTAINER_BASE_DIRECTORY)->getValue();
        $appBase = $this->getConfiguration()->getChild(self::CONTAINER_HOST)->getAppBase();
        return $baseDir . $appBase;
    }
    
    /**
     * Return's the path to the web application.
     * 
     * @return string The path to the web application
     */
    public function getWebappPath() {
        return $this->getAppBase() . DS . $this->getName();
    }

    /**
     * Return's the server software.
     *
     * @return string The server software
     */
    public function getServerSoftware() {
        return $this->getConfiguration()->getChild(self::CONTAINER_HOST)->getServerSoftware();
    }

    /**
     * Return's the server admin email.
     *
     * @return string The server admin email
     */
    public function getServerAdmin() {
        return $this->getConfiguration()->getChild(self::CONTAINER_HOST)->getServerAdmin();
    }
    
    /**
     * Sets the applications entity manager instance.
     * 
     * @param \TechDivision\ServletContainer\ServletManager $entityManager The entity manager instance
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setServletManager(ServletManager $servletManager) {
        $this->servletManager = $servletManager;
        return $this;
    }
    
    /**
     * Return the entity manager instance.
     * 
     * @return \TechDivision\ServletContainer\ServletManager The entity manager instance
     */
    public function getServletManager() {
        return $this->servletManager;
    }

    /**
     * Return's the applications available VHost configurations.
     *
     * @return array The available VHost configurations
     */
    public function getVhosts() {
        return $this->vhosts;
    }

    /**
     * Checks if the application is the VHost for the passed server name.
     *
     * @param string $serverName The server name to check the application being a VHost of
     * @return boolean TRUE if the application is the VHost, else FALSE
     */
    public function isVhostOf($serverName) {

        // check if the application is VHost for the passed server name
        foreach ($this->getVhosts() as $vhost) {

            // compare the VHost name itself
            if (strcmp($vhost->getName(), $serverName) === 0) {
                return true;
            }

            // then compare all aliases
            if (in_array($serverName, $vhost->getAliases())) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @param Request $request
     * @return type
     */
    public function locate(Request $request) {
        $servletLocator = new ServletLocator($this->getServletManager());
        return $servletLocator->locate($request);
    }

    /**
     * Creates a new instance of the passed class name and passes the
     * args to the instance constructor.
     *
     * @param string $className The class name to create the instance of
     * @param array $args The parameters to pass to the constructor
     * @return object The created instance
     */
    public function newInstance($className, array $args = array()) {
        return $this->initialContext->newInstance($className, $args);
    }
}