<?php

/**
 * TechDivision\ServletContainer\Stream\SecureHttpClient
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Stream;

use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\Stream\Client;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 *         Philipp Dittert <p.dittert@techdivision.com>
 */
class SecureHttpClient extends HttpClient
{

    /**
     * Overwrites the readFrom() method of the Stream classes because the 
     * {@link http://de3.php.net/stream_socket_recvfrom stream_socket_recvfrom()} doesn't
     * support SSL handling.
     *
     * @param integer $length
     *            The maximum number of bytes read is specified by the length parameter
     * @param integer $flags
     *            The value of flags can be any combination of the following flags, joined with the binary OR (|) operator
     * @return string The string read from the socket
     */
    public function readFrom($length, $flags = 0)
    {
        $this->getPeerName($this->address, $this->port);
        return $this->read($length);
    }
}