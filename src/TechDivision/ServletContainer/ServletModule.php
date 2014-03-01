<?php

/**
 * TechDivision\ServletContainer\ServletModule
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
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\Http\HttpRequestInterface;
use TechDivision\Http\HttpResponseInterface;
use TechDivision\WebServer\ModuleInterface;
use TechDivision\WebServer\ServerConfigInterface;
use TechDivision\WebServer\ModuleConfigInterface;

/**
 * This is a web server handler implementation that 
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class ServletModule implements ModuleInterface
{
    
    /**
     * The unique module name.
     * 
     * @var string
     */
    const MODULE_NAME = 'servlet';
    
    /**
     * Array with the modules this module is depending on.
     * 
     * @var array
     */
    protected $dependencies = array('directory');
    
    /**
     * Array with the initialized applications.
     * 
     * @var array
     */
    protected $applications;
    
    /**
     * The servers context instance.
     * 
     * @var \TechDivision\ApplicationServer\Interface\ContextInterface
     */
    protected $context;
    
    /**
     * Injext the servers context instance.
     * 
     * @param \TechDivision\ApplicationServer\Interface\ContextInterface $context The context instance
     * 
     * @return void
     */
    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }
    
    /**
     * Returns the unique module name.
     * 
     * @return string The unique module name
     */
    public function getModuleName()
    {
        return ServletModule::MODULE_NAME;
    }
    
    /**
     * Returns the array with the modules this module is depending on.
     * 
     * @return array The array with the dependencies
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
    
    /**
     * Initialize the module with the server and the module configuration.
     * 
     * @param \TechDivision\WebServer\ServerConfigInterface $serverConfig The server configuration
     * @param \TechDivision\WebServer\ModuleConfigInterface $moduleConfig The module configuration
     * 
     * @return void
     */
    public function init(ServerConfigInterface $serverConfig, ModuleConfigInterface $moduleConfig)
    {
        
        // initialize the deployment
        $deployment = $this->newInstance(
            $moduleConfig->getValue('deployment/type'),
            array(
                $this->getContext(),
                $containerNode
            )
        );
        
        // initialize the
        $this->applications = $deployment->deploy()->getApplications();
    }
    
    /**
     * Processes the request.
     * 
     * @param \TechDivision\Http\HttpRequestInterface  $request  The Http request instance
     * @param \TechDivision\Http\HttpResponseInterface $response The Http response instance
     * 
     * @return void
     * @throws \TechDivision\WebServer\Exceptions\ModuleException Is thrown if an error during module processing occurs
     */
    public function handle(HttpRequestInterface $request, HttpResponseInterface $response)
    {
        
        try {
                
            // try to locate the application and the servlet that could service the current request
            $applicationInfo = $this->locate($request);
            
            // explode the application information
            list ($application, $documentRoot, $isVhost) = $applicationInfo;

            // intialize servlet request/response
            $servletRequest = $this->newInstance('TechDivision\ServletContainer\Http\HttpServletRequest', array($request));
            $servletResponse = $this->newInstance('TechDivision\ServletContainer\Http\HttpServletResponse', array($response));
            
            // set the application context path + Http document root (for legacy applications)
            $servletRequest->setContextPath($contextPath = '/' . $application->getName());
            
            // prepare the path info for the servlet request
            if ($isVhost === true) {
                $servletRequest->setPathInfo($request->getUri());
            } else {
                // strip the context path if we're NOT in a vhost
                $servletRequest->setUri(
                    substr_replace($request->getUri(), '', 0, strlen($contextPath))
                );
            }
            
            // locate the servlet that has to handle the request
            $servlet = $application->locate($servletRequest);
            
            // set the servlet path
            $servletRequest->setServletPath(get_class($servlet));
            
            // inject shutdown handler
            $servlet->injectShutdownHandler(
                $this->newInstance(
                    'TechDivision\ServletContainer\Servlets\DefaultShutdownHandler',
                    array(
                        $client,
                        $servletResponse
                    )
                )
            );

            // inject authentication manager
            $servlet->injectAuthenticationManager(
                $this->newInstance('TechDivision\ServletContainer\AuthenticationManager')
            );

            // let the servlet process the request send it back to the client
            $servlet->service($servletRequest, $servletResponse);
        
        } catch (\Exception $e) {
            throw new ModuleException($e);
        }
    }
    
    /**
     * Tries to find an application that matches the passed request.
     * 
     * @param HttpRequestInterface $request The request instance to locate the application for
     * 
     * @return array The application info that matches the request
     * @throws \TechDivision\ServletContainer\Exceptions\BadRequestException Is thrown if no application matches the request
     */
    protected function locate(HttpRequestInterface $request)
    {
        
        // prepare the URL to be matched
        $url = $request->getServerName() . $request->getUri();
        
        // try to find the application by match it one of the prepared patterns
        foreach ($this->getApplications() as $pattern => $applicationInfo) {
        
            // try to match a registered application with the passed request
            if (preg_match($pattern, $url) === 1) {
                return $applicationInfo;
            }
        }
        
        // if not throw a bad request exception
        throw new BadRequestException(
            sprintf(
                "Can't find application for URI %s",
                $request->getUri()
            )
        );
    }

    /**
     * Returns a new instance of the passed class name.
     *
     * @param string $className The fully qualified class name to return the instance for
     * @param array  $args      Arguments to pass to the constructor of the instance
     *
     * @return mixed The instance itself
     */
    public function newInstance($className, array $args = array())
    {
        return $this->getContext()->newInstance($className, $args);
    }
    
    /**
     * Returns the servers context instance.
     * 
     * @return \TechDivision\ApplicationServer\Interface\ContextInterface The context instance
     */
    protected function getContext()
    {
        return $this->context;
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
