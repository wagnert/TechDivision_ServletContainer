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
namespace TechDivision\ServletContainer;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Authentication\AuthenticationAdapter;

/**
 * The authentication manager handles request which need http authentication.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Florian Sydekum <fs@techdivision.com>
 */
class AuthenticationManager
{
    /**
     * Basic HTTP authentication method
     */
    const AUTHENTICATION_METHOD_BASIC = 'Basic';

    /**
     * Digest HTTP authentication method
     */
    const AUTHENTICATION_METHOD_DIGEST = 'Digest';

    /**
     * Handles request in order to authenticate.
     *
     * @param Request $req
     * @param Response $res
     * @param Servlet $servlet
     * @return bool
     */
    public function handleRequest(Request $req, Response $res, Servlet $servlet)
    {
        // get security configuration
        $securityConfig = $servlet->getSecuredUrlConfig();
        $configuredAuthType = $securityConfig['auth_type'];
        $realm = $securityConfig['realm'];
        $adapterType = $securityConfig['adapter_type'];
        $options = $securityConfig['options'];

        // if client provided authentication data
        if ($authorizationData = $req->getHeader('Authorization')) {
            list($authType, $data) = explode(' ', $authorizationData);

            // handle authentication method and get credentials
            $credentials = null;
            if ($authType == self::AUTHENTICATION_METHOD_BASIC) {
                $credentials = $this->basic($data);
            } elseif ($authType == self::AUTHENTICATION_METHOD_DIGEST) {
                $credentials = $this->digest($data);
            }

            // if credentials are provided and authorization method is the same as configured
            if ($credentials && $configuredAuthType == $authType) {
                // get real credentials
                list($user, $pwd) = explode(':', $credentials);

                // instantiate configured authentication adapter
                /* @var $authAdapter AuthenticationAdapter */
                $authAdapter = $servlet->getServletManager()->getApplication()->newInstance(
                    'TechDivision\ServletContainer\Authentication\Adapters\\' . ucfirst($adapterType) . 'Adapter',
                    array($options, $servlet)
                );

                // delegate authentication to adapter
                if ($authAdapter->authenticate($user, $pwd)) {
                    return true;
                }
            }
        }

        // either authentication data was not provided or authentication failed
        $res->addHeader("status", 'HTTP/1.1 401 Authentication required');
        $res->addHeader("WWW-Authenticate", $configuredAuthType . ' ' . 'realm="' . $realm . '"');
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

    /**
     * Handles digest authentication method.
     *
     * @param $data
     */
    protected function digest($data)
    {
        // TODO: Implement digest function, refactor this class

        return null;
    }
}