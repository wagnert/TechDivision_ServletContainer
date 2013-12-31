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

use TechDivision\ServletContainer\Interfaces\Servlet;

/**
 * Abstract class for authentication adapters.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Florian Sydekum <fs@techdivision.com>
 */
abstract class AuthenticationAdapter
{
    /**
     * @var array $options Necessary options for specific adapter.
     */
    protected $options;

    /**
     * @var Servlet $servlet Current servlet which needs authentication.
     */
    protected $servlet;

    /**
     * Instantiates an authentication adapter
     *
     * @param array $options Necessary options for specific adapter.
     */
    public function __construct($options, Servlet $servlet)
    {
        $this->options = $options;
        $this->servlet = $servlet;
    }

    /**
     * Initializes the adapter.
     */
    abstract function init();

    /**
     * Authenticates a user/password combination.
     *
     * @param string $user Username
     * @param string $pwd Password
     * @param array $options Necessary options for adapter
     * @return bool
     */
    abstract function authenticate($user, $pwd);
}