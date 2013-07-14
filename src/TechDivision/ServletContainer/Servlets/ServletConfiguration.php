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
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 */
class ServletConfiguration implements ServletConfig {

    /**
     * The application instance.
     * @var \TechDivision\ServletContainer\Application
     */
    protected $application;

    /**
     * Initializes the servlet configuration with the application instance.
     *
     * @param \TechDivision\ServletContainer\Application $application The application instance
     * @return void
     */
    public function __construct($application) {
        $this->application = $application;
    }

    /**
     * Returns the application instance.
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function getApplication() {
        return $this->application;
    }

    /**
     * Returns the host configuration.
     *
     * @return \TechDivision\ApplicationServer\Configuration The host configuration
     */
    public function getConfiguration() {
        return $this->getApplication()->getConfiguration();
    }

    /**
     * Returns the webapp base path.
     *
     * @return string The webapp base path
     */
    public function getWebappPath() {
        return $this->getApplication()->getWebappPath();
    }

    /**
     * Returns the server variables.
     *
     * @return array The array with the server variables
     */
    public function getServerVars() {
        return array(
            'SERVER_ADMIN' => $this->getConfiguration()->getServerAdmin(),
            'SERVER_SOFTWARE' => $this->getConfiguration()->getServerAdmin(),
            'DOCUMENT_ROOT' => $this->getWebappPath()
        );
    }
}