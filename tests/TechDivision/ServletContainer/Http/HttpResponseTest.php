<?php 

/**
 * TechDivision\ApplicationServer\Http\HttpResponseTest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

/**
 * @package     TechDivision\ApplicationServer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
class HttpResponseTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var HttpResponse
     */
    public $response;

	/**
	 * Initializes response object to test.
	 *
	 * @return void
	 */
	public function setUp() {
        $this->response = new HttpResponse();
	}

    /**
     * Test default header settings after response object was instantiated.
     */
    public function testDefaultResponseObjectHeadersConstructor() {
        $this->assertSame('HTTP/1.1 200 OK', $this->response->getHeader(Header::HEADER_NAME_STATUS));
        $this->assertNotEmpty($this->response->getHeader(Header::HEADER_NAME_DATE));
        $this->assertSame('text/html', $this->response->getHeader(Header::HEADER_NAME_CONTENT_TYPE));
    }

    /**
     * Test add header functionality on response object.
     */
    public function testAddHeaderToResponseObject() {
        $contentLength = rand(0,100000);
        $this->response->addHeader('X-Powered-By', 'PhpUnit');
        $this->response->addHeader('Content-Length', $contentLength);

        $this->assertSame('PhpUnit', $this->response->getHeader('X-Powered-By'));
        $this->assertSame($contentLength, $this->response->getHeader('Content-Length'));
    }

    /**
     * Test if getHeaders returns right result.
     */
    public function testGetAllHeadersAvailableAsArray() {
        $contentLength = rand(0,100000);
        $this->response->addHeader('X-Powered-By', 'PhpUnit');
        $this->response->addHeader('Content-Length', $contentLength);
        $headers = $this->response->getHeaders();
        $headersXPoweredByValue = array_key_exists('X-Powered-By', $headers) ? $headers['X-Powered-By'] : NULL;
        $headersContentLengthValue = array_key_exists('Content-Length', $headers) ? $headers['Content-Length'] : NULL;

        $this->assertSame('PhpUnit', $headersXPoweredByValue);
        $this->assertSame($contentLength, $headersContentLengthValue);
    }

    /**
     * Test if getCode returns the right code by giving various status lines.
     */
    public function testGetCodeFromCorrectStatusHeader() {
        $this->response->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 200 OK');
        $this->assertSame('200', $this->response->getCode());
        $this->response->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 404 Not Found');
        $this->assertSame('404', $this->response->getCode());
    }

}