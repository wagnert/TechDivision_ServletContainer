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
use TechDivision\ServletContainer\Exceptions\InvalidServletMappingException;

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
     * Array that contains the servlet mappings
     * 
     * @var array
     */
    protected $servletMappings = array();
    
    /**
     * Array with the servlet's init parameters found in the web.xml configuration file.
     * 
     * @var array
     */
    protected $initParameter = array();

    /**
     * Webapps SecurityContext
     *
     * @var array
     */
    protected $securedUrlConfigs = array();

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
            
            // add the default servlet (StaticResourceServlet)
            $this->addDefaultServlet();
            
            // load the application config
            $config = new \SimpleXMLElement(file_get_contents($web));


            // parse for securityConfigs
            $securityConfigs = array();
            foreach ($config->xpath('/web-app/security') as $securityParam) {
                $securityConfigs[] = json_decode( json_encode($securityParam) , 1);
            }
            $this->setSecuredUrlConfigs($securityConfigs);


            // initialize the context by parsing the context-param nodes
            foreach ($config->xpath('/web-app/context-param') as $contextParam) {
                $this->addInitParameter((string) $contextParam->{'param-name'}, (string) $contextParam->{'param-value'});
            }
            
            // initialize the servlets by parsing the servlet-mapping nodes
            foreach ($config->xpath('/web-app/servlet') as $servlet) {
                
                // load the servlet name and check if it already has been initialized
                $servletName = (string) $servlet->{'servlet-name'};
                if (array_key_exists($servletName, $this->servlets)) {
                    continue;
                }
                
                // try to resolve the mapped servlet class
                $className = (string) $servlet->{'servlet-class'};
                if (! count($className)) {
                    throw new InvalidApplicationArchiveException(sprintf('No servlet class defined for servlet %s', $servlet->{'servlet-class'}));
                }
                
                // instantiate the servlet
                $instance = $this->getApplication()->newInstance($className);
                
                //  initialize the servlet configuration
                $servletConfig = $this->getApplication()->newInstance('TechDivision\ServletContainer\Servlets\ServletConfiguration', array(
                    $this
                ));
                
                // set the unique servlet name
                $servletConfig->setServletName($servletName);
                
                // append the init params to the servlet configuration
                foreach ($servlet->{'init-param'} as $initParam) {
                    $servletConfig->addInitParameter((string) $initParam->{'param-name'}, (string) $initParam->{'param-value'});
                }

                // inject query parser
                $instance->injectQueryParser($this->getApplication()->newInstance('TechDivision\ServletContainer\Http\HttpQueryParser'));
                
                // initialize the servlet
                $instance->init($servletConfig);
                
                // the servlet is added to the dictionary using the complete request path as the key
                $this->addServlet((string) $servlet->{'servlet-name'}, $instance);
            }
            
            // initialize the servlets by parsing the servlet-mapping nodes
            foreach ($config->xpath('/web-app/servlet-mapping') as $mapping) {
                
                // load the url pattern and the servlet name
                $urlPattern = (string) $mapping->{'url-pattern'};
                $servletName = (string) $mapping->{'servlet-name'};
                
                // make sure that the URL pattern always starts with a leading slash
                $urlPattern = ltrim($urlPattern, '/');
                
                // the servlet is added to the dictionary using the complete request path as the key
                if (!array_key_exists($servletName, $this->servlets)) {
                    throw new InvalidServletMappingException(sprintf("Can't find servlet %s for url-pattern %s", $servletName, $urlPattern));
                }
                
                // append the url-pattern - servlet mapping to the array
                $this->servletMappings['/' . $urlPattern] = (string) $mapping->{'servlet-name'};

                $this->application->getInitialContext()->getSystemLogger()->debug(
                    sprintf('Successfully initialized servlet %s for url-pattern %s in application %s',
                        $servletName, $urlPattern, $this->application->getName()));
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
        $defaultServletName = 'StaticResourceServlet';
        $defaultServlet = $this->getApplication()->newInstance('TechDivision\ServletContainer\Servlets\StaticResourceServlet');
        $config = $this->getApplication()
            ->newInstance('TechDivision\ServletContainer\Servlets\ServletConfiguration', array(
            $this
        ));
        $config->setServletName($defaultServletName);
        $defaultServlet->init($config);
        $this->addServlet($defaultServletName, $defaultServlet);
        $this->servletMappings['/'] = $defaultServletName;
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
     * @return array|\TechDivision_Collections_Dictionary
     */
    public function getServlets()
    {
        return $this->servlets;
    }
    
    /**
     * Returns the servlet mappings found in the
     * configuration file.
     * 
     * @return array The servlet mappings
     */
    public function getServletMappings()
    {
        return $this->servletMappings;
    }

    /**
     * Returns the servlet with the passed name.
     * 
     * @param string $key The name of the servlet to return
     * @return \TechDivision\ServletContainer\Interfaces\Servlet The servlet instance
     */
    public function getServlet($key)
    {
        if (array_key_exists($key, $this->servlets)) {
            return $this->servlets[$key];
        }
    }
    
    /**
     * Returns the servlet for the passed URL mapping.
     * 
     * @param string $urlMapping The URL mapping to return the servlet for
     * @return \TechDivision\ServletContainer\Interfaces\Servlet The servlet instance
     */
    public function getServletByMapping($urlMapping)
    {
        if (array_key_exists($urlMapping, $this->servletMappings)) {
            return $this->getServlet($this->servletMappings[$urlMapping]);
        }
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
     * Returns the path to the webapp.
     * 
     * @return string The path to the webapp
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
    
    /**
     * Register's the init parameter under the passed name.
     * 
     * @param string $name Name to register the init parameter with
     * @param string $value The value of the init parameter
     */
    public function addInitParameter($name, $value)
    {
        $this->initParameter[$name] = $value;
    }
    
    /**
     * Return's the init parameter with the passed name.
     * 
     * @param string $name Name of the init parameter to return
     */
    public function getInitParameter($name)
    {
        if (array_key_exists($name, $this->initParameter)) {
            return $this->initParameter[$name];
        }
    }

    public function getSecuredUrlConfigs()
    {
        return $this->securedUrlConfigs;
    }

    public function setSecuredUrlConfigs($securedUrlConfigs)
    {
        $this->securedUrlConfigs = $securedUrlConfigs;
    }
}