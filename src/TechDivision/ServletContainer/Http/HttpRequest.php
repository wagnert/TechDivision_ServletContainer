<?php

/**
 * TechDivision\ServletContainer\Http\HttpRequest
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
 * @subpackage Http
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Part;
use TechDivision\ServletContainer\Exceptions\InvalidHeaderException;
use TechDivision\ServletContainer\Interfaces\QueryParser;

/**
 * A http server request implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class HttpRequest implements Request
{

    /**
     * Separator between Header and Content (e. g. POST-Request).
     *
     * @var string
     */
    protected $headerContentSeparator = "\r\n\r\n";

    /**
     * Request header data.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Path info.
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * Server name as called by client.
     *
     * @var string
     */
    protected $serverName;

    /**
     * The address of the server.
     *
     * @var string $serverAddress
     */
    protected $serverAddress;

    /**
     * Server port called by client.
     *
     * @var string
     */
    protected $serverPort;

    /**
     * Clients name/IP.
     *
     * @var string
     */
    protected $clientIp;

    /**
     * Clients port.
     *
     * @var string
     */
    protected $clientPort;

    /**
     * Holds the response instance.
     *
     * @var \TechDivision\ServletContainer\Interfaces\Response
     */
    protected $response;

    /**
     * The accepted encodings data.
     *
     * @var array
     */
    protected $acceptedEncodings = array();

    /**
     * The request method.
     *
     * @var string
     */
    protected $method;

    /**
     * The request body.
     *
     * @var string
     */
    protected $content;

    /**
     * Uri called by client.
     *
     * @var string
     */
    protected $uri;

    /**
     * Protocol version.
     *
     * @var string
     */
    protected $version;

    /**
     * Query string with params.
     *
     * @var string
     */
    protected $queryString;

    /**
     * Name of the webapp related by the request.
     *
     * @var string
     */
    protected $webappName;

    /**
     * Server data.
     *
     * @var array
     */
    protected $server = array();

    /**
     * Holds the query parser.
     *
     * @var \TechDivision\ServletContainer\Interfaces\QueryParser
     */
    protected $queryParser;

    /**
     * An array that contains all request parameters.
     *
     * @var array
     */
    protected $parameterMap = array();

    /**
     * Holds collection of parts from multipart form data.
     *
     * @var array A collection of HttpPart objects
     */
    protected $parts = array();

    /**
     * Holds the part factory instance.
     *
     * @var \TechDivision\ServletContainer\Interfaces\Part
     */
    protected $part;

    /**
     * Array that contain's the cookies passed with.
     * the request.
     *
     * @var array
     */
    protected $cookies = array();
    
    /**
     * Flag that the request has been dispatched.
     * 
     * @var boolean
     */
    protected $dispatched = false;

    /**
     * Inject the query parser
     *
     * @param QueryParser $queryParser The query parser implementation to use for
     *
     * @return void
     */
    public function injectQueryParser(QueryParser $queryParser)
    {
        $this->queryParser = $queryParser;
    }

    /**
     * Inject a part factory
     *
     * @param Part $part A Part implementation with factory function
     *
     * @return void
     */
    public function injectHttpPart(Part $part)
    {
        $this->part = $part;
    }

    /**
     * Sets response object
     *
     * @param \TechDivision\ServletContainer\Interfaces\Response $response A response instance
     *
     * @return void
     */
    public function injectResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * validate actual InputStream
     *
     * @param string $buffer InputStream
     *
     * @return \TechDivision\ServletContainer\Http\HttpRequest
     *
     */
    public function initFromRawHeader($buffer)
    {
        // parse method uri and http version
        list ($method, $uri, $version) = explode(" ", trim(strtok($buffer, "\n")));

        // initialize the basic values
        $this->setMethod($method);
        $this->setUri($uri);
        $this->setVersion($version);
        $this->setHeaders($this->parseHeaders($buffer));

        // parsing for servername and port
        list ($serverName, $serverPort) = explode(":", $this->getHeader(Header::HEADER_NAME_HOST));

        // set server address, name and server port
        $this->setServerAddress(gethostbyname($serverName));
        $this->setServerName($serverName);
        $this->setServerPort($serverPort);

        // set intial server vars and cookies
        $this->initServerVars();
        $this->initCookies();

        // inject the query parser
        $this->injectQueryParser(new HttpQueryParser());

        // set accepted encoding data
        $this->acceptedEncodings = explode(',', $this->getHeader(Header::HEADER_NAME_ACCEPT_ENCODING));

        return $this;
    }

    /**
     * Parse multipart form data
     *
     * @param string $content The content to parse
     *
     * @return void
     */
    public function parseMultipartFormData($content)
    {

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $this->getHeader(Header::HEADER_NAME_CONTENT_TYPE), $matches);
        // get boundary
        $boundary = $matches[1];
        // split content by boundary
        $blocks = preg_split("/-+$boundary/", $content);
        // get rid of last -- element
        array_pop($blocks);
        // loop data blocks
        foreach ($blocks as $id => $block) {
            // of block is empty continue with next one
            if (empty($block)) {
                continue;
            }

            // check if filename is given
            if (strpos($block, '; filename="') !== false) {
                // init new part instance
                $part = $this->getHttpPartInstance();
                // seperate headers from body
                $partHeaders = strstr($block, "\n\r\n", true);
                $partBody = ltrim(strstr($block, "\n\r\n"));
                // parse part headers
                foreach (explode("\n", $partHeaders) as $i => $h) {
                    $h = explode(':', $h, 2);
                    if (isset($h[1])) {
                        $part->addHeader($h[0], trim($h[1]));
                    }
                }
                // match name and filename
                preg_match("/name=\"([^\"]*)\"; filename=\"([^\"]*)\".*$/s", $partHeaders, $matches);
                // set name
                $part->setName($matches[1]);
                // set given filename
                $part->setFilename($matches[2]);
                // put content to part
                $part->putContent(preg_replace('/.' . PHP_EOL . '$/', '', $partBody));
                // add the part instance to request
                $this->addPart($part);
                // parse all other fields as normal key value pairs
            } else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                $this->getQueryParser()->parseKeyValue($matches[1], $matches[2]);
            }
        }
    }

    /**
     * Checks if request has multipart formdata or not
     *
     * @return boolean
     */
    public function hasMultipartFormData()
    {
        // grab out boundary info
        preg_match('/boundary=(.*)$/', $this->getHeader(Header::HEADER_NAME_CONTENT_TYPE), $matches);

        return (count($matches) > 0);
    }

    /**
     * Parse request content and sets parameter map and parts
     *
     * @param string $content The content to parse
     *
     * @return void
     */
    public function parse($content)
    {
        // set content to req instance
        $this->setContent($content);

        // set and parse params within url if exist
        if ($queryString = parse_url($this->getUri(), PHP_URL_QUERY)) {
            $this->setQueryString($queryString);
            $this->getQueryParser()->parseStr($queryString);
        }

        // check if request has to be parsed depending on Content-Type header
        if ($this->getQueryParser()->isParsingRelevant($this->getHeader(Header::HEADER_NAME_CONTENT_TYPE))) {
            if ($this->hasMultipartFormData()) {
                $this->parseMultipartFormData($content);
            } else {
                $this->getQueryParser()->parseStr(urldecode($content));
            }
        }

        // finally set parameter map
        $this->setParameterMap($this->getQueryParser()->getResult());
    }

    /**
     * init basic Server Vars
     *
     * @return void
     */
    public function initServerVars()
    {
        $this->server = array(
            'DOCUMENT_ROOT' => $this->getServerVar('DOCUMENT_ROOT'),
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'HTTP_HOST' => $this->getHeader(Header::HEADER_NAME_HOST),
            'HTTP_CONNECTION' => $this->getHeader(Header::HEADER_NAME_CONNECTION),
            'HTTP_ACCEPT' => $this->getHeader(Header::HEADER_NAME_ACCEPT),
            'HTTP_USER_AGENT' => $this->getHeader(Header::HEADER_NAME_USER_AGENT),
            'HTTP_ACCEPT_ENCODING' => $this->getHeader(Header::HEADER_NAME_ACCEPT_ENCODING),
            'HTTP_ACCEPT_LANGUAGE' => $this->getHeader(Header::HEADER_NAME_ACCEPT_LANGUAGE),
            'HTTP_REFERER' => $this->getHeader(Header::HEADER_NAME_REFERER),
            'PATH' => '/opt/appserver/bin',
            'PATH_INFO' => $this->getPathInfo(),
            'REMOTE_ADDR' => '127.0.0.1',
            'REQUEST_METHOD' => $this->getMethod(),
            'REQUEST_URI' => $this->getUri(),
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
            'SERVER_ADMIN' => $this->getServerVar('SERVER_ADMIN'),
            'SERVER_PROTOCOL' => $this->getVersion(),
            'SERVER_SIGNATURE' => '',
            'SERVER_SOFTWARE' => $this->getServerVar('SERVER_SOFTWARE'),
            'SERVER_NAME' => $this->getServerName(),
            'SERVER_ADDR' => gethostbyname($this->getServerName()),
            'SERVER_PORT' => $this->getServerPort()
        );

        if ($cookie = $this->getHeader(Header::HEADER_NAME_COOKIE)) {
            $this->server['HTTP_COOKIE'] = $cookie;
        }
    }

    /**
     * Initializes the cookies found in the header.
     *
     * @return void
     */
    public function initCookies()
    {
        $cookies = explode(';', $this->getHeader(Header::HEADER_NAME_COOKIE));
        foreach ($cookies as $cookie) {
            if (!empty($cookie)) {
                list ($cookieName, $cookieValue) = explode('=', trim($cookie));
                $this->cookies[$cookieName] = new Cookie($cookieName, $cookieValue);
            }
        }
    }

    /**
     * parsing header
     *
     * @param string $var RawHeader
     *
     * @return array
     */
    protected function parseHeaders($var)
    {
        $headers = array();
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
     * Sets query string
     *
     * @param string $queryString The query string to set
     *
     * @return void
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
        $this->setServerVar('QUERY_STRING', $queryString);
    }

    /**
     * validates the header
     *
     * @param string $buffer Inputstream from socket
     *
     * @return boolean
     */
    public function isHeaderCompleteAndValid($buffer)
    {
        $this->initFromRawHeader($buffer);

        return true;
    }

    /**
     * checks if the Request is received completely
     *
     * @return boolean
     */
    public function isComplete()
    {
        return true;
    }

    /**
     * Set ParameterMap
     *
     * @param array $parameterMap The parameter map array
     *
     * @return void
     */
    protected function setParameterMap($parameterMap)
    {
        $this->parameterMap = $parameterMap;
    }

    /**
     * Returns an part instance
     *
     * @return Part
     */
    public function getHttpPartInstance()
    {
        return $this->part->getInstance();
    }

    /**
     * Returns the response instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Response The response instance
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns an array with all request parameters.
     *
     * @return array The array with the request parameters
     */
    public function getParameterMap()
    {
        return $this->parameterMap;
    }

    /**
     * Returns header info by given key
     *
     * @param string $key The header key to get
     *
     * @return string|null
     */
    public function getHeader($key)
    {
        foreach ($this->headers as $headerName => $value) {
            if (strcasecmp($key, $headerName) === 0) {
                return $this->headers[$headerName];
            }
        }
    }

    /**
     * Returns accepted encodings data
     *
     * @return array
     */
    public function getAcceptedEncodings()
    {
        return $this->acceptedEncodings;
    }

    /**
     * Returns query string of the actual request.
     *
     * @return string|null The query string of the actual request
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
     *
     * @return string
     */
    protected function setServerName($serverName)
    {
        return $this->setServerVar('SERVER_NAME', $this->serverName = $serverName);
    }

    /**
     * Returns the server's IP v4 address
     *
     * @return string
     */
    public function getServerAddress()
    {
        return $this->serverAddress;
    }

    /**
     * Sets server's IP v4 address
     *
     * @param string $serverAddress The server's IP address
     *
     * @return string
     */
    protected function setServerAddress($serverAddress)
    {
        return $this->setServerVar('SERVER_ADDR', $this->serverAddress = $serverAddress);
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
     *
     * @return string
     */
    protected function setServerPort($serverPort)
    {
        return $this->setServerVar('SERVER_PORT', $this->serverPort = $serverPort);
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
     *
     * @return string
     */
    public function setPathInfo($pathInfo)
    {
        return $this->setServerVar('PATH_INFO', $this->pathInfo = $pathInfo);
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
     * @param array $headers The headers array to set
     *
     * @return void
     */
    protected function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * Sets the body content
     *
     * @param string $content The content to set
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Return content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->content;
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
     *
     * @return void
     */
    protected function setMethod($method)
    {
        $this->setServerVar('REQUEST_METHOD', $this->method = $method);
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
     * @param string $uri The uri to set
     *
     * @return void
     */
    public function setUri($uri)
    {
        $this->setServerVar('REQUEST_URI', $this->uri = $uri);
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
     * @param string $version The http protocol version
     *
     * @return void
     */
    public function setVersion($version)
    {
        $this->setServerVar('SERVER_PROTOCOL', $this->version = $version);
    }

    /**
     * Returns the injected query parser
     *
     * @return QueryParser
     */
    public function getQueryParser()
    {
        return $this->queryParser;
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
     * @param string $key   The server var key
     * @param string $value The value for given server var key
     *
     * @return void
     */
    public function setServerVar($key, $value)
    {
        $this->server[$key] = $value;
    }

    /**
     * Returns specific server var data
     *
     * @param string $key The key to get
     *
     * @return null|string
     */
    public function getServerVar($key)
    {
        if (array_key_exists($key, $this->server)) {
            return $this->server[$key];
        }
    }

    /**
     * Sets clients ip address
     *
     * @param string $clientIp The client's ip address as string
     *
     * @return void
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;
    }

    /**
     * Returns clients ip address
     *
     * @return mixed
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * Sets clients port
     *
     * @param int|string $clientPort The client's port as string (or int)
     *
     * @return void
     */
    public function setClientPort($clientPort)
    {
        $this->clientPort = $clientPort;
    }

    /**
     * Returns clients port
     *
     * @return int
     */
    public function getClientPort()
    {
        return $this->clientPort;
    }

    /**
     * Sets the webapps name related with the request
     *
     * @param string $webappName The webapp's name
     *
     * @return void
     */
    public function setWebappName($webappName)
    {
        $this->webappName = $webappName;
    }

    /**
     * returns the webapps name related with the request
     *
     * @return string
     */
    public function getWebappName()
    {
        return $this->webappName;
    }

    /**
     * Returns the parameter with the passed name if available or null
     * if the parameter not exists.
     *
     * @param string  $name   The name of the parameter to return
     * @param integer $filter The filter to use
     *
     * @return string|null
     */
    public function getParameter($name, $filter = FILTER_SANITIZE_STRING)
    {
        $parameterMap = $this->getParameterMap();
        if (array_key_exists($name, $parameterMap)) {
            return filter_var($parameterMap[$name], $filter);
        }
    }

    /**
     * Returns a part object by given name
     *
     * @param string $name The name of the form part
     *
     * @return \TechDivision\ServletContainer\Http\HttpPart
     */
    public function getPart($name)
    {
        if (array_key_exists($name, $this->parts)) {
            return $this->parts[$name];
        }
    }

    /**
     * Returns the parts collection as array
     *
     * @return array A collection of HttpPart objects
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * adds a part to the parts collection
     *
     * @param Part   $part A form part object
     * @param string $name A manually defined name
     *
     * @return void
     */
    public function addPart(Part $part, $name = null)
    {
        if (is_null($name)) {
            $name = $part->getName();
        }
        $this->parts[$name] = $part;
    }

    /**
     * Returns true if the request has a cookie header with the passed
     * name, else false.
     *
     * @param string $cookieName Name of the cookie header to be checked
     *
     * @return boolean true if the request has the cookie, else false
     */
    public function hasCookie($cookieName)
    {
        return array_key_exists($cookieName, $this->cookies);
    }

    /**
     * Returns the value of the cookie with the passed name.
     *
     * @param string $cookieName The name of the cookie to return
     *
     * @return mixed The cookie value
     */
    public function getCookie($cookieName)
    {
        if ($this->hasCookie($cookieName)) {
            return $this->cookies[$cookieName];
        }
    }
    
    /**
     * Sets the flag to mark the request dispatched.
     * 
     * @param boolean $dispatched TRUE if the request has already been dispatched, else FALSE
     * 
     * @return void
     */
    public function setDispatched($dispatched = true)
    {
        $this->dispatched = $dispatched;
    }
    
    /**
     * Sets the flag that shows if the request has already been dispatched.
     * 
     * @return boolean TRUE if the request has already been dispatched, else FALSE
     */
    public function isDispatched()
    {
        return $this->dispatched;
    }
}
