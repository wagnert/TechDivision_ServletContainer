<?php

/**
 * TechDivision\ServletContainer\Http\HttpServletRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Http;

use TechDivision\ServletContainer\Http\Request;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;

/**
 * The Http servlet request implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
class HttpServletRequest implements ServletRequest {

    /**
     * @var String
     */
    protected $inputStream;

    /**
     * @var \TechDivision\ServletContainer\Http\Request
     */
    protected $request;

    /**
     * @var \TechDivision\ServletContainer\Interfaces\ServletResponse
     */
    protected $response;

    /**
     * @var PersistentSessionManager
     */
    protected $sessionManager;

    protected $cookies = array();

    /**
     * @param \TechDivision\ServletContainer\Http\Request $request
     */
    private function __construct($request) {
        $this->setRequest($request);
        $this->sessionManager = new PersistentSessionManager();
    }

    /**
     * @param \TechDivision_Lang_String $inputStream
     * @return void
     */
    public function setInputStream($inputStream) {
        $this->inputStream = $inputStream;
    }


    /**
     * @param \TechDivision_Lang_String $request
     * @return HttpServletRequest
     */
    public static function factory($request) {
        return new HttpServletRequest($request);
    }

    /**
     * @param \TechDivision\ServletContainer\Http\Request $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    public function getRequest() {
        return $this->request;
    }

    public function getInputStream() {
        return $this->inputStream;
    }

    public function getRequestUrl() {
        return $this->getRequest()->getPathInfo();
    }

    public function getRequestUri() {
        return $this->getRequest()->getUri();
    }

    public function getRequestQueryString() {
        return $this->getRequest()->getQueryString();
    }

    public function getRequestParameter(){
        return $this->getRequest()->getParameter();
    }

    public function getRequestParameterMap(){
        return $this->getRequest()->getParameterMap();
    }

    public function getRequestHeaders(){
        return $this->getRequest()->getHeaders();
    }

    public function getRequestContent(){
        return $this->getRequest()->getContent();
    }

    public function getRequestMethod(){
        return $this->getRequest()->getMethod();
    }

    public function hasCookie($cookieName) {
        return array_key_exists($cookieName, $this->cookies);
    }

    public function getCookie($cookieName) {
        if ($this->hasCookie($cookieName)) {
            return $this->cookies[$cookieName];
        }
    }

    public function setResponse(ServletResponse $response) {
        $this->response = $response;
    }

    public function getResponse() {
        return $this->response;
    }

    /**
     * Returns the session for this request.
     *
     * @return Session
     */
    public function getSession() {

        if (is_null($this->session)) {
            $this->session = $this->sessionManager->getSessionForRequest($this);
        }

        return $this->session;
    }
}