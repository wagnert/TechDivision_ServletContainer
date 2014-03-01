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
     * Holds access logger instance
     *
     * @var \TechDivision\ServletContainer\Http\AccessLogger
     */
    protected $accessLogger;
    
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
