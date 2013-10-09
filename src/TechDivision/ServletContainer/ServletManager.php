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
use TechDivision\ServletContainer\Servlets\ServletConfiguration;

/**
 * The servlet manager handles the servlets registered for the application.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Markus Stockbauer <ms@techdivision.com>
 * @author Tim Wagner <tw@techdivision.com>
 * @author Johann Zelger <jz@techdivision.com>
 */
class ServletManager
{

    /**
     * The application instance.
     *
     * @var \TechDivision\ServletContainer\Application
     */
    protected $application;

    /**
     *
     * @var array
     */
    protected $servlets = array();

    /**
     * Set's the application instance.
     *
     * @param \TechDivision\ServletContainer\Application $application
     *            The application instance
     * @return void
     */
    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     * Has been automatically invoked by the container after the application
     * instance has been created.
     *
     * @return \TechDivision\ServletContainer\ServletManager The servlet manager instance itself
     */
    public function initialize()
    {
        $this->registerServlets();
        return $this;
    }

    /**
     * Finds all servlets which are provided by the webapps and initializes them.
     *
     * @return void
     */
    protected function registerServlets()
    {

        // the phar files have been deployed into folders
        if (is_dir($folder = $this->getWebappPath())) {

            // it's no valid application without at least the web.xml file
            if (! file_exists($web = $folder . DIRECTORY_SEPARATOR . 'WEB-INF' . DIRECTORY_SEPARATOR . 'web.xml')) {
                throw new InvalidApplicationArchiveException(sprintf('Folder %s contains no valid webapp.', $folder));
            }

            // load the application config
            $config = new \SimpleXMLElement(file_get_contents($web));

            // add the default servlet (StaticResourceServlet)
            $this->addDefaultServlet();

            /**
             * @var $mapping \SimpleXMLElement
             */
            foreach ($config->xpath('/web-app/servlet-mapping') as $mapping) {

                // try to resolve the mapped servlet class
                $className = $config->xpath('/web-app/servlet[servlet-name="' . $mapping->{'servlet-name'} . '"]/servlet-class');

                if (! count($className)) {
                    throw new InvalidApplicationArchiveException(sprintf('No servlet class defined for servlet %s', $mapping->{'servlet-name'}));
                }

                // get the string classname
                $className = (string) array_shift($className);

                // instantiate the servlet
                $servlet = $this->getApplication()->newInstance($className);
                $servlet->init($this->getApplication()
                    ->newInstance('TechDivision\ServletContainer\Servlets\ServletConfiguration', array(
                    $this
                )));

                // load the url pattern
                $urlPattern = (string) $mapping->{'url-pattern'};

                // make sure that the URL pattern always starts with a leading slash
                $urlPattern = ltrim($urlPattern, '/');

                // the servlet is added to the dictionary using the complete request path as the key
                $this->addServlet('/' . $urlPattern, $servlet);
            }
        }
    }

    /**
     * Registers the default servlet for the passed webapp.
     *
     * @param $key The
     *            webapp name to register the default servlet for
     * @return false
     */
    protected function addDefaultServlet()
    {
        $defaultServlet = $this->getApplication()->newInstance('TechDivision\ServletContainer\Servlets\StaticResourceServlet');
        $defaultServlet->init($this->getApplication()
            ->newInstance('TechDivision\ServletContainer\Servlets\ServletConfiguration', array(
            $this
        )));
        $this->addServlet('/', $defaultServlet);
    }

    /**
     *
     * @param \TechDivision_Collections_Dictionary $servlets
     */
    public function setServlets($servlets)
    {
        $this->servlets = $servlets;
    }

    /**
     *
     * @return \TechDivision_Collections_Dictionary
     */
    public function getServlets()
    {
        return $this->servlets;
    }

    /**
     * Registers a servlet under the passed key.
     *
     * @param string $key
     *            The servlet to key to register with
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet
     *            The servlet to be registered
     */
    public function addServlet($key, Servlet $servlet)
    {
        $this->servlets[$key] = $servlet;
    }

    /**
     *
     * @return String
     */
    public function getWebappPath()
    {
        return $this->getApplication()->getWebappPath();
    }

    /**
     * Returns the application instance.
     *
     * @return \TechDivision\ServletContainer\Application The application instance
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Returns the host configuration.
     *
     * @return \TechDivision\ApplicationServer\Configuration The host configuration
     */
    public function getConfiguration()
    {
        return $this->getApplication()->getConfiguration();
    }
}