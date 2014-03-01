<?php

/**
 * TechDivision\ServletContainer\Servlets\HttpServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Servlets\GenericServlet;
use TechDivision\ServletContainer\Exceptions\MethodNotImplementedException;

/**
 * Abstract Http servlet implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class HttpServlet extends GenericServlet
{

    /**
     * Implements Http CONNECT method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doConnect(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http DELETE method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doDelete(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http GET method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doGet(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http HEAD method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doHead(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http OPTIONS method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doOptions(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http POST method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doPost(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http PUT method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doPut(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements Http TRACE method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not implemented
     */
    public function doTrace(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Delegation method for specific Http methods:
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     * 
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException Is thrown if the request method is not available
     */
    public function service(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        
        // pre-initialize response
        $servletResponse->addHeader(Header::HEADER_NAME_SERVER, $servletRequest->getServerVar('SERVER_SOFTWARE'));
        $servletResponse->addHeader(Header::HEADER_NAME_X_POWERED_BY, get_class($this));

        // check if servlet needs authentication and return if authentication is not provided.
        if ($this->getAuthenticationRequired() && !$this->getAuthenticationManager()->handleRequest($servletRequest, $servletResponse, $this)) {
            return;
        }
        
        // load the information about the requested URI
        $uri = $servletRequest->getUri();
        $documentRoot = $servletRequest->getServerVar('DOCUMENT_ROOT');
        
        // create a file info object to check if a directory is requested
        $fileInfo = new \SplFileInfo($documentRoot . $uri);
        
        // check if a directory/webapp was been called without ending slash
        if ($fileInfo->isDir() && strrpos($uri, '/') !== strlen($uri) - 1) {
            
            // redirect to path with ending slash
            $servletResponse->addHeader(Header::HEADER_NAME_LOCATION, $uri . '/');
            $servletResponse->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 301 OK');
            $servletResponse->setContent(PHP_EOL);
            
            return;
        }
        
        // check the request method to invoke the appropriate method
        switch ($servletRequest->getMethod()) {
            case Request::CONNECT:
                $this->doConnect($servletRequest, $servletResponse);
                break;
            case Request::DELETE:
                $this->doDelete($servletRequest, $servletResponse);
                break;
            case Request::GET:
                $this->doGet($servletRequest, $servletResponse);
                break;
            case Request::HEAD:
                $this->doHead($servletRequest, $servletResponse);
                break;
            case Request::OPTIONS:
                $this->doOptions($servletRequest, $servletResponse);
                break;
            case Request::POST:
                $this->doPost($servletRequest, $servletResponse);
                break;
            case Request::PUT:
                $this->doPut($servletRequest, $servletResponse);
                break;
            case Request::TRACE:
                $this->doTrace($servletRequest, $servletResponse);
                break;
            default:
                throw new MethodNotImplementedException(
                    sprintf('%s is not implemented yet.', $servletRequest->getMethod())
                );
        }
    }
}
