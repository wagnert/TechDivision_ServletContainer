<?php

/**
 * TechDivision\ServletContainer\Utilities\\Http\RequestValidator
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Utilities\Http;

use TechDivision\ServletContainer\Interfaces\Validator;
use TechDivision\ServletContainer\Http\HttpRequest;

/**
 * A servlet response implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */


class RequestValidator implements Validator
{
    /**
     * Request object
     *
     * @var HttpRequest
     */
    protected $request;

    /**
     * Constructor
     *
     */
    public function __construct() {

        // initialize Request Object
        $this->request = new HttpRequest();
    }

    /**
     * Creates Request by given raw header data
     *
     * @param string $rawHeaderData
     * @return array
     */
    public function initFromRawHeader($rawHeaderData)
    {

        $header=array(); // tmp Array for header vars
        // parse raw headers
        // if PECL pecl_http >= 0.10.0 is not used
        if (!function_exists('http_parse_headers')) {
            foreach (explode("\n", $rawHeaderData) as $i => $h) {
                $h = explode(':', $h, 2);
                if (isset($h[1])) {
                    $header[$h[0]] = trim($h[1]);
                }
            }
        } else {
            $header = http_parse_headers($rawHeaderData);
        }

        // set headers
        $this->getRequest()->setHeaders($header);

        // parse method uri and http version
        list($method,$uri, $version) = explode(" ", trim(strtok($rawHeaderData, "\n")));

        $this->getRequest()->setMethod($method);
        $this->getRequest()->setUri($uri);
        $this->getRequest()->setVersion($version);

        // parse servername and port
        list($serverName,$serverPort) = explode(":", $this->getRequest()->getHeader('Host'));

        $this->getRequest()->setServerName($serverName);
        $this->getRequest()->setServerPort($serverPort);

        // parse url
        $url = parse_url($this->getRequest()->getUri);
        // parse path
        if (array_key_exists('path', $url)) {
            $this->pathInfo = $url['path'];
        }
        // parse query params
        if (array_key_exists('query', $url)) {
            $this->getRequest()->setQueryString($url['query']);
            parse_str($url['query'], $params);
            $this->getRequest()->setParams($params);
        }
        // set server vars
        $this->getRequest()->initServerVars(array(
            'HTTP_HOST' => $this->getServerName(),
            'HTTP_CONNECTION' => $this->getHeader('Connection'),
            'HTTP_ACCEPT' => $this->getHeader('Accept'),
            'HTTP_USER_AGENT' => $this->getHeader('User-Agent:'),
            'HTTP_ACCEPT_ENCODING' => $this->getHeader('Accept-Encoding'),
            'HTTP_ACCEPT_LANGUAGE' => $this->getHeader('Accept-Language'),
            'HTTP_REFERER' => $this->getHeader('Referer'),
            'PATH' => '/opt/appserver/bin',
            'SERVER_SIGNATURE' => '',
            'SERVER_SOFTWARE' => $this->getServerVar('SERVER_SOFTWARE'),
            'SERVER_NAME' => $this->getServerName(),
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => $this->getServerPort(),
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => $this->getServerVar('DOCUMENT_ROOT'),
            'SERVER_ADMIN' => $this->getServerVar('SERVER_ADMIN'),
            'SERVER_PROTOCOL' => $this->getVersion(),
            'REQUEST_METHOD' => $this->getMethod(),

            'REQUEST_URI' => $this->getUri(),
            'REQUEST_TIME' => time(),
        ));
        // check if php script is called to set script and php info
        if (pathinfo($this->getRequest()->getPathInfo, PATHINFO_EXTENSION) == 'php') {
            $this->getRequest()->setServerVar('SCRIPT_FILENAME',$this->getRequest()->getServerVar('DOCUMENT_ROOT') . $this->getRequest()->getPathInfo());
            $this->getRequest()->setServerVar('SCRIPT_NAME', $this->getRequest()->getPathInfo());
            $this->getRequest()->setServerVar('PHP_SELF', $this->getRequest()->getPathInfo());
        }
        // set accepted encoding data
        #$this->acceptedEncodings = explode(',', $this->getHeader('Accept-Encoding'));
    }

    /**
     * validates the header
     *
     * @param string $buffer Inputstream from socket
     * @return mixed
     */
    public function isHeaderCompleteAndValid($buffer) {

        $this->initFromRawHeader($buffer);
    }

    /**
     * checks if the Request is received completely
     *
     * @return boolean
     */
    public function isComplete() {

    }

    /**
     * checks if the Request is received completely
     *
     * @return \TechDivision\ServletContainer\Http\HttpRequest HttpRequest
     */
    public function getRequest() {

        //returns a valid HttpRequest object
        return $this->request;
    }

}