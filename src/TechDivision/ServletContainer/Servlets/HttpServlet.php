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

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Servlets\GenericServlet;
use TechDivision\ServletContainer\Exceptions\MethodNotImplementedException;

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
abstract class HttpServlet extends GenericServlet {

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doConnect(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doDelete(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doGet(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doHead(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doOptions(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doPost(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doPut(Request $req, Response $res) {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * @param Request $req
     * @param Response $res
     * @throws MethodNotImplementedException
     * @return void
     */
    public function doTrace(Request $req, Response $res) {
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
    public function service(Request $req, Response $res) {

        // pre-initialize response
        $res->addHeader('Server', $req->getServerVar('SERVER_SOFTWARE'));

        // check the request method to invoke the appropriate method
        switch($req->getMethod()) {
            case 'CONNECT':
                $this->doConnect($req, $res);
                break;
            case 'DELETE':
                $this->doDelete($req, $res);
                break;
            case 'GET':
                $this->doGet($req, $res);
                break;
            case 'HEAD':
                $this->doHead($req, $res);
                break;
            case 'OPTIONS':
                $this->doOptions($req, $res);
                break;
            case 'POST':
                $this->doPost($req, $res);
                break;
            case 'PUT':
                $this->doPut($req, $res);
                break;
            case 'TRACE':
                $this->doTrace($req, $res);
                break;
            default:
                throw new MethodNotImplementedException(sprintf('%s is not implemented yet.', $req->getMethod()));
        }

    }
}