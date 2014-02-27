<?php

/**
 * TechDivision\ServletContainer\Servlets\StaticResourceServlet
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
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Exceptions\FileNotFoundException;
use TechDivision\ServletContainer\Utilities\MimeTypeDictionary;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Servlets\DefaultServlet;
use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Service\Locator\StaticResourceLocator;
use TechDivision\ServletContainer\Exceptions\PermissionDeniedException;

/**
 * A servlet implementation to handle static file requests.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class StaticResourceServlet extends HttpServlet
{

    /**
     * Hold dictionary for mimetypes
     *
     * @var MimeTypeDictionary
     */
    protected $mimeTypeDictionary;

    /**
     * The resource locator necessary to load static resources.
     *
     * @var \TechDivision\ServletContainer\Servlets\StaticResourceServlet
     */
    protected $locator;

    /**
     * Initializes the servlet with the passed configuration.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletConfig $config The configuration to initialize the servlet with
     *
     * @throws \TechDivision\ServletContainer\Exceptions\ServletException Is thrown if the configuration has errors
     * @return void
     */
    public function init(ServletConfig $config)
    {
        parent::init($config);
        $this->locator = new StaticResourceLocator($this);
        $this->mimeTypeDictionary = new MimeTypeDictionary();
    }

    /**
     * Implements Http POST method.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doPost(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        $this->doGet($servlerRequest, $servletResponse);
    }

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        try {
            
            // let the locator retrieve the file
            $fileInfo = $this->locator->locate($servletRequest);
            
            // do not directly serve php files
            if (strpos($fileInfo->getFilename(), '.php') !== false) {
                throw new PermissionDeniedException(sprintf('403 - You do not have permission to access %s', $fileInfo->getFilename()));
            }
            
            // set mimetypes to header
            $servletResponse->addHeader(Header::HEADER_NAME_CONTENT_TYPE, $this->mimeTypeDictionary->find($fileInfo->getExtension()));
            
            // set last modified date from file
            $servletResponse->addHeader(Header::HEADER_NAME_LAST_MODIFIED, gmdate('D, d M Y H:i:s \G\M\T', $fileInfo->getMTime()));
            
            // set expires date
            $servletResponse->addHeader(Header::HEADER_NAME_EXPIRES, gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
            
            // check if If-Modified-Since header info is set
            if ($servletRequest->getHeader(Header::HEADER_NAME_IF_MODIFIED_SINCE)) {
                // check if file is modified since header given header date
                if (strtotime($servletRequest->getHeader(Header::HEADER_NAME_IF_MODIFIED_SINCE)) >= $fileInfo->getMTime()) {
                    // send 304 Not Modified Header information without content
                    $servletResponse->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 304 Not Modified');
                    $servletResponse->getContent(PHP_EOL);
                    return;
                }
            }

            // remove the headers to prevent response from beeing cached
            $servletResponse->removeHeader(Header::HEADER_NAME_CACHE_CONTROL);
            $servletResponse->removeHeader(Header::HEADER_NAME_PRAGMA);
            
            // store the file's contents in the response
            $servletResponse->setContent(file_get_contents($fileInfo->getRealPath()));
            
        } catch (\FoundDirInsteadOfFileException $fdiofe) {
            
            // load the information about the requested path
            $pathInfo = $servletRequest->getPathInfo();
            
            // if we found a folder AND ending slash is missing, redirect to same folder but with slash appended
            if (substr($pathInfo, - 1) !== '/') {
                $servletResponse->addHeader(Header::HEADER_NAME_LOCATION, $pathInfo . '/');
                $servletResponse->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 301 OK');
                $servletResponse->setContent(PHP_EOL);
            }
            
        } catch (\Exception $e) {
            
            // load the information about the requested path
            $pathInfo = $servletRequest->getPathInfo();
            
            $servletResponse->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 404 OK');
            $servletResponse->setContent(
                sprintf(
                    '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
                     <html>
                         <head><title>404 Not Found</title></head>
                         <body><h1>Not Found</h1><p>The requested URL %s was not found on this server.</p></body>
                     </html>',
                    $pathInfo
                )
            );
        }
    }
}
