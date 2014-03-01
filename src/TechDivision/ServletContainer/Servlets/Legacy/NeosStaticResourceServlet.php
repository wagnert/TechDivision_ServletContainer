<?php

/**
 * TechDivision\ServletContainer\Servlets\Legacy\NeosStaticResourceServlet
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
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets\Legacy;

use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;

/**
 * This is a legacy servlet to handle TYPO3.Neos static resources.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class NeosStaticResourceServlet extends StaticResourceServlet
{

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
        
        // get request uri for further rewrite processing
        $uri = $servletRequest->getUri();
        
        // Perform rewriting of persistent private resources
        // .htaccess RewriteRule ^(_Resources/Persistent/[a-z0-9]+/(.+/)?[a-f0-9]{40})/.+(\..+) $1$3 [L]
        if (preg_match('/^(\/_Resources\/Persistent\/[a-z0-9]+\/(.+\/)?[a-f0-9]{40})\/.+(\..+)/', $uri, $matches)) {
            $servletRequest->setUri($matches[1] . $matches[3]);
            $servletRequest->initServerVars();
        }
        
        // Perform rewriting of persistent resource files
        // .htaccess RewriteRule ^(_Resources/Persistent/.{40})/.+(\..+) $1$2 [L]
        if (preg_match('/^(\/_Resources\/Persistent\/.{40})\/.+(\..+)/', $uri, $matches)) {
            $servletRequest->setUri($matches[1] . $matches[2]);
            $servletRequest->initServerVars();
        }
        
        // prepare the document root
        $servletRequest->setServerVar('DOCUMENT_ROOT', $servletRequest->getServerVar('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR);
        parent::doGet($servletRequest, $servletResponse);
    }
}
