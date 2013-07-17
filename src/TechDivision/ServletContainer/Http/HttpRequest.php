<?php

/**
 * TechDivision\ServletContainer\Http\HttpRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Session\PersistentSessionManager;
use TechDivision\ServletContainer\Session\ServletSession;

/**
 * A web request implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */

class HttpRequest implements Request
{

    /**
     * Request header data
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Path info
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * Server name as called by client
     *
     * @var string
     */
    protected $serverName;

    /**
     * Server port called by client
     *
     * @var string
     */
    protected $serverPort;

    /**
     * Holds the response instance
     *
     * @var Response
     */
    protected $response;

    /**
     * The accepted encodings data
     *
     * @var array
     */
    protected $acceptedEncodings = array();

    /**
     * The request method
     *
     * @var string
     */
    protected $method;

    /**
     * Uri called by client
     *
     * @var string
     */
    protected $uri;

    /**
     * Protocol version
     *
     * @var string
     */
    protected $version;

    /**
     * Query string with params
     *
     * @var string
     */
    protected $queryString;

    /**
     * Params data
     *
     * @var array
     */
    protected $params = array();

    /**
     * Server data
     *
     * @var array
     */
    protected $server = array();

    /**
     * Session Manager instance
     *
     * @var PersistentSessionManager
     */
    protected $sessionManager;

    /**
     * The Session
     *
     * @var ServletSession
     */
    protected $session;

    /**
     * Constructor
     *
     * @param $data The raw header data
     */
    public function __construct($data)
    {
        // init from raw header
        $this->initFromRawHeader($data);
        // init session manager
        $this->sessionManager = new PersistentSessionManager();
    }

    /**
     * Creates Request by given raw header data
     *
     * @param string $rawHeaderData
     * @return array
     */
    public function initFromRawHeader($rawHeaderData)
    {
        // parse raw headers
        // if PECL pecl_http >= 0.10.0 is not used
        if (!function_exists('http_parse_headers')) {
            foreach (explode("\n", $rawHeaderData) as $i => $h) {
                $h = explode(':', $h, 2);
                if (isset($h[1])) {
                    $this->headers[$h[0]] = trim($h[1]);
                }
            }
        } else {
            $this->headers = http_parse_headers($rawHeaderData);
        }
        // parse method uri and http version
        list($this->method, $this->uri, $this->version) = explode(" ", trim(strtok($rawHeaderData, "\n")));
        // parse servername and port
        list($this->serverName, $this->serverPort) = explode(":", $this->getHeader('Host'));
        // parse url
        $url = parse_url($this->uri);
        // parse path
        if (array_key_exists('path', $url)) {
            $this->pathInfo = $url['path'];
        }
        // parse query params
        if (array_key_exists('query', $url)) {
            $this->queryString = $url['query'];
            parse_str($url['query'], $this->params);
        }
        // set server vars
        $this->server = array(
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
            'QUERY_STRING' => $this->getQueryString(),
            'REQUEST_URI' => $this->getUri(),
            'REQUEST_TIME' => time(),
        );
        // check if php script is called to set script and php info
        if (pathinfo($this->pathInfo, PATHINFO_EXTENSION) == 'php') {
            $this->setServerVar('SCRIPT_FILENAME', $this->getServerVar('DOCUMENT_ROOT') . $this->getPathInfo());
            $this->setServerVar('SCRIPT_NAME', $this->getPathInfo());
            $this->setServerVar('PHP_SELF', $this->getPathInfo());
        }
        // set accepted encoding data
        $this->acceptedEncodings = explode(',', $this->getHeader('Accept-Encoding'));
    }

    /**
     * Returns header info by given key
     *
     * @param string $key
     */
    public function getHeader($key)
    {
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        }
    }

    /**
     * Sets response object
     *
     * @param Response $response
     * @return void
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        // add accepted encoding methods to response
        $this->response->setAcceptedEncodings(
            $this->getAcceptedEncodings()
        );
    }

    /**
     * Returns response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns accepted encodings data
     *
     * @var array
     */
    public function getAcceptedEncodings()
    {
        return $this->acceptedEncodings;
    }

    /**
     * Returns query string
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Returns server name
     *
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * Returns server port
     *
     * @return string
     */
    public function getServerPort()
    {
        return $this->serverPort;
    }

    /**
     * Returns path info
     *
     * @return string
     */
    public function getPathInfo() {
        return $this->pathInfo;
    }

    /**
     * Returns headers data
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns request uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns protocol version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns params data
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns the session for this request.
     *
     * @return ServletSession
     */
    public function getSession() {

        if ($this->session == null) {
            $this->session = $this->sessionManager->getSessionForRequest($this);
        }

        return $this->session;
    }

    /**
     * Returns server data
     *
     * @return array
     */
    public function getServerVars()
    {
        return $this->server;
    }

    /**
     * Set specific server var data
     *
     * @param string $key
     * @param string $value
     */
    public function setServerVar($key, $value)
    {
        $this->server[$key] = $value;
    }

    /**
     * Returns specific server var data
     *
     * @param $key
     * @return mixed
     */
    public function getServerVar($key)
    {
        if (array_key_exists($key, $this->server)) {
            return $this->server[$key];
        }
    }
}