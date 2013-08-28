<?php

/**
 * TechDivision\ServletContainer\Interfaces\Servlet
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer\Interfaces;

use TechDivision\ServletContainer\Interfaces\ServletConfig;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Socket\HttpClient;

/**
 * Interface for all servlets.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Markus Stockbauer <ms@techdivision.com>
 */
interface Servlet {

    /**
     * @abstract
     * @param ServletConfig $config
     * @throws ServletException;
     * @return mixed
     */
    public function init(ServletConfig $config);

    /**
     * @abstract
     * @return ServletConfig
     */
    public function getServletConfig();

    /**
     * @abstract
     * @param Request $req
     * @param Response $res
     * @throws ServletException
     * @throws IOException
     * @return mixed
     */
    public function service(Request $req, Response $res);

    /**
     * @abstract
     * @return mixed
     */
    public function getServletInfo();

    /**
     * @param HttpClient $client
     * @param Response $response
     * @return mixed
     */
    public function shutdown(HttpClient $client, Response $response);

    /**
     * @abstract
     * @return mixed
     */
    public function destroy();

}
