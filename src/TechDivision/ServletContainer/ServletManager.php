<?php

/**
 * TechDivision\ServletContainer\ServletManager
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
    
namespace TechDivision\ServletContainer;

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;
use TechDivision\ServletContainer\Exceptions\InvalidApplicationArchiveException;

/**
 * The servlet manager handles the servlets registered for the application.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 * @author      Johann Zelger <jz@techdivision.com>
 */
class ServletManager {

    /**
     * The path to the web application.
     * @var string
     */
    protected $webappPath;

    /**
     * @var array
     */
    protected $servlets = array();
    
    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     * 
     * @return \TechDivision\ServletContainer\Application The connected application
     */
    public function initialize() {

        // deploy the web application and register the servlets
        $this->deployWebapps();
        $this->registerServlets();
        
        // return the instance itself
        return $this;
    }

    /**
     * @param $archive
     */
    protected function deployArchive($archive) {
        error_log(__METHOD__ . ' is not implemented!');
    }

    /**
     * Gathers all available archived webapps and deploys them for usage.
     *
     * @param void
     * @return void
     */
    protected function deployWebapps() {
        // gather all the available web application archives and deploy them
        foreach (new \RegexIterator(new \FilesystemIterator($this->getWebappPath()), '/^.*\.phar$/') as $archive) {
            $this->deployArchive($archive);
        }
    }

    /**
     * Registers the default servlet for the passed webapp.
     *
     * @param $key The webapp name to register the default servlet for
     * @return false
     */
    protected function addDefaultServlet($key) {
        $defaultServlet = new StaticResourceServlet();
        $defaultServlet->init();
        $this->addServlet("/$key/*", $defaultServlet);
    }

    /**
     * Finds all servlets which are provided by the webapps and initializes them.
     *
     * @param void
     * @return void
     */
    protected function registerServlets() {

        // the phar files have been deployed into folders
        if (is_dir($folder = $this->getWebappPath())) {

            // it's no valid application without at least the web.xml file
            if (!file_exists($web = $folder . DS . 'WEB-INF' . DS . 'web.xml')) {
                throw new InvalidApplicationArchiveException(sprintf('Folder %s contains no valid webapp.', $folder));
            }

            // add the servlet-specific include path
            set_include_path($folder . PATH_SEPARATOR . get_include_path());

            // load the application config
            $config = new \SimpleXMLElement(file_get_contents($web));

            /** @var $mapping \SimpleXMLElement */
            foreach ($config->xpath('/web-app/servlet-mapping') as $mapping) {

                // try to resolve the mapped servlet class
                $className = $config->xpath(
                    '/web-app/servlet[servlet-name="' . $mapping->{'servlet-name'} . '"]/servlet-class');

                if (!count($className)) {
                    throw new InvalidApplicationArchiveException(sprintf(
                        'No servlet class defined for servlet %s', $mapping->{'servlet-name'}));
                }

                // get the string classname
                $className = (string) array_shift($className);

                // set the additional servlet include paths
                set_include_path($folder . DS . 'WEB-INF' . DS . 'classes' . PS . get_include_path());
                set_include_path($folder . DS . 'WEB-INF' . DS . 'lib' . PS . get_include_path());

                // instanciate the servlet
                $servlet = new $className();

                // initialize the servlet
                $servlet->init();

                // load the url pattern
                $urlPattern = (string) $mapping->{'url-pattern'};

                // make sure that the URL pattern always starts with a leading slash
                $urlPattern = ltrim($urlPattern, '/');

                // the servlet is added to the dictionary using the complete request path as the key
                $this->addServlet('/' . basename($folder) . '/' . $urlPattern,  $servlet);
            }

            // add the default servlet (StaticResourceServlet)
            $this->addDefaultServlet(basename($folder));
        }
    }

    /**
     * @param \TechDivision_Collections_Dictionary $servlets
     */
    public function setServlets($servlets) {
        $this->servlets = $servlets;
    }

    /**
     * @return \TechDivision_Collections_Dictionary
     */
    public function getServlets() {
        return $this->servlets;
    }

    /**
     * @param string              $key
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet
     */
    public function addServlet($key, Servlet $servlet) {
        $this->servlets[$key] = $servlet;
    }

    /**
     * @param String $webappPath
     */
    public function setWebappPath($webappPath) {
        $this->webappPath = $webappPath;
    }

    /**
     * @return String
     */
    public function getWebappPath() {
        return $this->webappPath;
    }
}