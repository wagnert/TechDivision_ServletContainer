<?php

/**
 * TechDivision\ServletContainer\Service\Locator\ServletLocator
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Service\Locator;

use TechDivision\ServletContainer\Service\Locator\ResourceLocatorInterface;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Exceptions\ServletNotFoundException;

/**
 * The servlet resource locator implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class ServletLocator implements ResourceLocatorInterface
{

    /**
     * The servlet manager instance.
     *
     * @var \TechDivision\ServletContainer\ServletManager
     */
    protected $servletManager;
    
    /**
     * The array with the servlet mappings.
     * 
     * @var array
     */
    protected $servletMappings;

    /**
     * Initializes the locator with the actual servlet manager instance.
     *
     * @param \TechDivision\ServletContainer\ServletManager $servletManager The servlet manager instance
     *
     * @return void
     */
    public function __construct($servletManager)
    {
        
        // initialize the servlet manager
        $this->servletManager = $servletManager;

        // retrieve the registered servlets
        $this->servletMappings = $this->getServletManager()->getServletMappings();
    }

    /**
     * Returns the servlet manager instance to use.
     *
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance to use
     */
    public function getServletManager()
    {
        return $this->servletManager;
    }

    /**
     * Returns the array with the servlet mappings.
     *
     * @return array The array with the servlet mappings
     */
    public function getServletMappings()
    {
        return $this->servletMappings;
    }

    /**
     * Returns the actual application instance.
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function getApplication()
    {
        return $this->getServletManager()->getApplication();
    }

    /**
     * Tries to locate a servlet for the passed request instance.
     *
     * @param Request $request The request instance to return the servlet for
     *
     * @return \TechDivision\ServletContainer\Interfaces\Servlet The requested servlet
     * @throws \TechDivision\ServletContainer\Exceptions\ServletNotFoundException Is thrown if no servlet can be found for the passed request
     * @see \TechDivision\ServletContainer\Service\Locator\ResourceLocatorInterface::locate()
     */
    public function locate(Request $request)
    {
        
        // build the file-path of the request
        $path = $this->getApplication()->normalizePathInfo($request);
        
        // iterate over all servlets and return the matching one
        foreach ($this->getServletMappings() as $urlPattern => $servletName) {
            if (fnmatch($urlPattern, $path)) {
                $servlet = $this->getServletManager()->getServlet($servletName);
                return $servlet;
            }
        }
        
        // throw an exception if no servlet matches the path info
        throw new ServletNotFoundException(
            sprintf("Can't find servlet for requested path %s", $path)
        );
    }
}
