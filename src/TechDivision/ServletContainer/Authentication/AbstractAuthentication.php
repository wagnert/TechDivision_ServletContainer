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


    protected $servlet;

    protected $request;

    Protected $response;

    public function init(Servlet $servlet, Request $req, Response $res)
    {
        $this->setServlet($servlet);
        $this->setResponse($res);
        $this->setRequest($req);

    }

    protected function setServlet($servlet)
    {
        $this->servlet = $servlet;
        return $this;
    }

    protected function getServlet()
    {
        return $this->servlet;
    }

    protected function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    protected function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    protected function getResponse()
    {
        return $this->response;
    }
}
