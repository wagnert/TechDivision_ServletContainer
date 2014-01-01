<?php

/**
 * TechDivision\ServletContainer\ServletManager
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Authentication;


/**
 * Abstract class for authentication adapters.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Philipp Dittert <pd@techdivision.com>
 */

class BasicAuthentication extends AbstractAuthentication
{

    public function authenticate()
    {
        $config = $this->getServlet()->getSecuredUrlConfig();
        $req = $this->getRequest();
        $res = $this->getResponse();

        $realm = $config['realm'];
        $adapterType = $config['adapter_type'];
        $options = $config['options'];

        // if client provided authentication data
        if ($authorizationData = $req->getHeader('Authorization')) {
            list($authType, $data) = explode(' ', $authorizationData);

            // handle authentication method and get credentials
            $credentials = null;
            if ($authType == self::AUTHENTICATION_METHOD_BASIC) {
                $credentials = $this->basic($data);
            }

            // if credentials are provided and authorization method is the same as configured
            if ($credentials) {
                // get real credentials
                list($user, $pwd) = explode(':', $credentials);

                // instantiate configured authentication adapter

                $authAdapter = $this->getServlet()->getServletManager()->getApplication()->newInstance(
                    'TechDivision\ServletContainer\Authentication\Adapters\\' . ucfirst($adapterType) . 'Adapter',
                    array($options, $this->getServlet())
                );

                // delegate authentication to adapter
                if ($authAdapter->authenticate($user, $pwd)) {
                    return true;
                }
            }
        }

        // either authentication data was not provided or authentication failed
        $res->addHeader("status", 'HTTP/1.1 401 Authentication required');
        $res->addHeader("WWW-Authenticate", self::AUTHENTICATION_METHOD_BASIC . ' ' . 'realm="' . $realm . '"');
        $res->setContent("<html><head><title>401 Authorization Required</title></head><body><h1>401 Authorization Required</h1><p>This server could not verify that you are authorized to access the document requested. Either you supplied the wrong credentials (e.g., bad password), or your browser doesn't understand how to supply the credentials required. Confused</p></body></html>");
        return false;
    }

    /**
     * Handles basic authentication method.
     *
     * @param $data
     * @return string
     */
    protected function basic($data)
    {
        return base64_decode($data);
    }
} 