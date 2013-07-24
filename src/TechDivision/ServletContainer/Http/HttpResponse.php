<?php

/**
 * TechDivision\ServletContainer\Http\HttpResponse
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Http\Cookie;

/**
 * A servlet response implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */
class HttpResponse implements Response {

    /**
     * @var status
     */
    const HEADER_NAME_STATUS = 'status';

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @var array
     */
    protected $cookies = array();

    /**
     * @var array
     */
    protected $acceptedEncodings = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        // prepare the headers
        $this->setHeaders(
            array(
                self::HEADER_NAME_STATUS => "HTTP/1.1 200 OK",
                "Date"                   => gmdate('D, d M Y H:i:s \G\M\T', time()),
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
     * Returns header info by given key
     *
     * @param $key
     */
    public function getHeader($key)
    {
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        }
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
        // check if encoding is available
        foreach ($this->getAcceptedEncodings() as $acceptedEncoding) {
            // check if gzip is possible
            if ($acceptedEncoding == 'gzip') {
                // set correct header encoding information
                $this->addHeader('Content-Encoding', 'gzip');
                // return content encoded by gzip
                return gzencode($this->content);
            // check if deflate is possible
            } elseif ($acceptedEncoding == 'deflate') {
                // set correct header encoding information
                $this->addHeader('Content-Encoding', 'deflate');
                // return content deflate
                return gzdeflate($this->content);
            }
        }
        // return content as default
        return $this->content;
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * @param Cookie $cookie
     * @return void
     */
    public function addCookie(Cookie $cookie) {
        $this->cookies[] = $cookie;
    }

    /**
     * Sets accepted encodings data
     *
     * @param $acceptedEncodings
     */
    public function setAcceptedEncodings($acceptedEncodings)
    {
        $this->acceptedEncodings = $acceptedEncodings;
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

}