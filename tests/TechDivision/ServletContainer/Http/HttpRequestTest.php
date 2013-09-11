<?php 

/**
 * TechDivision\ApplicationServer\Http\HttpRequestTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

/**
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class HttpRequestTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var HttpRequest
     */
    public $request;

	/**
	 * Initializes request object to test.
	 *
	 * @return void
	 */
	public function setUp()
	{
        $this->request = new HttpRequest();
	}

    /**
     * Test default header settings after response object was instantiated.
     */
    public function testInitFromRawHeader()
    {
        
        // initialize the raw header
        $rawHeader = "";
        $rawHeader .= "GET /index.html HTTP/1.1\r\n";
        $rawHeader .= "Host: 127.0.0.1:8590\r\n\r\n";
        
        // initialize the GET request instance
        $requestInstance = $this->request->initFromRawHeader($rawHeader);
        
        // check the request instance and the headers
        $this->assertInstanceOf('TechDivision\ServletContainer\Http\GetRequest', $requestInstance);
        $this->assertEquals('GET', $requestInstance->getMethod());
        $this->assertEquals('/index.html', $requestInstance->getUri());
        $this->assertEquals('HTTP/1.1', $requestInstance->getVersion());
        $this->assertEquals('127.0.0.1', $requestInstance->getServerName());
        $this->assertEquals('8590', $requestInstance->getServerPort());
    }
    
}