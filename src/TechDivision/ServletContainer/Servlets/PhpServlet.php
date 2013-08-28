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
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;
use TechDivision\ServletContainer\Service\Locator\StaticResourceLocator;
use TechDivision\ServletContainer\Exceptions\PermissionDeniedException;

/**
 * Abstract Http servlet implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 * @author      Johann Zelger <jz@techdivision.com>
 */
class PhpServlet extends StaticResourceServlet {

    /**
     * Holds the request object
     *
     * @var Request
     */
    protected $request;

    /**
     * Holds the response object
     *
     * @var Response
     */
    protected $response;


    /**
     * Set all headers for php script execution
     *
     * @return void
     */
    public function setHeaders()
    {
        // set default headers for php usage
        $this->getResponse()->addHeader('X-Powered-By', 'PhpServlet');
        $this->getResponse()->addHeader('Expires', '19 Nov 1981 08:52:00 GMT');
        $this->getResponse()->addHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->getResponse()->addHeader('Pragma', 'no-cache');
    }

    /**
     * Initialize globals
     *
     * @return void
     */
    public function initGlobals()
    {
        $_SERVER = $this->getRequest()->getServerVars();
        $_SERVER['SERVER_PORT'] = NULL;

        // check post type and set params to globals
        if ($this->getRequest() instanceof PostRequest) {
            $_POST = $this->getRequest()->getParameterMap();
            // check if there are get params send via uri
            parse_str($this->getRequest()->getQueryString(), $_GET);
        } else {
            $_GET = $this->getRequest()->getParameterMap();
        }

        $_REQUEST = $this->getRequest()->getParameterMap();

        foreach (explode('; ', $this->getRequest()->getHeader('Cookie')) as $cookieLine) {
            list($key, $value) = explode('=', $cookieLine);
            $_COOKIE[$key] = $value;
        }
    }

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param Request $req The servlet request
     * @param Response $res The servlet response
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(Request $req, Response $res) {

        // register request and response objects
        $this->setRequest($req);
        $this->setResponse($res);

        // init globals
        $this->initGlobals();

        // init resource locator
        $locator = new StaticResourceLocator($this);

        // let the locator retrieve the file
        $file = $locator->locate($this->getRequest());

        // do not directly serve php files
        if (strpos($file->getFilename(), '.php') === false) {
            throw new PermissionDeniedException(sprintf(
                '403 - You do not have permission to access %s', $file->getFilename()));
        }

        $this->setHeaders();

        // start output buffering
        ob_start();

        // load the file
        require_once $file->getPathname();

        // store the file's contents in the response
        $this->getResponse()->setContent(ob_get_clean());

    }

    /**
     * @see \TechDivision\ServletContainer\Servlets\PhpServlet::doGet()
     */
    public function doPost(Request $req, Response $res) {
        $this->doGet($req, $res);
    }

    /**
     * Sets request object
     *
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Returns request object
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets response object
     *
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Returns response object
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}