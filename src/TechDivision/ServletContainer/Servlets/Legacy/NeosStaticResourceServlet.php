<?php

/**
 * TechDivision\ServletContainer\Servlets\Legacy\NeosStaticResourceServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Servlets\Legacy;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;

/**
 * This is a legacy servlet to handle TYPO3.Neos static resources.
 * 
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class NeosStaticResourceServlet extends StaticResourceServlet
{

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $req
     *            The servlet request
     * @param \TechDivision\ServletContainer\Interfaces\Response $res
     *            The servlet response
     * @throws \TechDivision\ServletContainer\Exceptions\PermissionDeniedException Is thrown if the request tries to execute a PHP file
     * @return void
     */
    public function doGet(Request $req, Response $res)
    {
        
        // get request uri for further rewrite processing
        $uri = $req->getUri();
        
        // Perform rewriting of persistent private resources
        // .htaccess RewriteRule ^(_Resources/Persistent/[a-z0-9]+/(.+/)?[a-f0-9]{40})/.+(\..+) $1$3 [L]
        if (preg_match('/^(\/_Resources\/Persistent\/[a-z0-9]+\/(.+\/)?[a-f0-9]{40})\/.+(\..+)/', $uri, $matches)) {
            $req->setUri($matches[1] . $matches[3]);
            $req->initServerVars();
        }
        
        // Perform rewriting of persistent resource files
        // .htaccess RewriteRule ^(_Resources/Persistent/.{40})/.+(\..+) $1$2 [L]
        if (preg_match('/^(\/_Resources\/Persistent\/.{40})\/.+(\..+)/', $uri, $matches)) {
            $req->setUri($matches[1] . $matches[2]);
            $req->initServerVars();
        }
        
        // prepare the document root
        $req->setServerVar('DOCUMENT_ROOT', $req->getServerVar('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR);
        parent::doGet($req, $res);
    }
}