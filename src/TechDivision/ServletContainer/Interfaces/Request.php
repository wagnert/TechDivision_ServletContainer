<?php

/**
 * TechDivision\ServletContainer\Interfaces\Request
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
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * Interface for the servlet request.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Interfaces
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
interface Request
{

    /**
     * POST request method string.
     *
     * @var string
     */
    const POST = 'POST';

    /**
     * GET request method string.
     *
     * @var string
     */
    const GET = 'GET';

    /**
     * HEAD request method string.
     *
     * @var string
     */
    const HEAD = 'HEAD';

    /**
     * PUT request method string.
     *
     * @var string
     */
    const PUT = 'PUT';

    /**
     * DELETE request method string.
     *
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * OPTIONS request method string.
     *
     * @var string
     */
    const OPTIONS = 'OPTIONS';

    /**
     * TRACE request method string.
     *
     * @var string
     */
    const TRACE = 'TRACE';

    /**
     * CONNECT request method string.
     *
     * @var string
     */
    const CONNECT = 'CONNECT';

    /**
     * Parse request content
     *
     * @param string $content The raw request header
     *
     * @return void
     */
    public function parse($content);

    /**
     * validate actual InputStream
     *
     * @param string $buffer InputStream
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request
     */
    public function initFromRawHeader($buffer);
    
    /**
     * The server name passed with the request header.
     * 
     * @return string The server name of the actual request
     */
    public function getServerName();
    
    /**
     * Returns the absolute path info started from the context path.
     * 
     *  @return string the absolute path info
     *  @see \TechDivision\ServletContainer\Http\ServletRequest::getPathInfo()
     */
    public function getPathInfo();

    /**
     * Returns an part instance
     *
     * @return Part
     */
    public function getHttpPartInstance();

    /**
     * Returns an array with all request parameters.
     *
     * @return array The array with the request parameters
     */
    public function getParameterMap();

    /**
     * Returns header info by given key
     *
     * @param string $key The header key to get
     *
     * @return string|null
     */
    public function getHeader($key);

    /**
     * Returns accepted encodings data
     *
     * @return array
     */
    public function getAcceptedEncodings();

    /**
     * Returns query string of the actual request.
     *
     * @return string|null The query string of the actual request
     */
    public function getQueryString();

    /**
     * Returns the server's IP v4 address
     *
     * @return string
     */
    public function getServerAddress();

    /**
     * Returns server port
     *
     * @return string
     */
    public function getServerPort();

    /**
     * Returns headers data
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Return content
     *
     * @return string $content
     */
    public function getContent();

    /**
     * Returns request method
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns request uri
     *
     * @return string
     */
    public function getUri();

    /**
     * Returns protocol version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Returns server data
     *
     * @return array
     */
    public function getServerVars();

    /**
     * Returns specific server var data
     *
     * @param string $key The key to get
     *
     * @return null|string
     */
    public function getServerVar($key);

    /**
     * Returns clients ip address
     *
     * @return mixed
     */
    public function getClientIp();

    /**
     * Returns clients port
     *
     * @return int
     */
    public function getClientPort();

    /**
     * Returns the parameter with the passed name if available or null
     * if the parameter not exists.
     *
     * @param string  $name   The name of the parameter to return
     * @param integer $filter The filter to use
     *
     * @return string|null
     */
    public function getParameter($name, $filter = FILTER_SANITIZE_STRING);

    /**
     * Returns a part object by given name
     *
     * @param string $name The name of the form part
     *
     * @return \TechDivision\ServletContainer\Http\HttpPart
     */
    public function getPart($name);

    /**
     * Returns the parts collection as array
     *
     * @return array A collection of HttpPart objects
     */
    public function getParts();

    /**
     * Returns true if the request has a cookie header with the passed
     * name, else false.
     *
     * @param string $cookieName Name of the cookie header to be checked
     *
     * @return boolean true if the request has the cookie, else false
     */
    public function hasCookie($cookieName);

    /**
     * Returns the value of the cookie with the passed name.
     *
     * @param string $cookieName The name of the cookie to return
     *
     * @return mixed The cookie value
     */
    public function getCookie($cookieName);

    /**
     * Set specific server var data.
     *
     * @param string $key   The server var key
     * @param string $value The value for given server var key
     *
     * @return void
     */
    public function setServerVar($key, $value);
}
