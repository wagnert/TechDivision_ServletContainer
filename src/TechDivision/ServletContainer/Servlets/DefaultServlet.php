<?php

/**
 * TechDivision\ServletContainer\HttpServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ServletResponse;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Servlets\GenericServlet;
use TechDivision\ServletContainer\Exceptions\MethodNotImplementedException;
use TechDivision\ServletContainer\Exceptions\ServletException;
use TechDivision\ServletContainer\Exceptions\IOException;

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
class DefaultServlet extends HttpServlet implements Servlet {

    /**
     * @param Request $req
     * @param Response $res
     * @throws ServletException
     * @throws IOException
     * @throws MethodNotImplementedException
     * @return mixed
     */
    public function service(ServletRequest $req, ServletResponse $res) {

        $pathInfo = $req->getPathInfo();

        error_log("Found path info: " . pathinfo($pathInfo, PATHINFO_DIRNAME));
        error_log(substr($pathInfo, -1));

        if (substr($pathInfo, -1) !== '/') {

            error_log("Now redirecting to $pathInfo/");

            $res->addHeader("location", $pathInfo . '/');
            $res->addHeader("status", 'HTTP/1.1 301 OK');
            $res->setContent(PHP_EOL);
            $this->doGet($req, $res);

        } else {
            return parent::service($req, $res);
        }
    }
}