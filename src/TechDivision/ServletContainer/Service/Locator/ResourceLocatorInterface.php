<?php

/**
 * TechDivision\ServletContainer\Service\Locator\ResourceLocatorInterface
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
namespace TechDivision\ServletContainer\Service\Locator;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Servlet;

/**
 * Interface for the resource locator instances.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Service
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface ResourceLocatorInterface
{

    /**
     * Tries to locate the resource related with the request.
     *
     * @param Request $request The request instance to return the servlet for
     *
     * @return \TechDivision\ServletContainer\Interfaces\Servlet The requested servlet
     */
    public function locate(Request $request);
}
