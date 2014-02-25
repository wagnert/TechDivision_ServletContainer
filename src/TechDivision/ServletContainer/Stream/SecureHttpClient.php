<?php
/**
 * TechDivision\ServletContainer\Stream\SecureHttpClient
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Stream
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Stream;

use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\Stream\Client;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Stream
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class SecureHttpClient extends HttpClient
{

    /**
     * Overwrites the readFrom() method of the Stream classes because the 
     * {@link http://de3.php.net/stream_socket_recvfrom stream_socket_recvfrom()} doesn't
     * support SSL handling.
     *
     * @param int $length The maximum number of bytes read is specified by the length parameter
     * @param int $flags  The value of flags can be any combination of the following flags, joined with the binary OR (|) operator
     *
     * @return string The string read from the socket
     */
    public function readFrom($length, $flags = 0)
    {
        $this->getPeerName($this->address, $this->port);
        return $this->read($length);
    }
}
