<?php

/**
 * TechDivision\ServletContainer\Http\GetRequest
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
 *
 */

class GetRequest extends HttpRequest
{

    /**
     * parse get header content
     *
     * @param string $content
     * @return void
     */
    public function parse($content)
    {
        // queryString is only available on GET Method
        $qs = $this->parseQueryString($this->getUri());
        $this->setQueryString($qs);

        $this->setServerVar('QUERY_STRING', $qs);

        $this->setParameters($qs);
        $paramMap = $this->parseParameterMap($qs);
        $this->setParameterMap($paramMap);
    }

    /**
     * Parsing QueryString out of URI
     *
     * @param string $uri
     * @return mixed
     */
    protected function parseQueryString($uri)
    {
        $url = parse_url($uri);
        // parse path
        if (array_key_exists('query', $url)) {
            return $url['query'];
        }
    }

}