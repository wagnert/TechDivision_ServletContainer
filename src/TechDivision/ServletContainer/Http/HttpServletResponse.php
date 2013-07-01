<?php

/**
 * TechDivision\ServletContainer\Http\HttpServletResponse
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\ServletResponse;
use TechDivision\ServletContainer\Http\Cookie;

/**
 * A servlet response implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
class HttpServletResponse implements ServletResponse {

    const HEADER_NAME_STATUS = 'status';

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array
     */
    protected $headers = array();

    protected $cookies = array();

    public function __construct()
    {
        // prepare the headers
        $this->setHeaders(
            array(
                self::HEADER_NAME_STATUS => "HTTP/1.1 200 OK",
                "Date"                   => gmdate('D, d M Y H:i:s \G\M\T', time()),
                "Last-Modified"          => gmdate('D, d M Y H:i:s \G\M\T', time()),
                "Expires"                => gmdate('D, d M Y H:i:s \G\M\T', time() - 3600),
                "Server"                 => "Apache/4.3.29 (Unix) PHP/5.4.10",
                "Content-Language"       => "de",
                "Connection"             => "close",
                "Content-Type"           => "text/html",
            )
        );
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $header
     * @param string $value
     */
    public function addHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    /**
     * @return string
     */
    public function getHeadersAsString()
    {
        $headers = "";

        foreach ($this->getHeaders() as $header => $value) {

            if ($header === self::HEADER_NAME_STATUS) {
                $headers .= $value . "\r\n";
            } else {
                $headers .= $header . ': ' . $value . "\r\n";
            }
        }

        foreach ($this->cookies as $cookie) {
            $headers .= "Set-Cookie: $cookie\r\n";
        }

        return $headers;
    }

    /**
     * Removes one single header from the headers array.
     *
     * @param string $header
     * @return void
     */
    public function removeHeader($header)
    {
        unset($this->headers[$header]);
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent($content) {
        $this->content = $content;
    }

    public function addCookie(Cookie $cookie) {
        $this->cookies[] = $cookie;
    }
}