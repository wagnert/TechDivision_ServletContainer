<?php

/**
 * TechDivision\ServletContainer\Interfaces\QueryParser
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Interfaces;

/**
 * A query parser interface
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
interface QueryParser
{

    /**
     * Returns parsed result array
     *
     * @return array
     */
    public function getResult();

    /**
     * Parses the given queryStr and returns result array
     *
     * @return array The parsed result as array
     */
    public function parseStr($queryStr);

    /**
     * Parses key value and returns result array
     *
     * @param string $param The param to be parsed
     * @param string $value The value to be set
     */
    public function parseKeyValue($param, $value);

}