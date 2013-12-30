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
 * Authentication adapter for htpasswd file.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Florian Sydekum <fs@techdivision.com>
 */
class HtpasswdAdapter extends AuthenticationAdapter
{

    /**
     * @var string $htpasswd The content of the htpasswd file.
     */
    protected $htpasswd;

    /**
     * @var string $filename The filename of the htpasswd file.
     */
    protected $filename;

    public function __construct($options, Servlet $servlet)
    {
        parent::__construct($options, $servlet);
        $this->filename = $options['file'];
        $this->init();
    }

    /**
     * Initializes the adapter.
     */
    public function init()
    {
        // get current web app path.
        $webAppPath = $this->servlet->getServletManager()->getWebappPath();

        // get content of htpasswd file.
        $htpasswdData = file_get_contents($webAppPath . DS . 'WEB-INF' . DS . $this->filename);
        $htpasswdData = explode('\n', $htpasswdData);

        // prepare htpasswd entries
        $this->htpasswd = array();
        foreach ($htpasswdData as $entry) {
            list($user, $pwd) = explode(':', $entry);
            $this->htpasswd[$user] = $pwd;
        }
    }

    /**
     *  Authenticates a user/password combination.
     *
     * @param string $user
     * @param string $pwd
     * @return bool
     */
    public function authenticate($user, $pwd)
    {
        // if user is valid
        if (in_array($user, $this->htpasswd)) {
            // check if password correct
            if (crypt($pwd) == $this->htpasswd[$user]) {
                return true;
            }
        }
        return false;
    }
}