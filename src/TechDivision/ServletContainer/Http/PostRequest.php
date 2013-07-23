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
     * @var string
     */
    protected $content;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * extended Header Validation for POST Request
     * @param string $buffer
     * @return void
     */
    public function validate($buffer)
    {
        // call initial method for basic parsing
        $this->initFromRawHeader($buffer);

        // searching for POST-Content
        $content = $this->parseContent($buffer);
        $this->setContent($content);

        $this->setParameters($content);
        $paramMap = $this->parseParameterMap($content);
        $this->setParameterMap($paramMap);
    }

    /**
     * Parsing Raw Header for Post-Content
     *
     * @param string $buffer Raw Header
     * @return string Post-Content
     */
    public function parseContent($buffer)
    {
        // search for first upcoming Separator, strip whitespaces and return until end
        return ltrim(strstr($buffer, $this->headerContentSeparator));
    }

}