<?php

/**
 * TechDivision\ServletContainer\Deployment
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractDeployment;
use TechDivision\ApplicationServer\Interfaces\ApplicationInterface;

/**
 * Class Deployment
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class Deployment extends AbstractDeployment
{

    /**
     * Returns an array with available applications.
     *
     * @return \TechDivision\ApplicationServer\Interfaces\DeploymentInterface The deployment instance
     */
    public function deploy()
    {

        // gather all the deployed web applications
        foreach (new \FilesystemIterator($this->getBaseDirectory($this->getAppBase())) as $folder) {

            // check if file or subdirectory has been found
            if ($folder->isDir() === true) {

                // initialize the application instance
                $application = $this->newInstance(
                    '\TechDivision\ServletContainer\Application',
                    array(
                        $this->getInitialContext(),
                        $this->getContainerNode(),
                        $folder->getBasename()
                    )
                );

                // add the application to the available applications
                $this->attachApplication($application);
            }
        }

        // return initialized applications
        return $this;
    }

    /**
     * Append the deployed application to the deployment instance
     * and registers it in the system configuration.
     *
     * @param \TechDivision\ApplicationServer\Interfaces\ApplicationInterface $application The application to append
     *
     * @return void
     */
    public function attachApplication(ApplicationInterface $application)
    {

        // adds the application to the system configuration
        $this->addApplicationToSystemConfiguration($application);
        
        /*
         * Build an array with patterns as key and an array with application name and document root as value. This
         * helps to improve speed when matching an request to find the application to handle it.
         *
         * The array looks something like this:
         *
         * /^www.appserver.io(\/([a-z0-9+\$_-]\.?)+)*\/?/               => array(site, /opt/appserver/webapps/site)
         * /^appserver.io(\/([a-z0-9+\$_-]\.?)+)*\/?/                   => array(site, /opt/appserver/webapps/site)
         * /^appserver.local(\/([a-z0-9+\$_-]\.?)+)*\/?/                => array(site, /opt/appserver/webapps/site)
         * /^neos.local(\/([a-z0-9+\$_-]\.?)+)*\/?/                     => array(neos, /opt/appserver/webapps/site)
         * /^neos.appserver.io(\/([a-z0-9+\$_-]\.?)+)*\/?/              => array(neos, /opt/appserver/webapps/site)
         * /^[a-z0-9-.]*\/neos(\/([a-z0-9+\$_-]\.?)+)*\/?/              => array(neos, /opt/appserver/webapps)
         * /^[a-z0-9-.]*\/example(\/([a-z0-9+\$_-]\.?)+)*\/?/           => array(example, /opt/appserver/webapps)
         * /^[a-z0-9-.]*\/magento-1.8.1.0(\/([a-z0-9+\$_-]\.?)+)*\/?/   => array(magento-1.8.1.0, /opt/appserver/webapps)
         *
         * This should also match request URI's like:
         *
         * 127.0.0.1:8586/magento-1.8.1.0/index.php/admin/dashboard/index/key/8394a99f7bd5f4aca531d7c752a5fdb1/
         */
        
        // iterate over a applications vhost/alias configuration
        foreach ($application->getVhosts() as $vhost) {
        
            // prepare the application information for A vhost request
            $applicationInfo = array($application, $application->getWebappPath(), true);
        
            // PREPEND the vhost/alias to the patterns array
            $this->applications = array('/^' . $vhost->getName() . '(\/([a-z0-9+\$_-]\.?)+)*\/?/' => $applicationInfo) + $this->applications;
            foreach ($vhost->getAliases() as $alias) {
                $this->applications = array('/^' . $alias . '(\/([a-z0-9+\$_-]\.?)+)*\/?/' => $applicationInfo) + $this->applications;
            }
        }
        
        // prepare the application information for a NON vhost request
        $applicationInfo = array($application, $application->getBaseDirectory($application->getAppBase()), false);
        
        // finally APPEND a wildcard pattern for each application to the patterns array
        $this->applications = $this->applications + array('/^[a-z0-9-.]*\/' . $application->getName() . '(\/([a-z0-9+\$_-]\.?)+)*\/?/' => $applicationInfo);
    }
}
