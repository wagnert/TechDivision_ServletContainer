<?php

/**
 * TechDivision\ServletContainer\ServletManager
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
 * @subpackage Authentication
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Authentication;

use TechDivision\ServletContainer\Http\Header;

/**
 * Abstract class for authentication adapters.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Authentication
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class DigestAuthentication extends AbstractAuthentication
{
    /**
     * Authenticate against Backend
     *
     * @return bool
     */
    public function authenticate()
    {
        $config = $this->getServlet()->getSecuredUrlConfig();
        $req = $this->getServletRequest();
        $res = $this->getServletResponse();

        $realm = $config['realm'];
        $adapterType = $config['adapter_type'];
        $options = $config['options'];

        // if client provided authentication data
        if ($authorizationData = $req->getHeader(Header::HEADER_NAME_AUTHORIZATION)) {
            // check if Authentication is DIGEST
            if (substr($authorizationData, 0, 6) == self::AUTHENTICATION_METHOD_DIGEST) {

                $data = array();
                $parts = explode(", ", substr($authorizationData, 7));

                foreach ($parts as $element) {
                    $bits = explode("=", $element);
                    $data[$bits[0]] = str_replace('"', '', $bits[1]);
                }

                // instantiate configured authentication adapter
                $authAdapter = $this->getServlet()->getServletManager()->getApplication()->newInstance(
                    'TechDivision\ServletContainer\Authentication\Adapters\\' . ucfirst($adapterType) . 'Adapter',
                    array($options, $this->getServlet())
                );

                // delegate authentication to adapter
                if ($authAdapter->authenticate($data, $req->getMethod())) {
                    return true;
                }
            }
        }

        // either authentication data was not provided or authentication failed
        $res->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 401 Authentication required');
        $res->addHeader(Header::HEADER_NAME_WWW_AUTHENTICATE, self::AUTHENTICATION_METHOD_DIGEST . ' ' . 'realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) .'"');
        $res->setContent("<html><head><title>401 Authorization Required</title></head><body><h1>401 Authorization Required</h1><p>This server could not verify that you are authorized to access the document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required. Confused</p></body></html>");
        return false;
    }
}
