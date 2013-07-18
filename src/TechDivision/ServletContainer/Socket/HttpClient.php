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

use TechDivision\Socket\Client;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */
class HttpClient extends Client
{

    /**
     * New line character.
     * @var string
     */
    protected $newLine = "\r\n\r\n";

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

}