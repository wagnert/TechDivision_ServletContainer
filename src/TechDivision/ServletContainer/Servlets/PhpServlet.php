<?php

/**
 * TechDivision\ServletContainer\HttpServlet
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;
use TechDivision\ServletContainer\Service\Locator\StaticResourceLocator;
use TechDivision\ServletContainer\Exceptions\PermissionDeniedException;
use TechDivision\ServletContainer\Interfaces\QueryParser;
use TechDivision\ServletContainer\Interfaces\ServletConfig;

/**
 * This servlet emulates an Apache webserver request by initializing the 
 * globals and making them available in the excecuted script.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class PhpServlet extends StaticResourceServlet
{
    
    /**
     * Default directory index file.
     * 
     * @var string
     */
    protected $directoryIndex = 'index.php';

    /**
     * The resource locator necessary to load static resources.
     *
     * @var \TechDivision\ServletContainer\Servlets\StaticResourceServlet
     */
    protected $locator;

    /**
     * Set all headers for php script execution.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The HTTP response to append the headers to
     *
     * @return void
     */
    public function addHeaders(Response $res)
    {
        $res->addHeader(Header::HEADER_NAME_X_POWERED_BY, get_class($this));
        $res->addHeader(Header::HEADER_NAME_EXPIRES, '19 Nov 1981 08:52:00 GMT');
    }
    
    /**
     * Returns the directory index file that defaults to index.php.
     * 
     * @param string $directoryToPrepend Directory to prepend to the default directory index file
     *
     * @return string The directory index file prepended with the passed directory
     */
    protected function getDirectoryIndex($directoryToPrepend = DIRECTORY_SEPARATOR)
    {
        return $directoryToPrepend . $this->directoryIndex;
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
        
        // check if a XHttpRequest has to be handled
        if (($xRequestedWith = $req->getHeader(Header::HEADER_NAME_X_REQUESTED_WITH)) != null) {
            $req->setServerVar('HTTP_X_REQUESTED_WITH', $xRequestedWith);
        }
        
        // check if php script is called to set script and php info
        if (pathinfo($req->getPathInfo(), PATHINFO_EXTENSION) == 'php') {
            $scriptName = $req->getPathInfo();
        } else {
            $scriptName = $this->getDirectoryIndex();
        }
        
        // set the script file information
        $req->setServerVar('SCRIPT_FILENAME', $req->getServerVar('DOCUMENT_ROOT') . $scriptName);
        $req->setServerVar('SCRIPT_NAME', $scriptName);
        $req->setServerVar('PHP_SELF', $scriptName);
    }

    /**
     * Returns the array with the $_FILES vars.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return array The $_FILES vars
     */
    protected function initFileGlobals(Request $req)
    {
        // init query parser
        $this->getQueryParser()->clear();
        // iterate all files
        
        foreach ($req->getParts() as $part) {
            // check if filename is given, write and register it
            if ($part->getFilename()) {
                // generate temp filename
                $tempName = tempnam(ini_get('upload_tmp_dir'), $this->getServletConfig()->getApplication()->getName() . '_');
                // write part
                $part->write($tempName);
                // register uploaded file
                appserver_register_file_upload($tempName);
                // init error state
                $errorState = UPLOAD_ERR_OK;
            } else {
                // set error state
                $errorState = UPLOAD_ERR_NO_FILE;
                // clear tmp file
                $tempName = '';
            }
            // check if file has array info
            if (preg_match('/^([^\[]+)(\[.+)?/', $part->getName(), $matches)) {
                
                // get first part group name and array definition if exists
                $partGroup = $matches[1];
                $partArrayDefinition = '';
                if (isset($matches[2])) {
                    $partArrayDefinition = $matches[2];
                }
                
                $this->getQueryParser()->parseKeyValue($partGroup . '[name]' . $partArrayDefinition, $part->getFilename());
                $this->getQueryParser()->parseKeyValue($partGroup . '[type]' . $partArrayDefinition, $part->getContentType());
                $this->getQueryParser()->parseKeyValue($partGroup . '[tmp_name]' . $partArrayDefinition, $tempName);
                $this->getQueryParser()->parseKeyValue($partGroup . '[error]' . $partArrayDefinition, $errorState);
                $this->getQueryParser()->parseKeyValue($partGroup . '[size]' . $partArrayDefinition, $part->getSize());
            }
        }
        // set files globals finally.
        return $this->getQueryParser()->getResult();
    }

    /**
     * Returns the array with the $_COOKIE vars.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return array The $_COOKIE vars
     */
    protected function initCookieGlobals(Request $req)
    {
        $cookie = array();
        foreach (explode('; ', $req->getHeader(Header::HEADER_NAME_COOKIE)) as $cookieLine) {
            list ($key, $value) = explode('=', $cookieLine);
            $cookie[$key] = $value;
        }
        return $cookie;
    }

    /**
     * Returns the array with the $_REQUEST vars.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return array The $_REQUEST vars
     */
    protected function initRequestGlobals(Request $req)
    {
        return $req->getParameterMap();
    }

    /**
     * Returns the array with the $_POST vars.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return array The $_POST vars
     */
    protected function initPostGlobals(Request $req)
    {
        if ($req->getMethod() == Request::POST) {
            return $req->getParameterMap();
        } else {
            return array();
        }
    }

    /**
     * Returns the array with the $_GET vars.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return array The $_GET vars
     */
    protected function initGetGlobals(Request $req)
    {
        // check post type and set params to globals
        if ($req->getMethod() == 'POST') {
            parse_str($req->getQueryString(), $parameterMap);
        } else {
            $parameterMap = $req->getParameterMap();
        }
        return $parameterMap;
    }

    /**
     * Returns the array with the $_SERVER vars.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return array The $_SERVER vars
     */
    protected function initServerGlobals(Request $req)
    {
        return $req->getServerVars();
    }

    /**
     * Initialize the PHP globals necessary for legacy mode and backward compatibility 
     * for standard applications.
     * 
     * @param \TechDivision\ServletContainer\Interfaces\Request $req The request instance
     *
     * @return void
     */
    protected function initGlobals(Request $req)
    {
        // prepare the request before initializing the globals
        $this->prepareGlobals($req);
        // initialize the globals
        $_SERVER = $this->initServerGlobals($req);
        $_REQUEST = $this->initRequestGlobals($req);
        $_POST = $this->initPostGlobals($req);
        $_GET = $this->initGetGlobals($req);
        $_COOKIE = $this->initCookieGlobals($req);
        $_FILES = $this->initFileGlobals($req);
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
        // let the locator retrieve the file
        $file = $this->locator->locate($req);
        
        // do not directly serve php files
        if (strpos($file->getFilename(), '.php') === false) {
            throw new PermissionDeniedException(sprintf('403 - You do not have permission to access %s', $file->getFilename()));
        }

        // initialize the globals $_SERVER, $_REQUEST, $_POST, $_GET, $_COOKIE, $_FILES and set the headers
        $this->initGlobals($req);
        $this->addHeaders($res);
        
        // start output buffering
        ob_start();
        
        // load the file
        require $file->getPathname();
        
        // store the file's contents in the response
        $res->setContent(ob_get_clean());
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
    public function doPost(Request $req, Response $res)
    {
        $this->doGet($req, $res);
    }
}
