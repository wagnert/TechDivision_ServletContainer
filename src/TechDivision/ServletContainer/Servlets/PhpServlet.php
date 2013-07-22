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
 * @author      Johann Zelger <j.zelger@techdivision.com>
 */
class PhpServlet extends StaticResourceServlet {

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param Request $req The servlet request
     * @param Response $res The servlet response
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(Request $req, Response $res) {

        // instanciate the resource locator
        $locator = new StaticResourceLocator($this);

        // let the locator retrieve the file
        $file = $locator->locate($req);

        // do not directly serve php files
        if (strpos($file->getFilename(), '.php') === false) {
            throw new PermissionDeniedException(sprintf(
                '403 - You do not have permission to access %s', $file->getFilename()));
        }

        // start output buffering
        ob_start();

        // load the file
        require_once $file->getFilename();

        // store the file's contents in the response
        $res->setContent(ob_get_clean());
    }

    /**
     * @see \TechDivision\ServletContainer\Servlets\PhpServlet::doGet()
     */
    public function doPost(Request $req, Response $res) {
        $this->doGet($req, $res);
    }
}