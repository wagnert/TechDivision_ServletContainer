<?php

/**
 * TechDivision\ServletContainer\Modules\Module
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

/**
 * The interface for all modules.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Modules
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface Module
{
    
    /**
     * Initializes the module.
     * 
     * @return void
     */
    public function init();
    
    /**
     * Handles the passed request.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request  $request  The request to be handled
     * @param \TechDivision\ServletContainer\Interfaces\Response $response The response instance
     * 
     * @return void
     */
    public function handle(Request $request, Response $response);
}
