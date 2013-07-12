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

/**
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class Container extends AbstractContainer {

    /**
     * Path to the container's VHosts.
     * @var string
     */
    const CONTAINER_VHOSTS = '/container/vhosts/vhost';

    /**
     * Returns an array with available applications.
     * 
     * @return \TechDivision\Server The server instance
     * @todo Implement real deployment here
     */
    public function deploy() {

        // initialize the array for the VHost configuration
        $vhosts = array();

        // prepare the VHost configurations
        foreach ($this->getConfiguration()->getChilds(self::CONTAINER_VHOSTS) as $vhost) {

            // initialize VHost classname and parameters
            $vhostClassname = '\TechDivision\ServletContainer\Vhost';
            $vhostParameter = array($vhost->getName(), $vhost->getAppBase(), array());

            // register VHost in array with app base folder
            $vhosts[$vhost->getAppBase()][] = $this->newInstance($vhostClassname, $vhostParameter);
        }

        // gather all the deployed web applications
        foreach (new \FilesystemIterator(getcwd() . '/webapps') as $folder) {

            // check if file or subdirectory has been found
            if (is_dir($folder)) {

                // initialize the application name
                $name = basename($folder);

                // initialize the application instance
                $application = $this->newInstance('\TechDivision\ServletContainer\Application', array($name));
                $application->setWebappPath($folder->getPathname());

                // set the VHost configuration if available
                if (array_key_exists("webapps/$name", $vhosts)) {
                    $application->setVhosts($vhosts["webapps/$name"]);
                }

                // add the application to the available applications
                $this->applications[$application->getName()] = $application;
            }
        }

        // return the server instance
        return $this;
    }
}