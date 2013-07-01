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
     * Returns an array with available applications.
     * 
     * @return \TechDivision\Server The server instance
     * @todo Implement real deployment here
     */
    public function deploy() {

        // gather all the deployed web applications
        foreach (new \FilesystemIterator(getcwd() . '/webapps') as $folder) {

            // check if file or subdirectory has been found
            if (is_dir($folder)) {

                // initialize the application name
                $name = basename($folder);

                // initialize the application instance
                $application = $this->newInstance('\TechDivision\ServletContainer\Application', array($name));
                $application->setWebappPath($folder->getPathname());

                // add the application to the available applications
                $this->applications[$application->getName()] = $application;
            }
        }

        // return the server instance
        return $this;
    }
}