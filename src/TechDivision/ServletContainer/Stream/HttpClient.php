<?php

/**
 * TechDivision\ServletContainer\Stream\HttpClient
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
 * @subpackage Stream
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Stream;

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\Stream\Client;
use TechDivision\ServletContainer\Exceptions\ConnectionClosedByPeerException;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Stream
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class HttpClient extends Client implements HttpClientInterface
{

    /**
     * The HttpRequest instance to use as factory.
     * 
     * @var \TechDivision\ServletContainer\Http\HttpRequest
     */
    protected $httpRequest;

    /**
     * Hold the http part instance to use as factory.
     *
     * @var \TechDivision\ServletContainer\Http\HttpPart
     */
    protected $httpPart;

    /**
     * The new line character.
     * 
     * @param string $newLine The new line separator
     *
     * @return void
     */
    public function setNewLine($newLine)
    {
        $this->newLine = $newLine;
    }

    /**
     * Injects the HttpRequest instance to use as factory.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance to use
     *
     * @return void
     */
    public function injectHttpRequest($request)
    {
        $this->httpRequest = $request;
    }

    /**
     * Injects the Part instance.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Part $part The part instance to use
     *
     * @return void
     */
    public function injectHttpPart($part)
    {
        $this->httpPart = $part;
    }

    /**
     * Returns the HttpRequest factory instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request The request factory instance
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * Returns the HttpPart factory instance.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Part The part as factory instance
     */
    public function getHttpPart()
    {
        return $this->httpPart;
    }

    /**
     * Returns the Request instance initialized with request data read from the socket.
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request The initialized Request instance
     */
    public function receive()
    {

        // initialize the buffer
        $buffer = false;
        
        // read a chunk from the socket
        while ($line = $this->read($this->getLineLength())) {
            
            // if receive timeout occured
            if (strlen($line) === 0) {
                break;
            }
        
            // append line to buffer
            $buffer .= $line;
        
            // check if data transmission has finished
            if (false !== strpos($buffer, $this->getNewLine())) {
                break;
            }
        }

        // if the socket has been closed by peer
        if ($buffer === '' || $buffer === false) {
            $this->close();
            throw new ConnectionClosedByPeerException('Connection reset by peer');
        }

        // separate header from body chunk
        list ($rawHeader) = explode($this->getNewLine(), $buffer);
        $body = str_replace($rawHeader . $this->getNewLine(), '', $buffer);

        // initialize the request from the raw headers
        $requestInstance = $this->getHttpRequest();
        $requestInstance->initFromRawHeader($rawHeader);

        // check if body-length not reached content-length already
        if (($contentLength = $requestInstance->getHeader(Header::HEADER_NAME_CONTENT_LENGTH)) && ($contentLength > strlen($body))) {
            // read a chunk from the socket till content length is reached
            while (strlen($body) < (int) $contentLength) {
                // append body
                $body .= $this->read($this->getLineLength());
            }
        }

        // inject part instance
        $requestInstance->injectHttpPart($this->getHttpPart());

        // parse body with request instance
        $requestInstance->parse($body);

        // initialize client IP + port
        $requestInstance->setClientIp($this->getAddress());
        $requestInstance->setClientPort($this->getPort());
        
        // return fully qualified request instance
        return $requestInstance;
    }
}
