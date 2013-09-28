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

use TechDivision\ApplicationServer\AbstractApplication;
use TechDivision\ServletContainer\ServletManager;
use TechDivision\ServletContainer\Service\Locator\ServletLocator;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ApplicationServer\Configuration;
use TechDivision\ApplicationServer\Vhost;

/**
 * The application instance holds all information about the deployed application
 * and provides a reference to the servlet manager and the initial context.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class Application extends AbstractApplication
{

    /**
     * The servlet manager.
     *
     * @var \TechDivision\ServletContainer\ServletManager
     */
    protected $servletManager;

    /**
     * Array with available VHost configurations.
     * @array
     */
    protected $vhosts = array();

    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     *
     * @return \TechDivision\ServletContainer\Application The connected application
     */
    public function connect()
    {

        // also initialize the vhost configuration
        parent::connect();

        // initialize the class loader with the additional folders
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath());
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath() . DIRECTORY_SEPARATOR . 'WEB-INF' . DIRECTORY_SEPARATOR . 'classes');
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->getWebappPath() . DIRECTORY_SEPARATOR . 'WEB-INF' . DIRECTORY_SEPARATOR . 'lib');

        // initialize the servlet manager instance
        $servletManager = $this->newInstance('TechDivision\ServletContainer\ServletManager', array(
            $this
        ));
        $servletManager->initialize();

        // set the entity manager
        $this->setServletManager($servletManager);

        // return the instance itself
        return $this;
    }

    /**
     * Return's the server software.
     *
     * @return string The server software
     */
    public function getServerSoftware()
    {
        return $this->getConfiguration()
            ->getChild(self::XPATH_CONTAINER_HOST)
            ->getServerSoftware();
    }

    /**
     * Return's the server admin email.
     *
     * @return string The server admin email
     */
    public function getServerAdmin()
    {
        return $this->getConfiguration()
            ->getChild(self::XPATH_CONTAINER_HOST)
            ->getServerAdmin();
    }

    /**
     * Sets the applications entity manager instance.
     *
     * @param \TechDivision\ServletContainer\ServletManager $entityManager
     *            The entity manager instance
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setServletManager(ServletManager $servletManager)
    {
        $this->servletManager = $servletManager;
        return $this;
    }

    /**
     * Return the entity manager instance.
     *
     * @return \TechDivision\ServletContainer\ServletManager The entity manager instance
     */
    public function getServletManager()
    {
        return $this->servletManager;
    }

    /**
     * Return's the applications available VHost configurations.
     *
     * @return array The available VHost configurations
     */
    public function getVhosts()
    {
        return $this->vhosts;
    }

    /**
     * Checks if the application is the VHost for the passed server name.
     *
     * @param string $serverName
     *            The server name to check the application being a VHost of
     * @return boolean TRUE if the application is the VHost, else FALSE
     */
    public function isVhostOf($serverName)
    {

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
    public function locate(Request $request)
    {
        $servletLocator = new ServletLocator($this->getServletManager());
        return $servletLocator->locate($request);
    }
}