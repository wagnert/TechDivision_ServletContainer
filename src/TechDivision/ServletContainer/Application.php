<?php
/**
 * TechDivision\ServletContainer\Application
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
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
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
     * The servlet locator.
     *
     * @var \TechDivision\ServletContainer\Service\Locator\ServletLocator
     */
    protected $servletLocator;

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

        // set the servlet manager
        $this->setServletManager($servletManager->initialize());
        
        // initialize the servlet locator instance
        $servletLocator = $this->newInstance('TechDivision\ServletContainer\Service\Locator\ServletLocator', array(
            $this->servletManager
        ));
        
        // set the servlet locator
        $this->setServletLocator($servletLocator);

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
        return $this->getContainerNode()->getHost()->getServerSoftware();
    }

    /**
     * Return's the server admin email.
     *
     * @return string The server admin email
     */
    public function getServerAdmin()
    {
        return $this->getContainerNode()->getHost()->getServerAdmin();
    }

    /**
     * Sets the applications servlet manager instance.
     *
     * @param \TechDivision\ServletContainer\ServletManager $servletManager The servlet manager instance
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setServletManager(ServletManager $servletManager)
    {
        $this->servletManager = $servletManager;
        return $this;
    }

    /**
     * Return the servlet manager instance.
     *
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance
     */
    public function getServletManager()
    {
        return $this->servletManager;
    }

    /**
     * Sets the applications servlet locator instance.
     *
     * @param \TechDivision\ServletContainer\Service\Locator\ServletLocator $servletLocator The servlet locator instance
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function setServletLocator(ServletLocator $servletLocator)
    {
        $this->servletLocator = $servletLocator;
        return $this;
    }

    /**
     * Return the servlet locator instance.
     *
     * @return \TechDivision\ServletContainer\Service\Locator\ServletLocator The servlet locator instance
     */
    public function getServletLocator()
    {
        return $this->servletLocator;
    }

    /**
     * Locates and returns the servlet instance that handles
     * the request passed as parameter.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance
     *
     * @return \TechDivision\ServletContainer\Interfaces\Servlet The servlet instance to handle the request
     */
    public function locate(Request $request)
    {
        return $this->getServletLocator()->locate($request);
    }
}
