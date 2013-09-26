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
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class HttpQueryParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
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

    /**
     * Tests parse string functionality with filled queryString
     * and leading question mark url encoded
     */
    public function testParseStrFunctionWithNonEmptyEncodedQueryStringAndLeadingQuestionMark()
    {
        $queryString = urlencode('?@key-3=@value-1&key-2=value-2&key-1=value-3');

        $this->queryParser->parseStr($queryString);

        $expectedResult = array(
            '@key-3' => '@value-1',
            'key-2' => 'value-2',
            'key-1' => 'value-3'
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    /**
     * Tests parse string functionality with filled queryString
     * and leading question mark url encoded twice
     */
    public function testParseStrFunctionWithNonEmptyDoubleEncodedQueryStringAndLeadingQuestionMark()
    {
        $queryString = urlencode(urlencode('?@key-3=@value-1&key-2=value-2&key-1=value-3'));

        $this->queryParser->parseStr($queryString);

        $expectedResult = array(
            '@key-3' => '@value-1',
            'key-2' => 'value-2',
            'key-1' => 'value-3'
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    public function testParseKeyValueFunctionWithArrayStructuredKeyWithNumericIndex()
    {
        $keys[] = 'key[][2][3][4][5]';
        $keys[] = 'key[11][2][3][4][5]';
        $keys[] = 'key[][2][3][4][5]';
        $value = 'testValue';

        foreach ($keys as $key) {
            $this->queryParser->parseKeyValue($key, $value);
        }

        $expectedResult = array(
            'key' => array(
                0 => array(
                    2 => array(
                        3 => array(
                            4 => array(
                                5 => 'testValue'
                            )
                        )
                    )
                ),
                11 => array(
                    2 => array(
                        3 => array(
                            4 => array(
                                5 => 'testValue'
                            )
                        )
                    )
                ),
                12 => array(
                    2 => array(
                        3 => array(
                            4 => array(
                                5 => 'testValue'
                            )
                        )
                    )
                )
            )
        );
        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    public function testParseKeyValueFunctionWithArrayStructuredKeyWithDynamicIndex()
    {
        $key = 'key[level-1][level-2][level-3]';
        $value = 'testValue';

        $this->queryParser->parseKeyValue($key, $value);

        $expectedResult = array(
            'key' => array(
                'level-1' => array(
                    'level-2' => array(
                        'level-3' => 'testValue'
                    )
                )
            )
        );

        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }

    public function testClearNonEmptyQueryParserResult()
    {
        $this->queryParser->parseStr('key1[111]=value');
        $this->queryParser->parseStr('key2[aaa]=value');

        $this->queryParser->clear();
        $this->assertSame(array(), $this->queryParser->getResult());
    }

    public function testParseKeyValueFunctionWithSameArrayStructuredKeyWithDifferentValues()
    {
        $keys[] = 'test';
        $keys[] = 'test';
        $keys[] = 'key[level-1][level-2][level-3]';
        $keys[] = 'key[level-1][level-22][level-33]';
        $keys[] = 'key[level-1][level-22][level-34]';
        $keys[] = 'key[level-1][level-22]';
        $keys[] = 'key[level-1][level-23]';
        $keys[] = 'key[level-1][level-23]';
        $value = 'testValue';

        foreach ($keys as $key) {
            $this->queryParser->parseKeyValue($key, $value);
        }

        $expectedResult = array(
            'test' => 'testValue',
            'key' => array(
                'level-1' => array(
                    'level-2' => array(
                        'level-3' => 'testValue'
                    ),
                    'level-22' => array(
                        'level-33' => 'testValue',
                        'level-34' => 'testValue',
                        0 => 'testValue',
                    ),
                    'level-23' => 'testValue'
                )
            )
        );

        $this->assertSame($expectedResult, $this->queryParser->getResult());
    }
}