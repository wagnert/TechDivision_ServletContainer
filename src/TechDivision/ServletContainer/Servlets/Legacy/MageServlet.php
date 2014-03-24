<?php

/**
 * TechDivision\ServletContainer\Servlets\Legacy\MageServlet
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

use TechDivision\ServletContainer\Http\HttpPart;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;
use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Servlets\PhpServlet;

/**
 * A servlet implementation for Magento.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2014 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class MageServlet extends PhpServlet
{

    /**
     * Prepares the passed request instance for generating the globals.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return void
     */
    protected function prepareGlobals(ServletRequest $servletRequest)
    {
        // prepare the globals
        parent::prepareGlobals($servletRequest);
        
        // if the application has not been called over a vhost configuration append application folder name
        if ($this->getServletConfig()->getApplication()->isVhostOf($servletRequest->getServerName()) === true) {
            $directoryIndex = 'index.php';
        } else {
            $directoryToPrepend = DIRECTORY_SEPARATOR . $this->getServletConfig()->getApplication()->getName() . DIRECTORY_SEPARATOR;
            $directoryIndex = $directoryToPrepend . 'index.php';
        }
        
        // initialize the server variables
        $servletRequest->setServerVar('SCRIPT_FILENAME', $servletRequest->getServerVar('DOCUMENT_ROOT') . $directoryIndex);
        $servletRequest->setServerVar('SCRIPT_NAME', $directoryIndex);
        $servletRequest->setServerVar('PHP_SELF', $directoryIndex);
        
        // ATTENTION: This is necessary because of a Magento bug!!!!
        $servletRequest->setServerVar('SERVER_PORT', null);
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
        // load \Mage
        $this->load();
        // init globals
        $this->initGlobals($servletRequest);
        // run \Mage and set content
        $servletResponse->setContent($this->run($servletRequest));
    }

    /**
     * Loads the necessary files needed.
     *
     * @return void
     */
    public function load()
    {
        require_once $this->getServletConfig()->getWebappPath() . '/app/Mage.php';
    }

    /**
     * Runs the WebApplication
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return string The web applications content
     */
    public function run(ServletRequest $servletRequest)
    {
        
        try {
            
            // register the Magento autoloader as FIRST autoloader
            spl_autoload_register(array(new \Varien_Autoload(), 'autoload'), true, true);

            // Varien_Profiler::enable();
            if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
                \Mage::setIsDeveloperMode(true);
            }
            
            ini_set('display_errors', 1);
            umask(0);
            
            // store or website code
            $mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
            
            // run store or run website
            $mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
            
            // set headers sent to false and start output caching
            appserver_set_headers_sent(false);
            ob_start();
            
            // reset and run Magento
            \Mage::reset();
            \Mage::run();
            
            // write the session back after the request
            session_write_close();

            // grab the contents generated by Magento
            $content = ob_get_clean();
            
        } catch (\Exception $e) {
            error_log($content = $e->__toString());
        }

        // return the content
        return $content;
    }
}
