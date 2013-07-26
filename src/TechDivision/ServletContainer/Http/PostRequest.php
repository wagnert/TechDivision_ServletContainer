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
     * Parse post request content
     *
     * @param string $content
     * @return void
     */
    public function parse($content)
    {
        $this->setContent($content);

        $this->setParameters($content);
        $paramMap = $this->parseParameterMap($content);
        $this->setParameterMap($paramMap);
    }

}