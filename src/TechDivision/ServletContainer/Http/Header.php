<?php

/**
 * TechDivision\ServletContainer\Http\Header
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

/**
 * This is a utility class that defines the HTTP headers available.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Http
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class Header
{

    /**
     * Status header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_STATUS = 'Status';

    /**
     * Date header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_DATE = 'Date';

    /**
     * Connection header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_CONNECTION = 'Connection';

    /**
     * Content-Type header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_CONTENT_TYPE = 'Content-Type';

    /**
     * Content-Length header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_CONTENT_LENGTH = 'Content-Length';

    /**
     * Content-Encoding header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_CONTENT_ENCODING = 'Content-Encoding';

    /**
     * Cache-Control header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_CACHE_CONTROL = 'Cache-Control';

    /**
     * Pragma header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_PRAGMA = 'Pragma';

    /**
     * Status header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_LAST_MODIFIED = 'Last-Modified';

    /**
     * Expires header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_EXPIRES = 'Expires';

    /**
     * If-Modified-Since header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_IF_MODIFIED_SINCE = 'If-Modified-Since';

    /**
     * Location header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_LOCATION = 'Location';

    /**
     * X-Powered-By header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_X_POWERED_BY = 'X-Powered-By';

    /**
     * X-Request-With header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_X_REQUESTED_WITH = 'X-Requested-With';

    /**
     * Cookie header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_COOKIE = 'Cookie';

    /**
     * Set-Cookie header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_SET_COOKIE = 'Set-Cookie';

    /**
     * Host header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_HOST = 'Host';

    /**
     * Accept header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_ACCEPT = 'Accept';

    /**
     * Accept-Language header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_ACCEPT_LANGUAGE = 'Accept-Language';

    /**
     * Accept-Encoding header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_ACCEPT_ENCODING = 'Accept-Encoding';

    /**
     * User-Agent header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_USER_AGENT = 'User-Agent';

    /**
     * Referer header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_REFERER = 'Referer';

    /**
     * Keep-Alive header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_KEEP_ALIVE = 'Keep-Alive';

    /**
     * Server header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_SERVER = 'Server';

    /**
     * WWW-Authenticate header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_WWW_AUTHENTICATE = 'WWW-Authenticate';

    /**
     * Authorization header name.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
     * @var string
     */
    const HEADER_NAME_AUTHORIZATION = 'Authorization';

    /**
     * This is a utility class, so protect it against direct
     * instantiation.
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * This is a utility class, so protect it against cloning.
     *
     * @return void
     */
    private function __clone()
    {
    }
}
