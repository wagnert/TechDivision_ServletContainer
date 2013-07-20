<?php

/**
 * TechDivision\ServletContainer\Container
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractContainer;
use TechDivision\ServletContainer\Exceptions\BadRequestException;

/**
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Container extends AbstractContainer {

    /**
     * Tries to find and return the application for the passed request.
     *
     * @param \TechDivision\ServletContainer\Http\HttpRequest $request The request to find and return the application instance for
     * @return \TechDivision\ServletContainer\Application The application instance
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application can be found for the passed application name
     */
    public function findApplication($servletRequest) {

        // load the server name
        $serverName = $servletRequest->getServerName();

        // load the array with the applications
        $applications = $this->getApplications();

        // iterate over the applications and check if one of the VHosts match the request
        foreach ($applications as $application) {
            if ($application->isVhostOf($serverName)) {
                $servletRequest->setServerVar('DOCUMENT_ROOT', $application->getWebappPath());
                $servletRequest->setServerVar('SERVER_SOFTWARE', $application->getServerSoftware());
                $servletRequest->setServerVar('SERVER_ADMIN', $application->getServerAdmin());
                return $application;
            }
        }

        // load path information
        $pathInfo = $servletRequest->getPathInfo();

        // strip the leading slash and explode the application name
        list ($applicationName, $path) = explode('/', substr($pathInfo, 1));

        // if not, check if the request matches a folder
        if (array_key_exists($applicationName, $applications)) {
            $servletRequest->setServerVar('DOCUMENT_ROOT', $applications[$applicationName]->getAppBase());
            $servletRequest->setServerVar('SERVER_SOFTWARE', $applications[$applicationName]->getServerSoftware());
            $servletRequest->setServerVar('SERVER_ADMIN', $applications[$applicationName]->getServerAdmin());
            return $applications[$applicationName];
        }

        // if not throw an exception
        throw new BadRequestException("Can\'t find application for '$applicationName'");
    }
}