<?php

/**
 * TechDivision\ServletContainer\Socket\HttpClient
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Socket;

use TechDivision\ServletContainer\Exceptions\InvalidHeaderException;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\Socket\Client;
use TechDivision\ServletContainer\Http\GetRequest;
use TechDivision\ServletContainer\Http\PostRequest;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 *              Philipp Dittert <p.dittert@techdivision.com>
 */
class HttpClient extends Client
{

    /**
     * @param $newLine
     */
    public function setNewLine($newLine) {
        $this->newLine = $newLine;
    }

    /**
     * Receive a Stream from Socket an check it is valid
     *
     * @return mixed
     * @throws InvalidHeaderException Is thrown if the header is complete but not valid
     */
    public function receive()
    {
        // initialize the buffer
        $buffer = '';

        // get clients ip and port
        $this->getPeerName($clientIp, $clientPort);

        // read a chunk from the socket
        while ($buffer .= $this->read($this->getLineLength())) {
            // check if header finished
            if (false !== strpos($buffer, $this->getNewLine())) {
                break;
            }
        }

        // separate header from body chunk
        list ($rawHeader, $body) = explode($this->getNewLine(), $buffer);

        // get http request (factory)
        $requestFactory = new HttpRequest();

        // get method type instance inited by raw headers
        $requestInstance = $requestFactory->initFromRawHeader($rawHeader);

        // check if body-length not reached content-length already
        if (($contentLength = $requestInstance->getHeader('Content-Length'))
            && ($contentLength > strlen($body)))
        {
            // read a chunk from the socket till content length is reached
            while ($line = $this->read($this->getLineLength())) {
                // append body
                $body .= $line;
                // if length is reached break here
                if (strlen($body) == (int)$contentLength) {
                    break;
                }
            }
        }

        // parse body with request instance
        $requestInstance->parse($body);

        // set clients info to request
        $requestInstance->setClientIp($clientIp);
        $requestInstance->setClientPort($clientPort);

        // return fully qualified request instance
        return $requestInstance;
    }

    /**
     * extract request method from inputstream
     * @param string $buffer inputstream from socket
     * @return string $method
     */
    protected function getRequestType($buffer)
    {
        // extract request method
        list($method ) = explode(" ", trim(strtok($buffer, "\n")));

        // return Request-Type as String (e.g. POST, GET )
        return $method;
    }
}