<?php
/**
 * \TechDivision\ServletContainer\Session\SessionManager
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Session
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

/**
 * Interface SessionManager
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Session
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface SessionManager
{

    /**
     * Tries to find a session for the given request. If no session
     * is found, a new one is created and assigned to the request.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance
     *
     * @return ServletSession
     */
    public function getSessionForRequest(Request $request);
}
