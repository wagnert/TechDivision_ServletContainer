<?php 

/**
 * TechDivision\ApplicationServer\Socket\HttpClientTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Socket;

use TechDivision\ServletContainer\Http\HttpRequest;

/**
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@appserver.io>
 */
class HttpClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * The secure Http client to be tested.
     * @var HttpClient
     */
    public $client;

	/**
	 * Initializes secure Http client object to test.
	 *
	 * @return void
	 */
	public function setUp()
	{
        $this->client = new HttpClient();
        $this->client->injectHttpRequest(new HttpRequest());
        $this->client->setNewLine("\r\n\r\n");
	}
	
	/**
	 * Test the getter/setter for the new line.
	 * 
	 * @return void
	 */
	public function testSetGetNewLine()
	{
	    $this->client->setNewLine(PHP_EOL);
	    $this->assertEquals(PHP_EOL, $this->client->getNewLine());
	}
	
	/**
	 * Test if the request factory has been passed.
	 * 
	 * @return void
	 */
	public function testGetHttpRequest()
	{
	    $this->assertInstanceOf('TechDivision\ServletContainer\Http\HttpRequest', $this->client->getHttpRequest());
	}

    /**
     * Test the receive method for a POST request with params.
     * 
     * @return void
     */
	public function testReveivePostRequestWithOversizeContent()
	{

	    // load the socket pair
	    list ($client, $server) = $this->getSocketPair();
	    $this->client->setResource($server);
	    
	    // define the oversized content
	    $value = str_repeat('@', 8000);
	    $oversizedContent = "var_01=" . $value;
	    $contentLength = strlen($oversizedContent);
	    
	    // initialize the POST request
	    $requestString = "";
	    $requestString .= "POST /index.php HTTP/1.1\r\n";
	    $requestString .= "Content-Length: $contentLength\r\n";
	    $requestString .= "Host: 127.0.0.1:8590\r\n\r\n";
	    $requestString .= $oversizedContent;
        
        // write to the client socket
        socket_write($client, $requestString);
        
        // receive the request
        $request = $this->client->receive();
        
        // check the request instance and the headers
        $this->assertInstanceOf('TechDivision\ServletContainer\Http\PostRequest', $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/index.php', $request->getUri());
        $this->assertEquals('HTTP/1.1', $request->getVersion());
        $this->assertEquals('127.0.0.1', $request->getServerName());
        $this->assertEquals($contentLength, $request->getHeader('Content-Length'));
        $this->assertEquals('8590', $request->getServerPort());
        
        // check the request parameters
        $this->assertEquals($value, $request->getParameter('var_01'));
	}

    /**
     * Test the receive method for a simple GET request.
     * 
     * @return void
     */
    public function testReceiveSimpleGetRequest()
    {
        
        // load the socket pair
        list ($client, $server) = $this->getSocketPair();
        $this->client->setResource($server);
        
        // initialize the simple GET request
        $requestString = "";
        $requestString .= "GET /index.html HTTP/1.1\r\n";
        $requestString .= "Host: 127.0.0.1:8590\r\n\r\n";
        
        // write to the client socket
        socket_write($client, $requestString);
        
        // receive the request
        $request = $this->client->receive();
        
        // check the request instance and the headers
        $this->assertInstanceOf('TechDivision\ServletContainer\Http\GetRequest', $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/index.html', $request->getUri());
        $this->assertEquals('HTTP/1.1', $request->getVersion());
        $this->assertEquals('127.0.0.1', $request->getServerName());
        $this->assertEquals('8590', $request->getServerPort());
    }

    /**
     * Test the receive method for a GET request with params.
     * 
     * @return void
     */
	public function testReveiveGetRequestWithParams()
	{

	    // load the socket pair
	    list ($client, $server) = $this->getSocketPair();
	    $this->client->setResource($server);
	    
	    // initialize the POST request
	    $requestString = "";
	    $requestString .= "GET /index.html?var_01=value_01&var_02=value_02 HTTP/1.1\r\n";
	    $requestString .= "Host: 127.0.0.1:8590\r\n\r\n";
        
        // write to the client socket
        socket_write($client, $requestString);
        
        // receive the request
        $request = $this->client->receive();
        
        // check the request instance and the headers
        $this->assertInstanceOf('TechDivision\ServletContainer\Http\GetRequest', $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/index.html?var_01=value_01&var_02=value_02', $request->getUri());
        $this->assertEquals('HTTP/1.1', $request->getVersion());
        $this->assertEquals('127.0.0.1', $request->getServerName());
        $this->assertEquals('8590', $request->getServerPort());
        
        // check the request parameters
        $this->assertEquals('value_01', $request->getParameter('var_01'));
        $this->assertEquals('value_02', $request->getParameter('var_02'));
	}

    /**
     * Test the receive method for a POST request with params.
     * 
     * @return void
     */
	public function testReveivePostRequestWithParams()
	{

	    // load the socket pair
	    list ($client, $server) = $this->getSocketPair();
	    $this->client->setResource($server);
	    
	    // initialize the POST request
	    $requestString = "";
	    $requestString .= "POST /index.php HTTP/1.1\r\n";
	    $requestString .= "Host: 127.0.0.1:8590\r\n\r\n";
	    $requestString .= "var_01=value_01&var_02=value_02";
        
        // write to the client socket
        socket_write($client, $requestString);
        
        // receive the request
        $request = $this->client->receive();
        
        // check the request instance and the headers
        $this->assertInstanceOf('TechDivision\ServletContainer\Http\PostRequest', $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/index.php', $request->getUri());
        $this->assertEquals('HTTP/1.1', $request->getVersion());
        $this->assertEquals('127.0.0.1', $request->getServerName());
        $this->assertEquals('8590', $request->getServerPort());
        
        // check the request parameters
        $this->assertEquals('value_01', $request->getParameter('var_01'));
        $this->assertEquals('value_02', $request->getParameter('var_02'));
	}
    
    /**
     * Returns a new socket pair to simulate a real socket implementation.
     * 
     * @throws \Exception Is thrown if the socket pair can't be craeted
     * @return array The socket pair
     */
    public function getSocketPair()
    {
        
        // initialize the array for the socket pair
        $sockets = array();
        
        // on Windows we need to use AF_INET
        $domain = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? AF_INET : AF_UNIX);
        
        // setup and return a new socket pair
        if (socket_create_pair($domain, SOCK_STREAM, 0, $sockets) === false) {
            throw new \Exception("socket_create_pair failed. Reason: " . socket_strerror(socket_last_error()));
        }
        
        // return the array with the socket pair
        return $sockets;
    }
}