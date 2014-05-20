<?php

/**
 * TechDivision\ServletContainer\Container
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
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractContainer;
use TechDivision\ServletContainer\Exceptions\BadRequestException;
use TechDivision\ServletContainer\Http\AccessLogger;

/**
 * Class Container
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class Container extends AbstractContainer
{
    
    /**
     * The containers modules.
     * 
     * @var array
     */
    protected $modules;
    
    /**
     * Initializes the container with the initial context, the unique container ID
     * and the deployed applications.
     *
     * @param \TechDivision\ApplicationServer\InitialContext                         $initialContext The initial contextinstance
     * @param \TechDivision\ApplicationServer\Api\Node\ContainerNode                 $containerNode  The containers UUID
     * @param array<\TechDivision\ApplicationServer\Interfaces\ApplicationInterface> $applications   The application instance
     *
     * @todo Application deployment only works this way because of Thread compatibilty
     */
    public function __construct($initialContext, $containerNode, $applications)
    {
        
        // call parent constructor
        parent::__construct($initialContext, $containerNode, $applications);
        
        // @todo Add module names to system configuration
        $moduleNames = array(
            'TechDivision\ServletContainer\Modules\CoreModule',
            'TechDivision\ServletContainer\Modules\LogModule',
            'TechDivision\ServletContainer\Modules\DirectoryModule',
            'TechDivision\ServletContainer\Modules\ServletModule'
        );
        
        // initialize the array with the modules
        $modules = array();
        
        // instanciate the module and initialize it
        foreach ($moduleNames as $key => $className) {
            $modules[$key] = $this->newInstance($className, array($this));
            $modules[$key]->init();
        }
        
        // set the array with the modules
        $this->modules = $modules;
    }
    
    /**
     * Returns the array with the initialized modules
     * 
     * @return array The initialized modules
     */
    public function getModules()
    {
        return $this->modules;
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
}
