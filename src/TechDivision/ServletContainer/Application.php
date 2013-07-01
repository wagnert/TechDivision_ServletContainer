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
use TechDivision\ServletContainer\Interfaces\ServletRequest;

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
     * The unique application name.
     * @var string
     */
    protected $name;
    
    /**
     * The path to the web application.
     * @var string
     */
    protected $webappPath;

    /**
     * The servlet manager.
     * @var \TechDivision\ServletContainer\ServletManager
     */
    protected $servletManager;
    
    /**
     * Passes the application name That has to be the class namespace.
     * 
     * @param type $name The application name
     */
    public function __construct($name) {
        $this->name = $name;
    }
    
    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     * 
     * @return \TechDivision\ServletContainer\Application The connected application
     */
    public function connect() {
        
        // initialize the servlet manager instance
        $servletManager = new ServletManager();
        $servletManager->setWebappPath($this->getWebappPath());
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
     * Set's the path to the web application.
     * 
     * @param string $webappPath The path to the web application
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setWebappPath($webappPath) {
        $this->webappPath = $webappPath;
        return $this;
    }
    
    /**
     * Return's the path to the web application.
     * 
     * @return string The path to the web application
     */
    public function getWebappPath() {
        return $this->webappPath;
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
     * 
     * @param \TechDivision\ServletContainer\Interfaces\ServletRequest $request
     * @return type
     */
    public function locate(ServletRequest $request) {
        $servletLocator = new ServletLocator($this->getServletManager());
        return $servletLocator->locate($request);
    }
}