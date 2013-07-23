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
use TechDivision\Socket\Client;
use TechDivision\ServletContainer\Http\Request;
use TechDivision\ServletContainer\Utilities\Http\GetRequestValidator;
use TechDivision\ServletContainer\Utilities\Http\PostRequestValidator;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */
class HttpClient extends Client
{

    /**
     * Receive a Stream from Socket an check it is valid
     * @return mixed
     * @throws InvalidHeaderException Is thrown if the header is complete but not valid
     */
    public function receive()
    {

        // initialize the buffer
        $buffer = '';

        // read a chunk from the socket
        while ($buffer .= $this->read($this->getLineLength())) {

            // create validator if not set
            if (!isset ($validator)) {

                // extract Request-Type from InputStream
                $requestType = $this->getRequestType($buffer);

                // select fitting validator
                switch ($requestType) {
                    case "GET":
                        $request = new GetRequest();
                        break;
                    case "POST":
                        $request = new PostRequest();
                        break;
                    case "HEAD":
                        $request = new HeadRequest();
                        break;
                    default:

                        // Throw InvalidHeaderException if method is unknown
                        throw new InvalidHeaderException("Invalid Request Method");
                        break;
                }
            }

            // check if request complete is valid
            $request->validate($buffer);

            // check if content-length is reached (e.g. on POST Request)
            if ($request->isComplete()) {

                // return a valid request object
                return $request->getRequest();
            }

        }
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


// kann weg
    /**
     * Reads a line (ends with the new line character) from the socket.
     *
     * @return string The data read from the socket
     */
    public function readLine() {

        // initialize the buffer
        $buffer = '';

        // read a chunk from the socket
        while ($buffer .= $this->read($this->getLineLength())) {

            // check if a new line character was found
            if (false !== strpos($buffer, $this->getNewLine())) {
                // if yes, trim and return the data
                // TODO: validate content when post request is comming up
                return $buffer;
            }
        }
    }
// ende kann weg
}