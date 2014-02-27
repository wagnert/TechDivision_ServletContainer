<?php

/**
 * TechDivision\ServletContainer\Http\HttpResponse
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
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Http\Cookie;

/**
 * A servlet response implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class HttpResponse implements Response
{

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var array
     */
    protected $headers = array();

    /**
     *
     * @var array
     */
    protected $cookies = array();

    /**
     *
     * @var array
     */
    protected $acceptedEncodings = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initHeaders();
    }

    /**
     * Prepares the headers.
     *
     * @return void
     */
    public function initHeaders()
    {
        // prepare the headers
        $this->setHeaders(
            array(
                Header::HEADER_NAME_STATUS => "HTTP/1.1 200 OK",
                Header::HEADER_NAME_DATE => gmdate('D, d M Y H:i:s \G\M\T', time()),
                Header::HEADER_NAME_CONNECTION => 'keep-alive',
                Header::HEADER_NAME_CONTENT_TYPE => 'text/html',
                Header::HEADER_NAME_CACHE_CONTROL => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
                Header::HEADER_NAME_PRAGMA => 'no-cache'
            )
        );
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
        $this->headers = $headers;
    }

    /**
     * Return's the headers array
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
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
        $this->headers[$header] = $value;
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
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        }
    }

    /**
     * Returns http response code number only
     *
     * @return string
     */
    public function getCode()
    {
        list ($version, $code) = explode(" ", $this->getHeader(Header::HEADER_NAME_STATUS));
        return $code;
    }

    /**
     * Returns response http version
     *
     * @return string
     */
    public function getVersion()
    {
        list ($version, $code) = explode(" ", $this->getHeader(Header::HEADER_NAME_STATUS));
        return $version;
    }

    /**
     * Return's the headers as string
     *
     * @return string
     */
    public function getHeadersAsString()
    {
        $headers = "";

        foreach ($this->getHeaders() as $header => $value) {

            if ($header === Header::HEADER_NAME_STATUS) {
                $headers .= $value . "\r\n";
            } else {
                $headers .= $header . ': ' . $value . "\r\n";
            }
        }

        foreach ($this->cookies as $cookie) {
            $headers .= Header::HEADER_NAME_SET_COOKIE . ": $cookie\r\n";
        }

        return $headers;
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
        unset($this->headers[$header]);
    }

    /**
     * Prepares the content to be ready for sending to the client
     *
     * @return void
     */
    public function prepareContent()
    {
        // check if encoding is available
        foreach ($this->getAcceptedEncodings() as $acceptedEncoding) {
            // check if gzip is possible
            if ($acceptedEncoding == 'gzip') {
                // set correct header encoding information
                $this->addHeader(Header::HEADER_NAME_CONTENT_ENCODING, 'gzip');
                // return content encoded by gzip
                return $this->setContent(
                    gzencode($this->getContent())
                );
                // check if deflate is possible
            } elseif ($acceptedEncoding == 'deflate') {
                // set correct header encoding information
                $this->addHeader(Header::HEADER_NAME_CONTENT_ENCODING, 'deflate');
                // return content deflate
                return $this->setContent(
                    gzdeflate($this->getContent())
                );
            }
        }
    }

    /**
     * Returns the content string
     *
     * @return string
     */
    public function getContent()
    {
        // return content
        return $this->content;
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
        $this->content = $content;
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
        $this->cookies[] = $cookie;
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
        foreach ($this->cookies as $cookie) {
            if ($cookie->getName() === $cookieName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set's accepted encodings data
     *
     * @param array $acceptedEncodings The accepted codings as array
     *
     * @return void
     */
    public function setAcceptedEncodings($acceptedEncodings)
    {
        $this->acceptedEncodings = $acceptedEncodings;
    }

    /**
     * Return's accepted encodings data
     *
     * @return array
     */
    public function getAcceptedEncodings()
    {
        return $this->acceptedEncodings;
    }

    /**
     * Prepares the headers for final processing
     *
     * @return void
     */
    public function prepareHeaders()
    {

        // grap headers and set to response object
        foreach (appserver_get_headers(true) as $i => $h) {

            // set headers defined in sapi headers
            $h = explode(':', $h, 2);
            if (isset($h[1])) {

                // load header key and value
                $key = trim($h[0]);
                $value = trim($h[1]);

                // if not, add the header
                $this->addHeader($key, $value);

                // set status header to 301 if location is given
                if ($key == Header::HEADER_NAME_LOCATION) {
                    $this->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 301');
                }
            }
        }

        // prepare the content length
        $contentLength = strlen($this->getContent());

        // prepare the dynamic headers
        $this->addHeader(Header::HEADER_NAME_CONTENT_LENGTH, $contentLength);
    }
}
