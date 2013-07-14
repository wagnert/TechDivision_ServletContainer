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
use TechDivision\ServletContainer\Servlets\DefaultServlet;
use TechDivision\ServletContainer\Interfaces\ServletResponse;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Service\Locator\StaticResourceLocator;
use TechDivision\ServletContainer\Exceptions\PermissionDeniedException;

/**
 * A servlet implementation to handle static file requests.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
class StaticResourceServlet extends HttpServlet {

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletRequest $req The servlet request
     * @param \TechDivision\ServletContainer\Interfaces\ServletResponse $res The servlet response
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(ServletRequest $req, ServletResponse $res) {


        try {

            // instanciate the resource locator
            $locator = new StaticResourceLocator($this);

            // let the locator retrieve the file
            $file = $locator->locate($req);

        } catch(\Exception $e) {

            error_log($e->__toString());

            // load the information about the requested path
            $pathInfo = $req->getPathInfo();

            // if ending slash is missing, redirect to same folder but with slash appended
            if (substr($pathInfo, -1) !== '/') {

                $res->addHeader("location", $pathInfo . '/');
                $res->addHeader("status", 'HTTP/1.1 301 OK');
                $res->setContent(PHP_EOL);

                return;
            }
        }

        // do not directly serve php files
        if (strpos($file->getFilename(), '.php') !== false) {
            throw new PermissionDeniedException(sprintf(
                '403 - You do not have permission to access %s', $file->getFilename()));
        }

        if (strpos($file->getFilename(), '.css') !== false) {
            $res->addHeader('Content-Type', 'text/css');
        } elseif (strpos($file->getFilename(), '.gif') !== false) {
            $res->addHeader('Content-Type', 'image/gif');
        } elseif (strpos($file->getFilename(), '.jpg') !== false) {
            $res->addHeader('Content-Type', 'image/jpg');
        } elseif (strpos($file->getFilename(), '.js') !== false) {
            $res->addHeader('Content-Type', 'text/javascript');
        }  else {
            error_log("Can't serve filename: " . $file->getFilename());
        }

        // store the file's contents in the response
        $res->setContent(file_get_contents($file->getRealPath()));
    }
}