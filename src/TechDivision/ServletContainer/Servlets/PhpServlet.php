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
use TechDivision\ServletContainer\Interfaces\QueryParser;

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
     * Initialize global files
     *
     * @return void
     */
    public function initFiles()
    {
        // init query parser
        $this->getQueryParser()->clear();
        // iterate all files
    
        foreach ($this->getRequest()->getParts() as $part) {
            // check if filename is given, write and register it
            if ($part->getFilename()) {
                // generate temp filename
                $tempName = tempnam(ini_get('upload_tmp_dir'), 'neos_upload_');
                // write part
                $part->write($tempName);
                // register uploaded file
                appserver_register_file_upload($tempName);
                // init error state
                $errorState = UPLOAD_ERR_OK;
            } else {
                // set error state
                $errorState = UPLOAD_ERR_NO_FILE;
                // clear tmp file
                $tempName = '';
            }
            // check if file has array info
            if (preg_match('/^([^\[]+)(\[.+)?/', $part->getName(), $matches)) {
    
                // get first part group name and array definition if exists
                $partGroup = $matches[1];
                $partArrayDefinition = '';
                if (isset($matches[2])) {
                    $partArrayDefinition = $matches[2];
                }
    
                $this->getQueryParser()->parseKeyValue(
                    $partGroup.'[name]'.$partArrayDefinition, $part->getFilename()
                );
                $this->getQueryParser()->parseKeyValue(
                    $partGroup.'[type]'.$partArrayDefinition, $part->getContentType()
                );
                $this->getQueryParser()->parseKeyValue(
                    $partGroup.'[tmp_name]'.$partArrayDefinition, $tempName
                );
                $this->getQueryParser()->parseKeyValue(
                    $partGroup.'[error]'.$partArrayDefinition, $errorState
                );
                $this->getQueryParser()->parseKeyValue(
                    $partGroup.'[size]'.$partArrayDefinition, $part->getSize()
                );
            }
        }
        // set files globals finally.
        $_FILES = $this->getQueryParser()->getResult();
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
        
        // initialize the global files var
        $this->initFiles();

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
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Returns request object
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets response object
     *
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Returns response object
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}