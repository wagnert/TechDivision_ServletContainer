<?php

/**
 * TechDivision\ServletContainer\Servlets\Legacy\NeosServlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Servlets\Legacy;

use TechDivision\ServletContainer\Http\PostRequest;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
class NeosServlet extends PhpServlet
{

    /**
     * Returns the applications webapp path, by default this will be
     * /opt/appserver/webapps/<YOUR-WEBAPP-NAME>.
     *
     * @return string The applications webapp path
     */
    public function getWebappPath()
    {
        return $this->getServletConfig()
            ->getApplication()
            ->getWebappPath();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \TechDivision\ServletContainer\Servlets\PhpServlet::prepareGlobals()
     */
    protected function prepareGlobals(Request $req)
    {
        parent::prepareGlobals($req);
        $req->setServerVar('DOCUMENT_ROOT', $req->getServerVar('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'Web' . DIRECTORY_SEPARATOR);
        $req->setServerVar('SCRIPT_FILENAME', $req->getServerVar('DOCUMENT_ROOT') . 'index.php');
        $req->setServerVar('SCRIPT_NAME', DIRECTORY_SEPARATOR . 'index.php');
        $req->setServerVar('PHP_SELF', DIRECTORY_SEPARATOR . 'index.php');
    }

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
        
        // include the TYPO3.Flow bootstrap file
        require ($this->getWebappPath() . '/Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Core/Bootstrap.php');
        
        // initialize the globals $_SERVER, $_REQUEST, $_POST, $_GET, $_COOKIE, $_FILES
        $this->initGlobals($req);
        
        // this is a bad HACK because it's NOT possible to write to php://stdin
        if ($req instanceof PostRequest) {
            define('HTTP_RAW_POST_DATA', $req->getContent());
        }
        
        // initialize the TYPO3.Flow specific $_SERVER variables
        $_SERVER['FLOW_CONTEXT'] = $this->getServletConfig()->getInitParameter('flowContext');
        $_SERVER['FLOW_SAPITYPE'] = $this->getServletConfig()->getInitParameter('flowSapiType');
        $_SERVER['FLOW_REWRITEURLS'] = (integer) $this->getServletConfig()->getInitParameter('flowRewriteUrls');
        $_SERVER['FLOW_ROOTPATH'] = $this->getWebappPath();
        $_SERVER['REDIRECT_FLOW_CONTEXT'] = $_SERVER['FLOW_CONTEXT'];
        $_SERVER['REDIRECT_FLOW_SAPITYPE'] = $_SERVER['FLOW_SAPITYPE'];
        $_SERVER['REDIRECT_FLOW_REWRITEURLS'] = $_SERVER['FLOW_REWRITEURLS'];
        $_SERVER['REDIRECT_FLOW_ROOTPATH'] = $_SERVER['FLOW_ROOTPATH'];
        $_SERVER['REDIRECT_STATUS'] = $this->getServletConfig()->getInitParameter('redirectStatus');
        
        // check the context and set the default context to 'Development' if not specified in servlet configuration
        $context = $_SERVER['FLOW_CONTEXT'] ?  : ($_SERVER['REDIRECT_FLOW_CONTEXT'] ?  : 'Development');
        $bootstrap = new \TYPO3\Flow\Core\Bootstrap($context);
        
        ob_start();
        $bootstrap->run();
        $res->setContent(ob_get_clean());
    }
}