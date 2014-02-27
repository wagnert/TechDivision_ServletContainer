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
 * @author     Florian Sydekum <fs@techdivision.com>
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
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
 * @category   Appserver
 * @package    TechDivision_ApplicationServer
 * @subpackage Authentication
 * @author     Florian Sydekum <fs@techdivision.com>
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class HtdigestAdapter extends AuthenticationAdapter
{
    /**
     * @var array $htdigest The content of the htdigest file.
     */
    protected $htdigest = array();

    /**
     * Constructor
     *
     * @param array                                             $options The options
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet A servlet instance
     */
    public function __construct($options, Servlet $servlet)
    {
        parent::__construct($options, $servlet);
        $this->init();
    }

    /**
     * Initializes the adapter.
     *
     * @return void
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
            $this->htdigest[$user] = array('user'=>$user, 'realm'=>$realm, 'hash'=>trim($hash));
        }
    }

    /**
     * Authenticates a user/realm/H1 hash combination.
     *
     * @param array  $data      The auth data
     * @param string $reqMethod e.g. GET or POST
     *
     * @return bool
     */
    public function authenticate($data, $reqMethod)
    {
        // if user is valid
        $credentials = $this->getHtDigest();
        $user = $data['username'];
        if ($credentials[$user] && $credentials[$user]['realm'] == $data['realm']) {

            $HA1 = $credentials[$user]['hash'];
            $HA2 = md5($reqMethod.":".$data['uri']);
            $middle = ':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':';
            $response = md5($HA1.$middle.$HA2);

            if ($data['response'] == $response) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return's htdigest credential array
     *
     * @return array
     */
    protected function getHtDigest()
    {
        return $this->htdigest;
    }
}
