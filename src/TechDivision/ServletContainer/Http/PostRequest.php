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
 * @author      Johann Zelger <jz@techdivision.com>
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

        $params = array();
        $queryParser = new QueryParser();

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $this->getHeader('Content-Type'), $matches);

        // content type is probably regular form-encoded
        if (!count($matches)) {
            // we expect regular query string containing data
            $queryParser->parseStr(urldecode($content));

        } else {
            // get boundary
            $boundary = $matches[1];

            // split content by boundary and get rid of last -- element
            $blocks = preg_split("/-+$boundary/", $content);
            array_pop($blocks);

            // loop data blocks
            foreach ($blocks as $id => $block)
            {
                if (empty($block))
                    continue;

                // parse uploaded files
                if (strpos($block, 'application/octet-stream') !== FALSE)
                {
                    // match "name", then everything after "stream" (optional) except for pretending newlines
                    preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                    $params['files'][$matches[1]] = $matches[2];
                }

                // parse all other fields
                else
                {
                    // match "name" and optional value in between newline sequences
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                    $queryParser->parseKeyValue($matches[1], $matches[2]);
                }
            }
        }
        $this->setParameterMap($queryParser->getResult());
    }

}