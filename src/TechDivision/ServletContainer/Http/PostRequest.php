<?php

/**
 * TechDivision\ServletContainer\Http\PostRequest
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
 * @author      Philipp Dittert <p.dittert@techdivision.com>
 */
class PostRequest extends HttpRequest
{
    /**
     * Constructor
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * setting post-vars as string and as array
     *
     * @return $this
     */
    public function setParameter() {
        $content = $this->getContent();
        $this->_parameter = $content;
        $this->setParameterMap();
        return $this;
    }
}