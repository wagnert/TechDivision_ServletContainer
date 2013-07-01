<?php

/**
 * TechDivision\ServletContainer\Http\Request
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

/**
 * A web request implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Philipp Dittert <pd@techdivision.com>
 */
class GetRequest extends Request {

    /**
     * splice uri into PathInfo and QueryString
     * @return $this
     */
    public function ParseUriInformation() {

        $uriMod = explode("?", $this->getUri() );

        if (array_key_exists(0, $uriMod)) {
            $this->setPathInfo($uriMod[0]);
        }

        if (array_key_exists(1, $uriMod)) {
            $this->setQueryString($uriMod[1]);
        }

        return $this;
    }

    public function setParameter() {
        $this->_parameter = $this->getQueryString();
        $this->setParameterMap();
        return $this;
    }

    /**
     * @deprec is not used anymore
     * @return string
     */
    public function getRequestUrl() {
        return $this->getPathInfo();
    }

    public function getRequestUri() {
        return $this->getUri();
    }

    public function getRequestMethod() {
        return $this->getMethod();
    }
}