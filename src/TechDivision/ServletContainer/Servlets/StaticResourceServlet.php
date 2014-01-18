<?php

/**
 * TechDivision\ServletContainer\StaticResourceServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Servlets;

use Symfony\Component\Security\Acl\Exception\Exception;
use TechDivision\ServletContainer\Exceptions\FileNotFoundException;
use TechDivision\ServletContainer\Utilities\MimeTypeDictionary;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Servlets\DefaultServlet;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Service\Locator\StaticResourceLocator;
use TechDivision\ServletContainer\Exceptions\PermissionDeniedException;

/**
 * A servlet implementation to handle static file requests.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Markus Stockbauer <ms@techdivision.com>
 * @author Johann Zelger <jz@techdivision.com>
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
     *
     * @param ServletConfig $config            
     * @throws ServletException;
     * @return mixed
     */
    public function init(ServletConfig $config)
    {
        parent::init($config);
        $this->locator = new StaticResourceLocator($this);
        $this->mimeTypeDictionary = new MimeTypeDictionary();
    }
    
    /**
     * (non-PHPdoc)
     * 
     * @see \TechDivision\ServletContainer\Servlets\HttpServlet::doPost()
     */
    public function doPost(Request $req, Response $res)
    {
        $this->doGet($req, $res);
    }

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param Request $req
     *            The servlet request
     * @param Response $res
     *            The servlet response
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(Request $req, Response $res)
    {
        try {
            
            // let the locator retrieve the file
            $file = $this->locator->locate($req);
            
            // do not directly serve php files
            if (strpos($file->getFilename(), '.php') !== false) {
                throw new PermissionDeniedException(sprintf('403 - You do not have permission to access %s', $file->getFilename()));
            }
            
            // set mimetypes to header
            $res->addHeader('Content-Type', $this->mimeTypeDictionary->find(pathinfo($file->getFilename(), PATHINFO_EXTENSION)));
            
            // set last modified date from file
            $res->addHeader('Last-Modified', gmdate('D, d M Y H:i:s \G\M\T', $file->getMTime()));
            
            // set expires date
            $res->addHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 3600));
            
            // check if If-Modified-Since header info is set
            if ($req->getHeader('If-Modified-Since')) {
                // check if file is modified since header given header date
                if (strtotime($req->getHeader('If-Modified-Since')) >= $file->getMTime()) {
                    // send 304 Not Modified Header information without content
                    $res->addHeader('status', 'HTTP/1.1 304 Not Modified');
                    $res->getContent(PHP_EOL);
                    return;
                }
            }
            
            // store the file's contents in the response
            $res->setContent(file_get_contents($file->getRealPath()));
        } catch (\FoundDirInsteadOfFileException $fdiofe) {
            
            // load the information about the requested path
            $pathInfo = $req->getPathInfo();
            
            // if we found a folder AND ending slash is missing, redirect to same folder but with slash appended
            if (substr($pathInfo, - 1) !== '/') {
                
                $res->addHeader("location", $pathInfo . '/');
                $res->addHeader("status", 'HTTP/1.1 301 OK');
                $res->setContent(PHP_EOL);
            }
        } catch (\Exception $e) {
            
            // load the information about the requested path
            $pathInfo = $req->getPathInfo();
            
            $res->addHeader("status", 'HTTP/1.1 404 OK');
            $res->setContent(sprintf('<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL %s was not found on this server.</p></body></html>', $pathInfo));
        }
    }
}