<?php

/**
 * TechDivision\ServletContainer\Service\Locator\ServletLocator
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Service\Locator;

use TechDivision\ServletContainer\Service\Locator\ResourceLocatorInterface;
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\Servlet;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * The servlet resource locator implementation.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 * @author      Tim Wagner <tw@techdivision.com>
 */
class ServletLocator implements ResourceLocatorInterface {

    /**
     * The servlet manager instance.
     * @var \TechDivision\ServletContainer\ServletManager
     */
    protected $servletManager;

    /**
     * Initializes the locator with the actual servlet manager instance.
     *
     * @param \TechDivision\ServletContainer\ServletManager $servletManager The servlet manager instance
     * @return void
     */
    public function __construct($servletManager) {
        $this->servletManager = $servletManager;
    }

    /**
     * Prepares a collection with routes generated from the available servlets
     * ans their servlet mappings.
     *
     * @return \Symfony\Component\Routing\RouteCollection The collection with the available routes
     */
    public function getRouteCollection() {

        // retrieve the registered servlets
        $servlets = $this->servletManager->getServlets();

        // prepare the collection with the available routes and initialize the route counter
        $routes = new RouteCollection();
        $counter = 0;

        // iterate over the available servlets and prepare the routes
        foreach ($servlets as $urlPattern => $servlet) {
            $pattern = str_replace('/*', "/{placeholder_$counter}", $urlPattern);
            $route = new Route($pattern, array($servlet), array("{placeholder_$counter}" => '.*'));
            $routes->add($counter++, $route);
        }

        // return the collection with the routes
        return $routes;
    }

    /**
     * Tries to locate the servlet that handles the request and returns the instance if one can be found.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletRequest $request
     * @return \TechDivision\ServletContainer\Interfaces\Servlet
     * @see \TechDivision\ServletContainer\Service\Locator\ResourceLocatorInterface::locate()
     */
    public function locate(ServletRequest $request) {

        // build the file-path of the request
        $path = $request->getRequestUrl();

        // load the route collection
        $routes = $this->getRouteCollection();

        // initialize the URL matcher
        $matcher = new UrlMatcher($routes, new RequestContext($path));

        // traverse the path to find matching servlet
        do {

            try {
                $servlet = $matcher->match($path);
                $request->setScriptName($path);
                break;
            } catch(ResourceNotFoundException $rnfe) {
                $path = substr($path, 0, strrpos($path, '/'));
            }

        } while (strpos($path, '/') !== FALSE);

        // return the servlet instance
        return current($servlet);
    }
}
