<?php

/**
 * TechDivision\ServletContainer\Servlets\PhpServlet
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
 * @author     Tim Wagner <tw@techdivision.com>
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;
use TechDivision\ServletContainer\Service\Locator\PhpResourceLocator;
use TechDivision\ServletContainer\Exceptions\PermissionDeniedException;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\QueryParser;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Exceptions\FoundDirInsteadOfFileException;

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
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class PhpServlet extends StaticResourceServlet
{

    /**
     * The base directory of the actual webapp.
     * 
     * @var string
     */
    protected $webappPath;
    
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
        $this->locator = new PhpResourceLocator($this);
        $this->webappPath = $this->getServletConfig()->getWebappPath();
    }
    
    /**
     * Returns the base directory of the actual webapp.
     * 
     * @return string The base directory
     */
    protected function getWebappPath()
    {
        return $this->webappPath;
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
        // check if a XHttpRequest has to be handled
        if (($xRequestedWith = $servletRequest->getHeader(Header::HEADER_NAME_X_REQUESTED_WITH)) != null) {
            $servletRequest->setServerVar('HTTP_X_REQUESTED_WITH', $xRequestedWith);
        }
    }

    /**
     * Returns the array with the $_FILES vars.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return array The $_FILES vars
     */
    protected function initFileGlobals(ServletRequest $servletRequest)
    {
        // init query parser
        $this->getQueryParser()->clear();
        
        // iterate all files
        foreach ($servletRequest->getParts() as $part) {
            
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
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return array The $_COOKIE vars
     */
    protected function initCookieGlobals(ServletRequest $servletRequest)
    {
        $cookie = array();
        foreach (explode('; ', $servletRequest->getHeader(Header::HEADER_NAME_COOKIE)) as $cookieLine) {
            list ($key, $value) = explode('=', $cookieLine);
            $cookie[$key] = $value;
        }
        return $cookie;
    }

    /**
     * Returns the array with the $_REQUEST vars.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return array The $_REQUEST vars
     */
    protected function initRequestGlobals(ServletRequest $servletRequest)
    {
        return $servletRequest->getParameterMap();
    }

    /**
     * Returns the array with the $_POST vars.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return array The $_POST vars
     */
    protected function initPostGlobals(ServletRequest $servletRequest)
    {
        if ($servletRequest->getMethod() == Request::POST) {
            return $servletRequest->getParameterMap();
        } else {
            return array();
        }
    }

    /**
     * Returns the array with the $_GET vars.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return array The $_GET vars
     */
    protected function initGetGlobals(ServletRequest $servletRequest)
    {
        // check post type and set params to globals
        if ($servletRequest->getMethod() == Request::POST) {
            parse_str($servletRequest->getQueryString(), $parameterMap);
        } else {
            $parameterMap = $servletRequest->getParameterMap();
        }
        return $parameterMap;
    }

    /**
     * Returns the array with the $_SERVER vars.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return array The $_SERVER vars
     */
    protected function initServerGlobals(ServletRequest $servletRequest)
    {
        return $servletRequest->getServerVars();
    }

    /**
     * Initialize the PHP globals necessary for legacy mode and backward compatibility 
     * for standard applications.
     * 
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return void
     */
    protected function initGlobals(ServletRequest $servletRequest)
    {
        
        // prepare the request before initializing the globals
        $this->prepareGlobals($servletRequest);
        
        // initialize the globals
        $_SERVER = $this->initServerGlobals($servletRequest);
        $_REQUEST = $this->initRequestGlobals($servletRequest);
        $_POST = $this->initPostGlobals($servletRequest);
        $_GET = $this->initGetGlobals($servletRequest);
        $_COOKIE = $this->initCookieGlobals($servletRequest);
        $_FILES = $this->initFileGlobals($servletRequest);
    }

    /**
     * Tries to load the requested file and adds the content to the response.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     * 
     * @return void
     */
    public function doGet(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        
        // try to locate the file
        $fileInfo = $this->getLocator()->locate($servletRequest);

        // initialize the globals $_SERVER, $_REQUEST, $_POST, $_GET, $_COOKIE, $_FILES and set the headers
        $this->initGlobals($servletRequest);
        
        // add this header to prevent .php request to be cached
        $servletResponse->addHeader(Header::HEADER_NAME_EXPIRES, '19 Nov 1981 08:52:00 GMT');
        
        // start output buffering
        ob_start();
        
        // load the file
        require $fileInfo->getPathname();
        
        // store the file's contents in the response
        $servletResponse->setContent(ob_get_clean());
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
    public function doPost(ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        $this->doGet($servletRequest, $servletResponse);
    }
}
