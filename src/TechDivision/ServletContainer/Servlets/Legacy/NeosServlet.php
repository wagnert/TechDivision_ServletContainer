<?php

/**
 * TechDivision\ServletContainer\Servlets\Legacy\NeosServlet
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
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets\Legacy;

use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 * A servlet implementation for neos
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
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
     * Prepares the passed request instance for generating the globals.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return void
     */
    protected function prepareGlobals(ServletRequest $servletRequest)
    {
        // overwrite DOCUMENT_ROOT + REQUEST_URI
        $servletRequest->setServerVar('DOCUMENT_ROOT', $servletRequest->getServerVar('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'Web');
        $servletRequest->setServerVar('REQUEST_URI', str_replace($this->getDirectoryIndex(), '', $servletRequest->getServerVar('REQUEST_URI')));
        // prepare the globals
        parent::prepareGlobals($servletRequest);
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
        
        // include the TYPO3.Flow bootstrap file
        require ($this->getWebappPath() . '/Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Core/Bootstrap.php');
        
        // initialize the globals $_SERVER, $_REQUEST, $_POST, $_GET, $_COOKIE, $_FILES
        $this->initGlobals($servletRequest);
        
        // this is a bad HACK because it's NOT possible to write to php://stdin
        if ($servletRequest->getMethod() == Request::POST) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = $servletRequest->getContent();
        }
        
        // initialize the TYPO3.Flow specific $_SERVER variables
        $_SERVER['FLOW_CONTEXT'] = $this->getServletConfig()->getInitParameter('flowContext');
        $_SERVER['FLOW_SAPITYPE'] = $this->getServletConfig()->getInitParameter('flowSapiType');
        $_SERVER['FLOW_REWRITEURLS'] = $this->getServletConfig()->getInitParameter('flowRewriteUrls');
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
        $servletResponse->setContent(ob_get_clean());
    }
}
