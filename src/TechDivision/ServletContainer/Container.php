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
use TechDivision\ServletContainer\Http\AccessLogger;

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
     * @var \TechDivision\ServletContainer\Http\AccessLogger
     */
    protected $accessLogger;
    
    /**
     * The array with the request patterns.
     * 
     * @var array
     */
    protected $patterns;
    
    /**
     * Initializes the container with the initial context, the unique container ID
     * and the deployed applications.
     *
     * @param \TechDivision\ApplicationServer\InitialContext                         $initialContext The initial context
     *                                                                                               instance
     * @param \TechDivision\ApplicationServer\Api\Node\ContainerNode                 $containerNode  The container's
     *                                                                                               UUID
     * @param array<\TechDivision\ApplicationServer\Interfaces\ApplicationInterface> $applications   The application
     *                                                                                               instance
     *
     * @return void
     * @todo Application deployment only works this way because of Thread compatibilty
     */
    public function __construct($initialContext, $containerNode, $applications)
    {
        
        // call parent constructor
        parent::__construct($initialContext, $containerNode, $applications);
        
        // initialize the logger
        $this->accessLogger = $this->newInstance('TechDivision\ServletContainer\Http\AccessLogger');
        
        // initialize the array with the request patterns
        $this->patterns = array();
        
        /* 
         * Build an array with patterns as key and an array with application name
         * and document root as value. This helps to improve speed when matching
         * an request to find the application to handle it.
         * 
         * The array looks something like this:
         * 
         * /(www.appserver.io)\/(.*)/  => array(site, /opt/appserver/webapps/site)
         * /(appserver.io)\/(.*)/      => array(site, /opt/appserver/webapps/site)
         * /(appserver.local)\/(.*)/   => array(site, /opt/appserver/webapps/site)
         * /(neos.local)\/(.*)/        => array(neos, /opt/appserver/webapps/site)
         * /(neos.appserver.io)\/(.*)/ => array(neos, /opt/appserver/webapps/site)
         * /(.*)\/(neos)/              => array(neos, /opt/appserver/webapps)
         * /(.*)\/(example)/           => array(example, /opt/appserver/webapps)
         * /(.*)\/(magento-1.8.1.0)/   => array(magento-1.8.1.0, /opt/appserver/webapps)
         */
        
        // prepare the application patterns to be matched against the request
        foreach ($this->getApplications() as $applicationName => $application) {
            
            // prepend the vhost/alias to the patterns array
            foreach ($application->getVhosts() as $vhost) {
                $this->patterns = array('/(' . $vhost->getName() . ')\/(.*)/' => array($applicationName, $application->getWebappPath())) + $this->patterns; 
                foreach ($vhost->getAliases() as $alias) {
                    $this->patterns = array('/(' . $alias . ')\/(.*)/' => array($applicationName, $application->getWebappPath())) + $this->patterns;
                }
            }
            
            // append the wildcard patterns to the patterns array
            $documentRoot = $application->getBaseDirectory($application->getAppBase());
            $this->patterns = $this->patterns + array('/(.*)\/(' . $applicationName . ')/' => array($applicationName, $documentRoot));
        }
    }

    /**
     * The access logger implementation that writes the Apache compatible log files.
     *
     * @return \TechDivision\ServletContainer\Http\AccessLogger The access logger implementation
     */
    public function getAccessLogger()
    {
        return $this->accessLogger;
    }
    
    /**
     * Returns the request patterns to be matched against the
     * incoming request.
     * 
     * @return array The array with the request patterns
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * Tries to find and return the application for the passed request.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $servletRequest The request to find and return
     *                                                                          the application instance for
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application can be found
     *      for the passed application name
     */
    public function findApplication(Request $servletRequest)
    {

        // prepare the server variables for this container
        $this->prepareServerVars($servletRequest);

        // load the array with the applications
        $applications = $this->getApplications();
        
        // prepare the URL to be matched
        $url = $servletRequest->getServerName() . $servletRequest->getUri();
        
        foreach ($this->getPatterns() as $pattern => $applicationInfo) {
            
            // try to match a registered application with the passed request
            if (preg_match($pattern, $url) === 1) {
                
                // extract application name and document root from the application info
                list ($applicationName, $documentRoot) = $applicationInfo;
            
                // set the DOCUMENT_ROOT to /opt/appserver/webapps
                $servletRequest->setServerVar('DOCUMENT_ROOT', $documentRoot);
                $servletRequest->setWebappName($applicationName);
                
                // return the application instance
                return $applications[$applicationName];
            }
        }

        // if not throw an exception
        throw new BadRequestException("Can\'t find application for '$applicationName'");
    }

    /**
     * Prepare's the request with the server vars $_SERVER from the container's
     * specific data.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $servletRequest The request instance to be prepared
     *                                                                          with the container specific data
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
