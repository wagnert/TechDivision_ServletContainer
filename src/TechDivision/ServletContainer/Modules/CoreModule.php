<?php

/**
 * TechDivision\ServletContainer\Modules\CoreModule
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

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ApplicationServer\Interfaces\ContainerInterface;

/**
 * The core module that prepares the request.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 * @link       http://httpd.apache.org/docs/2.4/mod/core.html
 */
class CoreModule extends AbstractModule
{
    
    /**
     * The virtual host configuration for the container.
     * 
     * @var array
     */
    protected $vhosts = array();
    
    protected $documentRoot;
    
    protected $path;
    
    protected $serverSoftware;
    
    protected $serverAdmin;
    
    /**
     * Returns the array with the containers virtual host configuration.
     * 
     * @return array The array with the containers virtual host configuration
     */
    protected function getVhosts()
    {
        return $this->vhosts;
    }
    
    protected function getDocumentRoot()
    {
        return $this->documentRoot;
    }
    
    protected function getPath()
    {
        return $this->path;
    }
    
    protected function getServerSoftware()
    {
        return $this->serverSoftware;
    }
    
    /**
     * 
     */
    protected function getServerAdmin()
    {
        return $this->serverAdmin;
    }
    
    /**
     * Initializes the module.
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::init()
     */
    public function init()
    {
        
        // prepare server and path information
        $this->documentRoot = $this->getContainer()->getWebappsDir();
        $this->path = $this->getContainer()->getBaseDirectory(DIRECTORY_SEPARATOR . 'bin') . PATH_SEPARATOR . getenv('PATH');
        $this->serverSoftware = $this->getContainer()->getContainerNode()->getHost()->getServerSoftware();
        $this->serverAdmin = $this->getContainer()->getContainerNode()->getHost()->getServerAdmin();
        
        // iterate over all registered applications of the container
        foreach ($this->getContainer()->getApplications() as $urlPattern => $application) {
            
            // iterate over a applications vhost/alias configuration
            foreach ($application->getVhosts() as $vhost) {
                
                // set the server name as key and the app base directory
                $this->vhosts[$vhost->getName()] = $vhost->getAppBase();
                
                // add the base directory for each alias also
                foreach ($vhost->getAliases() as $alias) {
                    $this->vhosts[$alias] = $vhost->getAppBase();
                }
            }
        }
    }
    
    /**
     * Initializes the response instance by adding the apropriate headers.
     * 
     * Handles the passed request by extending the DOCUMENT_ROOT with the webapp path depending 
     * on the server name found in the passed request.
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
        
        // set the request to NOT dispatched
        $request->setDispatched(false);
        
        // set the server and the path information
        $request->setServerVar('PATH', $this->getPath());
        $request->setServerVar('SERVER_SOFTWARE', $this->getServerSoftware());
        $request->setServerVar('SERVER_ADMIN', $this->getServerAdmin());
        $request->setServerVar('DOCUMENT_ROOT', $this->getDocumentRoot());
        
        // initialize response and add accepted encoding methods
        $response->initHeaders();
        $response->setAcceptedEncodings($request->getAcceptedEncodings());
        $response->addHeader(Header::HEADER_NAME_STATUS, "{$request->getVersion()} 200 OK");
        
        // if the request is related with a vhost, prepare the document root
        if (array_key_exists($serverName = $request->getServerName(), $vhosts = $this->getVhosts()) === true) {
            $request->setServerVar('DOCUMENT_ROOT', $request->getServerVar('DOCUMENT_ROOT') . $vhosts[$serverName]);
        }
    }
}
