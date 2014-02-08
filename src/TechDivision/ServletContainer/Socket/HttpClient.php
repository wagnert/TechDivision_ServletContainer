<?php
/**
 * TechDivision\ServletContainer\Socket\HttpClient
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Socket
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Socket;

use TechDivision\ServletContainer\Interfaces\HttpClientInterface;
use TechDivision\ServletContainer\Http\HttpRequest;
use TechDivision\Socket\Client;
use TechDivision\ServletContainer\Exceptions\ConnectionClosedByPeerException;

/**
 * The http client implementation that handles the request like a webserver
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Socket
 * @author     Johann Zelger <jz@techdivision.com>
 * @author     Philipp Dittert <p.dittert@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
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
     * Injects the Request instance to use as factory.
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
     * Injects the Part instance to use as factory.
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
        $buffer = null;
        
        // read a chunk from the socket
        while ($buffer .= $this->read($this->getLineLength())) {
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
        if (($contentLength = $requestInstance->getHeader('Content-Length')) && ($contentLength > strlen($body))) {
            // read a chunk from the socket till content length is reached
            while ($line = $this->read($this->getLineLength())) {
                // append body
                $body .= $line;
                
                // if length is reached break here
                if (strlen($body) == (int) $contentLength) {
                    break;
                }
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
