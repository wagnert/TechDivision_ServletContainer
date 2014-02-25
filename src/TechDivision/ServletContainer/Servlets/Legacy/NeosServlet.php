<?php
/**
 * TechDivision\ServletContainer\Servlets\Legacy\NeosServlet
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets\Legacy;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 * A servlet implementation for neos
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
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
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return void
     */
    protected function prepareGlobals(Request $req)
    {
        // overwrite DOCUMENT_ROOT + REQUEST_URI
        $req->setServerVar('DOCUMENT_ROOT', $req->getServerVar('DOCUMENT_ROOT') . DIRECTORY_SEPARATOR . 'Web');
        $req->setServerVar('REQUEST_URI', str_replace($this->getDirectoryIndex(), '', $req->getServerVar('REQUEST_URI')));
        // prepare the globals
        parent::prepareGlobals($req);
    }

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The servlet request
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The servlet response
     *
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
        if ($req->getMethod() == Request::POST) {
            $GLOBALS['HTTP_RAW_POST_DATA'] = $req->getContent();
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
        $res->setContent(ob_get_clean());
    }
}
