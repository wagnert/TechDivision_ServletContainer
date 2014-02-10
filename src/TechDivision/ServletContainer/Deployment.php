<?php
/**
 * TechDivision\ServletContainer\Deployment
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractDeployment;

/**
 * Class Deployment
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
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
            if (is_dir($folder)) {

                // initialize the application name
                $name = basename($folder);

                // initialize the application instance
                $application = $this->newInstance('\TechDivision\ServletContainer\Application', array(
                    $this->getInitialContext(),
                    $this->getContainerNode(),
                    $name
                ));

                // add the application to the available applications
                $this->addApplication($application);
            }
        }

        // return initialized applications
        return $this;
    }
}
