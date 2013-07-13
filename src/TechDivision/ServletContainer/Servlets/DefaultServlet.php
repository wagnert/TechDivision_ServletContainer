<?php

/**
 * TechDivision\ServletContainer\DefaultServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Interfaces\ServletResponse;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Servlets\HttpServlet;

/**
 * Default Http servlet implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 */
class DefaultServlet extends HttpServlet {

    /**
     * @param Request $req
     * @param Response $res
     * @throws ServletException
     * @throws IOException
     * @throws MethodNotImplementedException
     * @return mixed
     */
    public function service(ServletRequest $req, ServletResponse $res) {

        // load the information about the requested path
        $pathInfo = $req->getPathInfo();

        // if ending slash is missing, redirect to same folder but with slash appended
        if (substr($pathInfo, -1) !== '/') {
            $res->addHeader("location", $pathInfo . '/');
            $res->addHeader("status", 'HTTP/1.1 301 OK');
            $res->setContent(PHP_EOL);
            $this->doGet($req, $res);

        } else {
            return parent::service($req, $res);
        }
    }
}