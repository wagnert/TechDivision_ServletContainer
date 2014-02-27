<?php

/**
 * TechDivision\ServletContainer\Http\HttpServletResponse
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

use TechDivision\ServletContainer\Interfaces\Response;

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
class HttpServletResponse implements ServletResponse
{
    
    /**
     * The Http response instance.
     * 
     * @var \TechDivision\ServletContainer\Interfaces\Response
     */
    protected $response;
    
    /**
     * Injects the passed response instance into this servlet response.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Response $response The response instance used for initialization
     * 
     * @return void
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }
    
    /**
     * Returns the that will be send back to the client.
     * 
     * @return \TechDivision\ServletContainer\Interfaces\Response The response instance
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Return's accepted encodings data
     *
     * @return array
     */
    public function getAcceptedEncodings()
    {
        return $this->getResponse()->getAcceptedEncodings();
    }

    /**
     * Returns the content string
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getResponse()->getContent();
    }

    /**
     * Set's the content
     *
     * @param string $content The content to set
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->getResponse()->setContent($content);
    }

    /**
     * Add's a cookie
     *
     * @param \TechDivision\ServletContainer\Http\Cookie $cookie The cookie instance to add
     *
     * @return void
     */
    public function addCookie(Cookie $cookie)
    {
        $this->getResponse()->addCookie($cookie);
    }

    /**
     * Returns TRUE if the response already has a cookie with the passed
     * name, else FALSE.
     *
     * @param string $cookieName Name of the cookie to be checked
     *
     * @return boolean TRUE if the response already has the cookie, else FALSE
     */
    public function hasCookie($cookieName)
    {
        return $this->getResponse()->hasCookie($cookieName);
    }

    /**
     * Set's the headers
     *
     * @param array $headers The headers array
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->getResponse()->setHeaders($headers);
    }

    /**
     * Return's the headers array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getResponse()->getHeaders();
    }

    /**
     * Add's a header to array
     *
     * @param string     $header The header label e.g. Accept or Content-Length
     * @param string|int $value  The header value
     *
     * @return void
     */
    public function addHeader($header, $value)
    {
        $this->getResponse()->addHeader($header, $value);
    }

    /**
     * Returns header info by given key
     *
     * @param string $key The headers key to return
     *
     * @return string|null
     */
    public function getHeader($key)
    {
        return $this->getResponse()->getHeader($key);
    }

    /**
     * Returns http response code number only
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getResponse()->getCode();
    }

    /**
     * Returns response http version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getResponse()->getVersion();
    }

    /**
     * Return's the headers as string
     *
     * @return string
     */
    public function getHeadersAsString()
    {
        return $this->getResponse()->getHeadersAsString();
    }

    /**
     * Removes one single header from the headers array.
     *
     * @param string $header The header to remove
     *
     * @return void
     */
    public function removeHeader($header)
    {
        $this->getResponse()->removeHeader($header);
    }

    /**
     * Prepares the headers for final processing.
     *
     * @return void
     */
    public function prepareHeaders()
    {
        $this->getResponse()->prepareHeaders();
    }

    /**
     * Prepares the content to be ready for sending to the client.
     *
     * @return void
     */
    public function prepareContent()
    {
        $this->getResponse()->prepareContent();
    }
}
