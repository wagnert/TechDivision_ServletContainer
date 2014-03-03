<?php

/**
 * TechDivision\ServletContainer\Modules\LogModule
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

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ApplicationServer\Interfaces\ContainerInterface;

/**
 * This module provides basic logging functionality.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 * @link       http://httpd.apache.org/docs/2.4/mod/mod_log_config.html
 */
class LogModule extends AbstractModule
{

    /**
     * Holds access logger instance
     *
     * @var \TechDivision\ServletContainer\Http\AccessLogger
     */
    protected $accessLogger;

    /**
     * Returns the access logger instance.
     *
     * @return \TechDivision\ServletContainer\Http\AccessLogger The initialized access logger instance
     */
    public function getAccessLogger()
    {
        return $this->accessLogger;
    }
    
    /**
     * Initializes the module.
     * 
     * @return void
     * @see \TechDivision\ServletContainer\Modules\Module::init()
     */
    public function init()
    {
        $this->accessLogger = $this->newInstance('TechDivision\ServletContainer\Http\AccessLogger');
    }
    
    /**
     * Logs the request in common log format.
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
        $this->getAccessLogger()->log($request, $response);
    }
}
