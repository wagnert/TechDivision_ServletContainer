<?php
/**
 * TechDivision\ServletContainer\Container
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

use TechDivision\ApplicationServer\AbstractContainer;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Exceptions\BadRequestException;

/**
 * Class Container
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class Container extends AbstractContainer
{

    /**
     * Holds access logger instance
     *
     * @var AccessLogger
     */
    protected $accessLogger;

    /**
     * Initializes the container with the initial context, the unique container ID
     * and the deployed applications.
     *
     * @param \TechDivision\ApplicationServer\InitialContext                         $initialContext The initial context instance
     * @param \TechDivision\ApplicationServer\Api\Node\ContainerNode                 $containerNode  The container's UUID
     * @param array<\TechDivision\ApplicationServer\Interfaces\ApplicationInterface> $applications   The application instance
     *
     * @return void
     * @todo Application deployment only works this way because of Thread compatibilty
     */
    public function __construct($initialContext, $containerNode, $applications)
    {
        parent::__construct($initialContext, $containerNode, $applications);
        $this->accessLogger = $this->newInstance('TechDivision\ServletContainer\Http\AccessLogger');
    }
    
    /**
     * The access logger implementation that writes the Apache compatible log files.
     * 
     * @return \TechDivision\ServletContainer\AccessLogger The access logger implementation
     */
    public function getAccessLogger()
    {
        return $this->accessLogger;
    }

    /**
     * Tries to find and return the application for the passed request.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $servletRequest The request to find and return the application instance for
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application can be found for the passed application name
     */
    public function findApplication(Request $servletRequest)
    {
        
        // load the server name
        $serverName = $servletRequest->getServerName();
        
        // prepare the server variables for this container
        $this->prepareServerVars($servletRequest);
        
        // load the array with the applications
        $applications = $this->getApplications();
        
        // iterate over the applications and check if one of the VHosts match the request
        foreach ($applications as $application) {
            if ($application->isVhostOf($serverName)) {
                // set the DOCUMENT_ROOT to /opt/appserver/webapps/<WEBAPP-NAME>
                $servletRequest->setServerVar('DOCUMENT_ROOT', $application->getWebappPath());
                $servletRequest->setWebappName($application->getName());
                return $application;
            }
        }
        
        // load path information
        $pathInfo = $servletRequest->getPathInfo();
        
        // strip the leading slash and explode the application name
        list ($applicationName, $path) = explode('/', substr($pathInfo, 1));
        
        // if not, check if the request matches a folder
        if (array_key_exists($applicationName, $applications)) {
            // set the DOCUMENT_ROOT to /opt/appserver/webapps
            $servletRequest->setServerVar('DOCUMENT_ROOT', $applications[$applicationName]->getWebappPath());
            $servletRequest->setWebappName($applications[$applicationName]->getName());
            return $applications[$applicationName];
        }
        
        // if not throw an exception
        throw new BadRequestException("Can\'t find application for '$applicationName'");
    }

    /**
     * Prepare's the request with the server vars $_SERVER from the container's
     * specific data.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $servletRequest The request instance to be prepared with the container specific data
     *
     * @return void
     */
    public function prepareServerVars(Request $servletRequest)
    {
        $servletRequest->setServerVar(
            'PATH',
            $this->getBaseDirectory(DIRECTORY_SEPARATOR . 'bin') . PATH_SEPARATOR . getenv('PATH')
        );
        $servletRequest->setServerVar(
            'SERVER_SOFTWARE',
            $this->getContainerNode()->getHost()->getServerSoftware()
        );
        $servletRequest->setServerVar(
            'SERVER_ADMIN',
            $this->getContainerNode()->getHost()->getServerAdmin()
        );
    }
}
