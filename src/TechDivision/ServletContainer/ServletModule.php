<?php


class ServletModule implements ModuleInterface
{
    
    const MODULE_NAME = 'servlet';
    
    protected $applications;
    
    protected $initialContext;
    
    protected $dependencies = array(
        'TechDivision\WebServer\Modules\DirectoryModule'
    );
    
    public function getModuleName()
    {
        return ServletModule::MODULE_NAME;
    }
    
    public function injectInitialContext(InitialContext $initalContext)
    {
        $this->initialContext = $initialContext;
    }
    
    public function init(ServerConfigInterface $serverConfig, ModuleConfigInterface $moduleConfig)
    {
        
        $deployment = $this->getInitialContext()->newInstance(
            $moduleConfig->getValue('deployment/type'),
            array(
                $this->getInitialContext(),
                $containerNode
            )
        );
        
        $this->applications = $deployment->deploy()->getApplications();
    }
    
    /**
     * 
     * @param HttpRequestInterface $req
     * @param HttpResponseInterface $res
     * 
     * @throws \TechDivision\WebServer\Exceptions\ModuleException
     */
    public function process(HttpRequestInterface $req, HttpResponseInterface $res)
    {

        // @todo Convert request/response => servlet request/response
        
        try {
            
            // load the application to handle the request
            $application = $this->findApplication($request);
            
            // try to locate a servlet which could service the current request
            $servlet = $application->locate($request);
            
            // inject shutdown handler
            $servlet->injectShutdownHandler($this->newInstance('TechDivision\ServletContainer\Servlets\DefaultShutdownHandler', array(
                $client,
                $response
            )));
            
            // inject authentication manager
            $servlet->injectAuthenticationManager($this->newInstance('TechDivision\ServletContainer\AuthenticationManager', array()));
            
            // let the servlet process the request send it back to the client
            $servlet->service($request, $response);
        
        } catch (\Exception $e) {
            throw new ModuleException($e);
        }
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

        // load the server name
        $serverName = $servletRequest->getServerName();

        // load the array with the applications
        $applications = $this->getApplications();

        // iterate over the applications and check if one of the VHosts match the request
        foreach ($applications as $application) {
            
            if ($application->isVhostOf($serverName)) {
                
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
            
            // load the application
            $application = $applications[$applicationName];
            
            $servletRequest->setWebappName($application->getName());

            return $application;
        }

        // if not throw an exception
        throw new BadRequestException("Can\'t find application for '$applicationName'");
    }
    
    public function getInitialContext()
    {
        return $this->initialContext;
    }
    
    public function getApplications()
    {
        return $this->applications;
    }
    
    public function getDependencies()
    {
        return $this->dependencies;
    }
}
