<?php

/**
 * TechDivision\ServletContainer\Http\HttpServletRequest
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
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Request;

/**
 * A servlet request implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class HttpServletRequest implements ServletRequest
{
    
    /**
     * The Http request instance.
     * 
     * @var \TechDivision\ServletContainer\Interfaces\Request
     */
    protected $request;
    
    /**
     * The application context name (application name prefixed with a slash) for the actual request.
     * 
     * @var string
     */
    protected $contextPath;
    
    /**
     * The path to the servlet used to handle this request.
     * 
     * @var string
     */
    protected $servletPath;
    
    /**
     * Injects the passed request instance into this servlet request.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance used for initialization
     * 
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Returns the wrapped request object.
     * 
     * @return \TechDivision\ServletContainer\Interfaces\Request The wrapped request object
     */
    public function getRequest()
    {
        return $this->request;
    }
    
    /**
     * Sets the application context name (application name prefixed with a slash) for the actual request.
     * 
     * @param string $contextPath The application context name
     * 
     * @return void
     */
    public function setContextPath($contextPath)
    {
        $this->contextPath = $contextPath;
    }
    
    /**
     * Returns the application context name (application name prefixed with a slash) for the actual request.
     * 
     * @return string The application context name
     */
    public function getContextPath()
    {
        return $this->contextPath;
    }
    
    /**
     * Sets the path to the servlet used to handle this request.
     * 
     * @param string $servletPath The path to the servlet used to handle this request
     * 
     * @return void
     */
    public function setServletPath($servletPath)
    {
        $this->servletPath = $servlerPath;
    }
    
    /**
     * Returns the path to the servlet used to handle this request.
     * 
     * @return string The relative path to the servlet
     */
    public function getServletPath()
    {
        return $this->servletPath;
    }
    
    /**
     * Sets the absolute path info started from the context path.
     * 
     * @param string $pathInfo The absolute path info started from the context path.
     * 
     * @return void
     */
    public function setPathInfo($pathInfo)
    {
        $this->getRequest()->setPathInfo($pathInfo);
    }
    
    /**
     * Returns the absolute path info started from the context path.
     * 
     * @return string the absolute path info
     * @see \TechDivision\ServletContainer\Http\ServletRequest::getPathInfo()
     */
    public function getPathInfo()
    {
        return $this->getRequest()->getPathInfo();
    }
    
    /**
     * Returns the host name passed with the request header.
     * 
     * @return string The host name of this request
     * @see \TechDivision\ServletContainer\Http\ServletRequest::getServerName()
     */
    public function getServerName()
    {
        return $this->getRequest()->getServerName();
    }

    /**
     * Returns an part instance
     *
     * @return Part
     */
    public function getHttpPartInstance()
    {
        return $this->getRequest()->getHttpPartInstance();
    }

    /**
     * Returns an array with all request parameters.
     *
     * @return array The array with the request parameters
     */
    public function getParameterMap()
    {
        return $this->getRequest()->getParameterMap();
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
        return $this->getRequest()->getHeader($key);
    }

    /**
     * Returns accepted encodings data
     *
     * @return array
     */
    public function getAcceptedEncodings()
    {
        return $this->getRequest()->getAcceptedEncodings();
    }

    /**
     * Returns query string of the actual request.
     *
     * @return string|null The query string of the actual request
     */
    public function getQueryString()
    {
        return $this->getRequest()->getQueryString();
    }

    /**
     * Returns the server's IP v4 address
     *
     * @return string
     */
    public function getServerAddress()
    {
        return $this->getRequest()->getServerAddress();
    }

    /**
     * Returns server port
     *
     * @return string
     */
    public function getServerPort()
    {
        return $this->getRequest()->getServerPort();
    }

    /**
     * Returns headers data
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getRequest()->getHeaders();
    }

    /**
     * Return content
     *
     * @return string $content
     */
    public function getContent()
    {
        return $this->getRequest()->getContent();
    }

    /**
     * Returns request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->getRequest()->getMethod();
    }

    /**
     * Returns request uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->getRequest()->getUri();
    }

    /**
     * Returns protocol version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getRequest()->getVersion();
    }

    /**
     * Returns the session for this request.
     *
     * @param string $sessionName The name of the session to return/create
     *
     * @return \TechDivision\ServletContainer\Session\ServletSession The session instance
     */
    public function getSession($sessionName = ServletSession::SESSION_NAME)
    {
        return $this->getRequest()->getSession($sessionName);
    }

    /**
     * Returns server data
     *
     * @return array
     */
    public function getServerVars()
    {
        return $this->getRequest()->getServerVars();
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
        return $this->getRequest()->getServerVar($key);
    }

    /**
     * Returns clients ip address
     *
     * @return mixed
     */
    public function getClientIp()
    {
        return $this->getRequest()->getClientIp();
    }

    /**
     * Returns clients port
     *
     * @return int
     */
    public function getClientPort()
    {
        return $this->getRequest()->getClientPort();
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
        return $this->getRequest()->getParameter($name, $filter);
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
        return $this->getRequest()->getPart($name);
    }

    /**
     * Returns the parts collection as array
     *
     * @return array A collection of HttpPart objects
     */
    public function getParts()
    {
        return $this->getRequest()->getParts();
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
        return $this->getRequest()->hasCookie($cookieName);
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
        return $this->getRequest()->getCookie($cookieName);
    }

    /**
     * Set specific server var data.
     *
     * @param string $key   The server var key
     * @param string $value The value for given server var key
     *
     * @return void
     */
    public function setServerVar($key, $value)
    {
        $this->getRequest()->setServerVar($key, $value);
    }
}
