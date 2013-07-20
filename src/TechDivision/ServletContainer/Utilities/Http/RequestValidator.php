<?php

/**
 * TechDivision\ServletContainer\Utilities\\Http\RequestValidator
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Utilities\Http;

use TechDivision\ServletContainer\Interfaces\Validator;

/**
 * A servlet response implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */


class RequestValidator implements Validator
{

    /**
     * validates the header
     *
     * @param string $buffer Inputstream from socket
     * @return mixed
     */
    public function isHeaderCompleteAndValid($buffer) {

    }

    /**
     * checks if the Request is received completely
     *
     * @return boolean
     */
    public function isComplete() {

    }

    /**
     * checks if the Request is received completely
     *
     * @return \TechDivision\ServletContainer\Http\HttpRequest HttpRequest
     */
    public function getRequest() {

    }

}