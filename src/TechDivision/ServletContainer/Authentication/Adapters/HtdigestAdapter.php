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
namespace TechDivision\ServletContainer\Authentication\Adapters;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Servlets\ServletConfiguration;
use TechDivision\ServletContainer\Authentication\AuthenticationAdapter;
use TechDivision\ServletContainer\Interfaces\Servlet;

/**
 * Authentication adapter for htdigest file.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Florian Sydekum <fs@techdivision.com>
 * @author Philipp Dittert <pd@techdivision.com>
 */
class HtdisgestAdapter
{
    /**
     * @var array $htdigest The content of the htdigest file.
     */
    protected $htdigest = array();

    public function __construct($options, Servlet $servlet)
    {
        parent::__construct($options, $servlet);

        $this->init();
    }

    /**
     * Initializes the adapter.
     */
    public function init()
    {
        // get current web app path.
        $webAppPath = $this->getServlet()->getServletManager()->getWebappPath();

        // get content of htdigest file.
        $htDigestData = file($webAppPath . DIRECTORY_SEPARATOR . 'WEB-INF' . DIRECTORY_SEPARATOR . $this->getFilename());

        // prepare htdigest entries
        foreach ($htDigestData as $entry) {
            list($user, $realm, $hash) = explode(':', $entry);
            $this->htdigest[$user] = array('user'=>$user, 'realm'=>$realm, 'hash'=>$hash);
        }
    }

    /**
     *  Authenticates a user/realm/H1 hash combination.
     *
     * @param string $user
     * @param string $realm
     * @param string $hash
     * @return bool
     */
    public function authenticate($user, $realm, $hash)
    {
        // if user is valid
        $credentials = $this->getHtDigest();
        if ($credentials[$user] && $credentials[$user]['realm'] == $realm) {
            if ($credentials[$user]['hash'] == $hash) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return's htdigest credential list
     *
     * @return array
     */
    protected function getHtDigest()
    {
        return $this->htdigest;
    }

}