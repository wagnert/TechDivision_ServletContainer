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
    protected $vhosts;
    
    /**
     * Returns the array with the containers virtual host configuration.
     * 
     * @return array The array with the containers virtual host configuration
     */
    protected function getVhosts()
    {
        return $this->vhosts;
    }
    
    /**
     * Initializes the module.
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::init()
     */
    public function init()
    {
        
        error_log(__METHOD__ . ':' . __LINE__);
        
        // iterate over all registered applications of the container
        foreach ($this->getContainer()->getApplications() as $urlPattern => $applicationInfo) {
                
            // explode the application information
            list ($application, $documentRoot, $isVhost) = $applicationInfo;
            
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
     * @param \TechDivision\ServletContainer\Interfaces\Request  $request  The request to be handled
     * @param \TechDivision\ServletContainer\Interfaces\Response $response The response instance
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::handle()
     */
    public function handle(Request $request, Response $response)
    {
        
        error_log(__METHOD__ . ':' . __LINE__);
        
        // set the request to NOT dispatched
        $request->setDispatched(false);

        // initialize response and add accepted encoding methods
        $response->initHeaders();
        $response->setAcceptedEncodings($request->getAcceptedEncodings());
        $response->addHeader(Header::HEADER_NAME_STATUS, "{$request->getVersion()} 200 OK");
        
        if (array_key_exists($serverName = $request->getServerName(), $vhosts = $this->getVhosts()) === true) {
            $request->setServerVar('DOCUMENT_ROOT', $request->getServerVar('DOCUMENT_ROOT') . $vhosts[$serverName]);
        }
    }
}
