<?php

/**
 * TechDivision\ServletContainer\AuthenticationManager
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
 * @author    Florian Sydekum <fs@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Authentication\AuthenticationAdapter;

/**
 * The authentication manager handles request which need Http authentication.
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Florian Sydekum <fs@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class AuthenticationManager
{

    /**
     * Handles request in order to authenticate.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     * @param \TechDivision\ServletContainer\Interfaces\Servlet   $servlet         The servlet to handle the request for
     *
     * @return boolean TRUE if the authentication has been successfull, else FALSE
     */
    public function handleRequest(ServletRequest $servletRequest, ServletResponse $servletResponse, Servlet $servlet)
    {
        
        // load security configuration
        $securityConfig = $servlet->getSecuredUrlConfig();
        $configuredAuthType = $securityConfig['auth_type'];

        // check the authentication type
        switch ($configuredAuthType) {
            case "Basic":
                $authImplementation =  'TechDivision\ServletContainer\Authentication\BasicAuthentication';
                break;
            case "Digest":
                $authImplementation =  'TechDivision\ServletContainer\Authentication\DigestAuthentication';
                break;
            default:
                throw new \Exception('AuthenticationType is unknown');
        }

        // initialize the authentication manager
        $auth = $servlet->getServletManager()->getApplication()->newInstance($authImplementation);
        $auth->init($servlet, $servletRequest, $servletResponse);

        // authenticate the request
        return $auth->authenticate();
    }
}
