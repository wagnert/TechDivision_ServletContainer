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
 *              Philipp Dittert <p.dittert@techdivision.com>
 */

class HttpRequest implements Request
{

    /**
     * Separator between Header and Content (e.g. POST-Request)
     *
     * @var string
     */
    protected $headerContentSeparator = "\r\n\r\n";


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
     */
    public function __construct()
    {
        // init session manager
        $this->sessionManager = new PersistentSessionManager();
    }

    /**
     * validate actual InputStream
     *
     * @param string $buffer InputStream
     * @return void
     */
    protected function initFromRawHeader($buffer)
    {
        // parse method uri and http version
        list($method, $uri, $version) = explode(" ", trim(strtok($buffer, "\n")));

        $this->setMethod($method);
        $this->setUri($uri);
        $this->setVersion($version);
        $this->setHeaders($this->parseHeaders($buffer));

        // parsing for Servername and Port
        list($serverName, $serverPort) = explode(":", $this->getHeader('Host'));

        // set Servername and Serverport attributes
        $this->setServerName($serverName);
        $this->setServerPort($serverPort);

        // get PathInfo from URI and sets to Attribute
        $pathInfo = $this->parsePathInfo($this->getUri());
        $this->setPathInfo($pathInfo);

        // set intial ServerVars
        $this->initServerVars();

        // check if php script is called to set script and php info
        if (pathinfo($this->getPathInfo(), PATHINFO_EXTENSION) == 'php') {
            $this->setServerVar('SCRIPT_FILENAME', $this->getServerVar('DOCUMENT_ROOT') . $this->getPathInfo());
            $this->setServerVar('SCRIPT_NAME', $this->getPathInfo());
            $this->setServerVar('PHP_SELF', $this->getPathInfo());
        }

        // set accepted encoding data
        $this->acceptedEncodings = explode(',', $this->getHeader('Accept-Encoding'));
    }

    /**
     * Parsing URI for PathInfo
     *
     * @param string $uri
     * @return string
     */
    public function parsePathInfo($uri)
    {
        $url = parse_url($uri);
        // parse path
        if (array_key_exists('path', $url)) {
            return $url['path'];
        }
    }

    /**
     * init basic Server Vars
     *
     *@return void
     */
    protected function initServerVars()
    {
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
    }

    /**
     * parsing header
     *
     * @param string $var RawHeader
     * @return array
     */
    protected function parseHeaders($var)
    {
        $headers=array();
        if (!function_exists('http_parse_headers')) {
            foreach (explode("\n", $var) as $i => $h) {
                $h = explode(':', $h, 2);
                if (isset($h[1])) {
                    $headers[$h[0]] = trim($h[1]);
                }
            }
        } else {
            $headers = http_parse_headers($var);
        }
        return $headers;
    }

    /**
     * validates the header
     *
     * @param string $buffer Inputstream from socket
     * @return mixed
     */
    public function isHeaderCompleteAndValid($buffer) {

        $this->initFromRawHeader($buffer);
        return TRUE;
    }

    /**
     * checks if the Request is received completely
     *
     * @return boolean
     */
    public function isComplete()
    {
        if ($this->getHeader('content-length') == strlen($this->getContent())) {
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Transform QueryString into Array
     * @param $queryString
     * @return mixed
     */
    protected function parseParameterMap($queryString)
    {
        parse_str($queryString, $paramMap);
        return $paramMap;
    }

    /**
     * Set ParameterMap
     *
     * @param array $paramMap
     * @return void
     */
    protected function setParameterMap($paramMap)
    {
        $this->paramMap = $paramMap;
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
     * Returns header info by given key
     *
     * @param string $key
     * @return string
     */
    public function getHeader($key)
    {
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        }
    }

    /**
     * save complete QueryString into Parameters var (Tomcat 6 compatibility)
     *
     * @param string $qs QueryString
     * @return void
     */
    protected function setParameters($qs)
    {
        $this->parameters = $qs;
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
     * Sets content
     *
     * @param $content
     * @return void
     */
    protected function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Return content
     *
     * @return string $content
     */
    protected function getContent()
    {
        return $this->content;
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
     * Sets server name
     *
     * @param string $serverName Servername
     * @return void
     */
    protected function setServerName($serverName)
    {
        return $this->serverName = $serverName;
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
     * Sets server port
     *
     * @param string $serverPort Serverport
     * @return void
     */
    protected function setServerPort($serverPort)
    {
        return $this->serverPort = $serverPort;
    }

    /**
     * Returns path info
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * Sets path info
     *
     * @param string $pathInfo Pathinfo
     * @return void
     */
    protected function setPathInfo($pathInfo)
    {
        return $this->pathInfo = $pathInfo;
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
     * Set headers data
     *
     * @param array $headers
     * @return void
     */
    protected function setHeaders($headers)
    {
        $this->headers = $headers;
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
     * Set request method
     *
     * @param string $method Request-Method
     * @return void
     */
    protected function setMethod($method)
    {
        $this->method = $method;
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
     * Set request uri
     *
     * @param string $uri URI
     * @return void
     */
    protected function setUri($uri)
    {
        $this->uri = $uri;
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
     * Set protocol version
     *
     * @param string $version protocol version
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Returns params data
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Returns the session for this request.
     *
     * @return ServletSession
     */
    public function getSession()
    {

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