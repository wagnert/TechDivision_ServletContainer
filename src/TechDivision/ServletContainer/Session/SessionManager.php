<?php

/**
 * \TechDivision\ServletContainer\Session\SessionManager
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
 * @subpackage Session
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Http\ServletRequest;

/**
 * Interface for the session managers.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Session
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface SessionManager
{

    /**
     * Tries to find a session for the given request. If no session
     * is found, a new one is created and assigned to the request.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return \TechDivision\ServletContainer\Session\ServletSession The session instance
     */
    public function getSessionForRequest(ServletRequest $servletRequest);
}
