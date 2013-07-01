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
 * Abstract Http servlet implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 */
abstract class HttpServlet extends GenericServlet implements Servlet {

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doGet(ServletRequest $req, ServletResponse $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doPost(ServletRequest $req, ServletResponse $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws ServletException
     * @throws IOException
     * @throws MethodNotImplementedException
     * @return mixed
     */
    public function service(ServletRequest $req, ServletResponse $res) {

        /** @var $req \TechDivision\ServletContainer\Http\HttpServletRequest */
        switch($req->getRequestMethod()) {
            case 'POST':
                $this->doPost($req, $res);
                break;
            case 'GET':
                $this->doGet($req, $res);
                break;
            default:
                throw new MethodNotImplementedException(sprintf('%s is not implemented yet.', $req->getRequestMethod()));
        }

    }
}