<?php

/**
 * TechDivision\ServletContainer\Modules\ServletModule
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Modules;

use TechDivision\ApplicationServer\Interfaces\ContainerInterface;
use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Exceptions\BadRequestException;

/**
 * This is a servlet module that handles request.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class ServletModule extends AbstractModule
{
    
    /**
     * The applications for the container.
     * 
     * @var array
     */
    protected $applications;
    
    /**
     * Initializes the module.
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::init()
     */
    public function init()
    {
        $this->applications = $this->getContainer()->getApplications();
    }
    
    /**
     * Processes the request.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client   The http client
     * @param \TechDivision\ServletContainer\Interfaces\Request             $request  The request to be handled
     * @param \TechDivision\ServletContainer\Interfaces\Response            $response The response instance
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::handle()
     */
    public function handle(HttpClientInterface $client, Request $request, Response $response)
    {

        // intialize servlet session, request + response
        $servletRequest = $this->newInstance('TechDivision\ServletContainer\Http\HttpServletRequest', array($request));
        $servletResponse = $this->newInstance('TechDivision\ServletContainer\Http\HttpServletResponse', array($response));
        $sessionManager = $this->newInstance('TechDivision\ServletContainer\Session\PersistentSessionManager', array($this->getInitialContext()));

        // inject servlet response and session manager
        $servletRequest->injectSessionManager($sessionManager);
        $servletRequest->injectServletResponse($servletResponse);
        
        // try to locate the application and the servlet that could service the current request
        $servlet = $this->locate($servletRequest)->locate($servletRequest);

        // initialize the shutdown handler, the session manager and the authentication manager
        $shutdownHandler = $this->newInstance('TechDivision\ServletContainer\Servlets\DefaultShutdownHandler', array($client, $servletResponse));
        $authenticationManager = $this->newInstance('TechDivision\ServletContainer\AuthenticationManager');

        // inject authentication manager and shutdown handler
        $servlet->injectAuthenticationManager($authenticationManager);
        $servlet->injectShutdownHandler($shutdownHandler);

        // let the servlet process the request send it back to the client
        $servlet->service($servletRequest, $servletResponse);
    }
    
    /**
     * Tries to find an application that matches the passed request.
     * 
     * @param \TechDivision\ServletContainer\Htt\ServletRequest $servletRequest The request instance to locate the application for
     * 
     * @return array The application info that matches the request
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application matches the request
     */
    public function locate(ServletRequest $servletRequest)
    {
        
        // prepare the URI to be matched
        $url = $servletRequest->getServerName() . $servletRequest->getUri();
        
        // try to find the application by match it one of the prepared patterns
        foreach ($this->getApplications() as $pattern => $application) {
        
            // try to match a registered application with the passed request
            if (preg_match($pattern, $url) === 1) {
                
                // prepare and set the applications context path
                $servletRequest->setContextPath($contextPath = '/' . $application->getName());
                
                // prepare the path information depending if we're a vhost or not
                if ($application->isVhostOf($servletRequest->getServerName())) {
                    $pathInfo = $servletRequest->getUri();
                } else {
                    $pathInfo = str_replace($contextPath, '', $servletRequest->getUri());
                }
                
                // set the script file information in the server variables
                $servletRequest->setPathInfo($pathInfo);
                
                // return the application instance
                return $application;
            }
        }
        
        // if not throw a bad request exception
        throw new BadRequestException(
            sprintf("Can't find application for URI %s", $servletRequest->getUri())
        );
    }
    
    /**
     * Returns the with the initialized applications.
     * 
     * @return array The array with the initialized applications
     */
    protected function getApplications()
    {
        return $this->applications;
    }
}
