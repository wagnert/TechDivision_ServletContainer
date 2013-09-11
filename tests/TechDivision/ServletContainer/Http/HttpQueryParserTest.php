<?php 

/**
 * TechDivision\ApplicationServer\Http\HttpQueryParserTest
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
 * @author      Johann Zelger <jz@techdivision.com>
 */
class HttpQueryParserTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var HttpQueryParser
     */
    public $queryParser;

	/**
	 * Initializes request object to test.
	 *
	 * @return void
	 */
	public function setUp()
	{
        $this->queryParser = new HttpQueryParser();
	}

	/**
	 * Tests parse string functionality with empty queryString
	 */
	public function testParseStrFunctionWithEmptyString()
	{
	    $this->queryParser->parseStr('');
	    $this->assertSame(array(), $this->queryParser->getResult());
	}
	
	/**
	 * Tests parse string functionality with filled queryString
	 */
	public function testParseStrFunctionWithNonEmptyQueryString()
	{
	    $this->queryParser->parseStr('key-1=value-1&key-2=value-2&key-3=value-3');
	    
	    $expectedResult = array(
	    	'key-1' => 'value-1',
	        'key-2' => 'value-2',
	        'key-3' => 'value-3'
	    );
	    
	    $this->assertSame($expectedResult, $this->queryParser->getResult());
	}
	
	/**
	 * Tests parse string functionality with filled queryString
	 * and leading question mark
	 */
	public function testParseStrFunctionWithNonEmptyQueryStringAndLeadingQuestionMark()
	{
	    $this->queryParser->parseStr('?key-3=value-1&key-2=value-2&key-1=value-3');
	     
	    $expectedResult = array(
	        'key-3' => 'value-1',
	        'key-2' => 'value-2',
	        'key-1' => 'value-3'
	    );
	    $this->assertSame($expectedResult, $this->queryParser->getResult());
	}
	
	/**
	 * Tests parse string functionality with queryString containing only question mark
	 */
	public function testParseStrFunctionWithQueryStringContainingOnlyQuestionMark()
	{
	    $this->queryParser->parseStr('?');
	
	    $this->assertSame(array(), $this->queryParser->getResult());
	}
}