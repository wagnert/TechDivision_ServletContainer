<?php
/**
 * TechDivision\ServletContainer\ServletManager
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Florian Sydekum <fs@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Authentication\AuthenticationAdapter;

/**
 * The authentication manager handles request which need http authentication.
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Florian Sydekum <fs@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
class AuthenticationManager
{


    /**
     * Handles request in order to authenticate.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req     The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res     The response instance
     * @param \TechDivision\ServletContainer\Interfaces\Servlet  $servlet The servlet to handle the request for
     *
     * @return bool
     */
    public function handleRequest(Request $req, Response $res, Servlet $servlet)
    {
        $securityConfig = $servlet->getSecuredUrlConfig();
        $configuredAuthType = $securityConfig['auth_type'];

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


        $auth = $servlet->getServletManager()->getApplication()->newInstance($authImplementation);

        $auth->init($servlet, $req, $res);

        return $auth->authenticate();

    }
}
