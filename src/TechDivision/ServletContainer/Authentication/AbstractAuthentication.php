<?php

/**
 * TechDivision\ServletContainer\ServletManager
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Authentication;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Servlet;


/**
 * Abstract class for authentication adapters.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Philipp Dittert <pd@techdivision.com>
 */

abstract class AbstractAuthentication {

    /**
     * Basic HTTP authentication method
     */
    const AUTHENTICATION_METHOD_BASIC = 'Basic';

    /**
     * Digest HTTP authentication method
     */
    const AUTHENTICATION_METHOD_DIGEST = 'Digest';

    /**
     * Holds Servlet Object
     *
     * @var Servlet
     */
    protected $servlet;

    /**
     * Holds Request object
     *
     * @var Request
     */
    protected $request;

    /**
     * Holds Response
     *
     * @var Response
     */
    Protected $response;

    /**
     * alternative manuell called constructor
     *
     * @param Servlet $servlet
     * @param Request $req
     * @param Response $res
     * @return void
     */
    public function init(Servlet $servlet, Request $req, Response $res)
    {
        $this->setServlet($servlet);
        $this->setResponse($res);
        $this->setRequest($req);

    }

    /**
     * Set's Servlet object
     *
     * @param $servlet
     * @return $this
     */
    protected function setServlet($servlet)
    {
        $this->servlet = $servlet;
        return $this;
    }

    /**
     * Returns Servlet object
     *
     * @return Servlet
     */
    protected function getServlet()
    {
        return $this->servlet;
    }

    /**
     * Sets Request object
     *
     * @param $request
     * @return $this
     */
    protected function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Returns Request object
     *
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * set Response object
     *
     * @param $response
     * @return $this
     */
    protected function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Returns Response object
     *
     * @return Response
     */
    protected function getResponse()
    {
        return $this->response;
    }
}
