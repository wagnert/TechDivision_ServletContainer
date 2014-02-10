<?php
/**
 * TechDivision\ServletContainer\Interfaces\LocatorInterface
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

use TechDivision\ServletContainer\Interfaces\Request;

/**
 * Interface for all resource locators.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface LocatorInterface
{

    /**
     * Locates the servlet by given request instance
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance
     *
     * @return mixed
     */
    public function locate(Request $request);
}
