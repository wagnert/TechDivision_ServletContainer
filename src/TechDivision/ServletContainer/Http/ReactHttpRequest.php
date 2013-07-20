<?php

/**
 * TechDivision\ServletContainer\Http\ReactHttpRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Session\PersistentSessionManager;

/**
 * A web request implementation that uses ReactPHP request for initialization.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class ReactHttpRequest extends HttpRequest
{

    /**
     * Constructor to initialize the request with the data
     * from the passed React request.
     *
     * @param $reactRequest The React request instance
     * @return void
     */
    public function __construct($reactRequest)
    {

        // initialize HttpRequest from React request
        $this->method = $reactRequest->getMethod();
        $this->pathInfo = $reactRequest->getPath();
        $this->uri = $reactRequest->getPath();
        $this->queryString = implode('&', $reactRequest->getQuery());
        $this->version = $reactRequest->getHttpVersion();
        $this->headers = $reactRequest->getHeaders();

        // init session manager
        $this->sessionManager = new PersistentSessionManager();
    }
}