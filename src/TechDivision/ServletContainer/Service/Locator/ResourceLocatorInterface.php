<?php

/**
 * TechDivision\ServletContainer\Service\Locator\ResourceLocatorInterface
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Service\Locator;

use TechDivision\ServletContainer\Interfaces\ServletRequest;

/**
 * Interface for the resource locator instances.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 */
interface ResourceLocatorInterface {

    /**
     * Tries to locate the resource related with the request.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletRequest $request
     * @return \TechDivision\ServletContainer\Interfaces\Servlet The servlet that serves the request
     */
    public function locate(ServletRequest $request);
}
