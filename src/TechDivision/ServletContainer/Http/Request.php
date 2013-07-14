<?php

/**
 * TechDivision\ServletContainer\Http\Request
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;
use TechDivision\ServletContainer\Session\PersistentSessionManager;

/**
 * A web request implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <pd@techdivision.com>
 */
class Request implements ServletRequest {

    /**
     * the complete Inputstream incoming from socket
     * @var string
     */
    protected $_inputStream;

    /**
     * Inputstream Transformed into Array
     * @var array
     */
    protected $_transformedInputStream = array();

    /**
     * Request Method (GET,POST...)
     * @var string
     */
    protected $_method;

    /**
     * Protocol (eg HTTP/1.1)
     * @var string
     */
    protected $_protocol;

    /**
     * Requested URL. if method is GET, with get parameters
     * @var string
     */
    protected $_uri;

    /**
     * Requested URL without any additional parameters
     * @var string
     */
    protected $_pathInfo;

    /**
     * Get Parameters
     * @var string
     */
    protected $_queryString;

    /**
     * all additional Header informations (eg. expires, date, content-length)
     * @var array
     */
    protected $_headers = array();

    /**
     * Reuqest content. If Method is POST there your Raw Post-Parameters
     * @var string
     */
    protected $_content = '';

    /**
     * Request Parameters (method independent)
     * @var string
     */
    protected $_parameter;

    /**
     * Parameters transformed to fit in array
     * @var array
     */
    protected $_parameterMap = array();

    /**
     * Helper attribute that stores line start for Content in $_transformedinputstream
     * @var int
     */
    protected $_contentStartId;

    /**
     * Shows if HTTP-Request is valid
     * @var bool
     */
    protected $_isValid = FALSE;

    protected $_scriptName = '';

    /**
     * @var \TechDivision\ServletContainer\Http\Request
     */
    protected $request;

    /**
     * @var \TechDivision\ServletContainer\Interfaces\ServletResponse
     */
    protected $response;

    /**
     * @var PersistentSessionManager
     */
    protected $sessionManager;

    protected $cookies = array();

    protected $session;

    protected $serverName;
    protected $httpConnection;
    protected $httpAccept;
    protected $httpCookie;
    protected $httpReferer;
    protected $httpUserAgent;
    protected $httpAcceptEncoding;
    protected $httpAcceptLanguage;

    protected $documentRoot;

    protected static $requestMethods = array(
        'Connect' => 'ConnectRequest',
        'Delete' => 'DeleteRequest',
        'Get' => 'GetRequest',
        'Head' => 'HeadRequest',
        'Options' => 'OptionsRequest',
        'Post' => 'PostRequest',
        'Put' => 'PutRequest',
        'Trace' => 'TraceRequest'
    );

    public function __construct() {
        $this->sessionManager = new PersistentSessionManager();
    }

    /**
     * Parsing inputstream and validate Request
     * @param $inputStream
     * @return mixed
     */
    static function parse($inputStream) {
        $method = strstr($inputStream, " ", true);

        $req = Request::factory($method);
        $req->transform($inputStream)
            ->ParseServerInformation()
            ->ParseRequestInformation()
            ->ParseUriInformation()
            ->setHeaders()
            ->setContent()
            ->setParameter()
            ->validate();

        return $req;
    }

    /**
     * Returns the request state
     * @return bool
     */
    public function isValid() {
        return $this->_isValid;
    }

    /**
     * Creates a new request class based on passed request method.
     *
     * @param string $method Request method to create the class for
     * @return \TechDivision\ServletContainer\Interfaces\ServletRequest The request instance
     * @throws \Exception Is thrown if an unknown request method is requested
     */
    public static function factory($method) {
        $requestMethod =  ucfirst(strtolower($method));
        if (!array_key_exists($requestMethod, self::$requestMethods)) {
            throw new \Exception("Found invalid request method '$requestMethod'");
        }
        $className =  __NAMESPACE__ . '\\' . self::$requestMethods[$requestMethod];
        return new $className;
    }

    /**
     * Returns Pathinfo
     * @return string
     */
    public function getPathInfo() {
        return $this->_pathInfo;
    }

    /**
     * @param $pathInfo
     * @return $this
     */
    public function setPathInfo($pathInfo) {
        $this->_pathInfo = $pathInfo;
        return $this;
    }

    /**
     * @return string
     */
    public function getQueryString() {
        return $this->_queryString;
    }

    /**
     * @param $queryString
     * @return $this
     */
    public function setQueryString($queryString) {
        $this->_queryString = $queryString;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->_method;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method) {
        $this->_method = $method;
        return $this;
    }

    /**
     * @param $inputStream
     * @return $this
     */
    public function setInputStream($inputStream) {
        $this->_inputStream = $inputStream;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputStream() {
        return $this->_inputStream;
    }

    /**
     * @param $inputStream
     * @return $this
     */
    public function transform($inputStream) {
        $this->_transformedInputStream = explode("\r\n", $inputStream);
        return $this;
    }

    /**
     * @return array
     */
    public function getTransformedInputStream() {
        return $this->_transformedInputStream;
    }

    /**
     * @return string
     */
    public function getUri() {
        return $this->_uri;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUri($uri) {
        $this->_uri = $uri;
        return $this;
    }

    /**
     * @return string
     */
    public function getProtocol() {
        return $this->_protocol;
    }

    /**
     * @param $protocol
     * @return $this
     */
    public function setProtocol($protocol) {
        $this->_protocol = $protocol;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders() {
        return $this->_headers;
    }

    public function ParseUriInformation() {
        return $this;
    }
    public function parseServerInformation() {

        $transformedInputStream = $this->getTransformedInputStream();

        $serverVars = array(
            'Host' => 'serverName',
            'Port' => 'serverPort',
            'Connection' => 'httpConnection',
            'Cache-Control' => 'httpCacheControl',
            'Accept' => 'httpAccept',
            'Accept-Encoding' => 'httpAcceptEncoding',
            'Accept-Language' => 'httpAcceptLanguage',
            'Pragma' => 'pragma',
            'User-Agent' => 'httpUserAgent',
            'Cookie' => 'httpCookie',
            'Referer' => 'httpReferer'
        );

        for ($i = 1; $i < count($transformedInputStream); $i++) {
            if (trim($transformedInputStream[$i]) == '') {
                //empty line, after this the content should follow

                $i++;
                $this->setContentHelper($i);
                break;
            }
            $regs = array();

            if (preg_match("'([^: ]+): (.+)'", $transformedInputStream[$i], $regs)) {

                if (!array_key_exists($regs[1], $serverVars)) {
                    continue;
                }

                $propertyName = $serverVars[$regs[1]];
                $propertyValue = $regs[2];


                if ($propertyName == 'serverName' && !empty($propertyName)) {
                    list ($this->$propertyName, $this->serverPort) = explode(':', $propertyValue);
                } elseif (!empty($propertyName)) {
                    $this->$propertyName = $propertyValue;
                } else {
                    // propertyName is empty
                }
            }
        }

        return $this;
    }

    /**
     * parsing the Header content and return as Array
     * @return array
     */
    public function parseHeaders() {

        $transformedInputStream = $this->getTransformedInputStream();

        $headers = array();
        for ($i = 1; $i < count($transformedInputStream); $i++) {
            if (trim($transformedInputStream[$i]) == '') {
                //empty line, after this the content should follow

                $i++;
                $this->setContentHelper($i);
                break;
            }
            $regs = array();
            if (preg_match("'([^: ]+): (.+)'", $transformedInputStream[$i], $regs)) {
                $headers[(strtolower($regs[1]))] = $regs[2];
            }
        }
        return $headers;
    }

    /**
     * @return $this
     */
    public function setHeaders() {
        $this->_headers = $this->parseHeaders();
        return $this;
    }

    /**
     * Parse and set request informations (Method, uri and protocol)
     * @return $this
     */
    public function parseRequestInformation() {

        $transformedInputStream = $this->getTransformedInputStream();

        $requestInfo = explode(" ", $transformedInputStream[0]);

        $this->setMethod($requestInfo[0]);

        if (empty($requestInfo[1])) {
            $this->setUri('/');
        } else {
            $this->setUri($requestInfo[1]);
        }

        $this->setProtocol($requestInfo[2]);

        return $this;
    }

    /**
     * @return int
     */
    public function getContentHelper() {
        return $this->_contentStartId;
    }

    /**
     * @param $contentStartId
     * @return $this
     */
    public function setContentHelper($contentStartId) {
        $this->_contentStartId = $contentStartId;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->_content;
    }

    /**
     * @return $this
     */
    public function setContent() {
        $content = $this->parseContent();
        $this->_content = $content;
        return $this;
    }

    /**
     * @return string
     */
    protected function parseContent() {
        $tis = $this->getTransformedInputStream();
        $id = $this->getContentHelper();
        $content = '';

        for ($id; $id < count($tis); $id++) {
            $content .= $tis[$id] . "\r\n";
        }

        return trim($content);
    }

    /**
     * @return array
     */
    public function getParameterMap(){
        return $this->_parameterMap;
    }

    /**
     * @return string
     */
    public function getParameter() {
        return $this->_parameter;
    }

    /**
     * @return $this
     */
    public function setParameterMap() {
        $this->_parameterMap = $this->parseParameter($this->getParameter());
        return $this;
    }

    /**
     * @param $queryString
     * @return mixed
     */
    public function parseParameter($queryString) {
        parse_str($queryString, $paramMap);
        return $paramMap;
    }

    /**
     * validate request
     * @TODO: Dummy implementationen
     * @return bool
     */
    protected function validate() {
        $this->_isValid = TRUE;
    }

    public function setScriptName($scriptName) {
        $this->_scriptName = $scriptName;
    }

    public function getScriptName() {
        return $this->_scriptName;
    }

    public function getServerVars() {

       return array(
            'HTTP_HOST' => 'localhost',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.65 Safari/537.31',
            'HTTP_REFERER' => 'http://localhost/symfony2/web/app_dev.php',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            // 'HTTP_COOKIE' => 'PHPSESSID=ot2d5n4js6rgvciua4bg93bsl2',
            'PATH' => '/usr/bin:/bin:/usr/sbin:/sbin',
            'SERVER_SIGNATURE' => '',
            'SERVER_SOFTWARE' => 'Apache/2.2.22 (Unix) DAV/2 PHP/5.4.11 mod_ssl/2.2.22 OpenSSL/0.9.8r',
            'SERVER_NAME' => 'localhost',
            'SERVER_ADDR' => '::1',
            'SERVER_PORT' => '8586',
            'REMOTE_HOST' => 'localhost',
            'REMOTE_ADDR' => '::1',
            'DOCUMENT_ROOT' => '/opt/appserver/webapps/',
            'SERVER_ADMIN' => 'admin@appserver.io',
            'SCRIPT_FILENAME' => '/opt/appserver/webapps' . $this->getScriptName(),
            'REMOTE_PORT' => '53983',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => $this->getMethod(),
            'QUERY_STRING' => $this->getQueryString(),
            'REQUEST_URI' => $this->getUri(),
            'SCRIPT_NAME' => $this->getScriptName(),
            'PATH_INFO' => '/webapps/',
            'PATH_TRANSLATED' => '/Library/WebServer/Documents/appserver/webapps/',
            'PHP_SELF' => $this->getUri(),
            'REQUEST_TIME_FLOAT' => 1368976493.147,
            'REQUEST_TIME' => 1368976493,
        );
    }

    public function hasCookie($cookieName) {
        return array_key_exists($cookieName, $this->cookies);
    }

    public function getCookie($cookieName) {
        if ($this->hasCookie($cookieName)) {
            return $this->cookies[$cookieName];
        }
    }

    public function setResponse(ServletResponse $response) {
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }

    /**
     * Returns the session for this request.
     *
     * @return Session
     */
    public function getSession() {

        if ($this->session == null) {
            $this->session = $this->sessionManager->getSessionForRequest($this);
        }

        return $this->session;
    }

    public function getServerName() {
        return $this->serverName;
    }

    /**
     * @deprec is not used anymore
     * @return string
     */
    public function getRequestUrl() {
        return $this->getPathInfo();
    }

    public function getRequestUri() {
        return $this->getUri();
    }

    public function getRequestMethod() {
        return $this->getMethod();
    }

    public function setDocumentRoot($documentRoot) {
        $this->documentRoot = $documentRoot;
    }

    public function getDocumentRoot() {
        return $this->documentRoot;
    }
}