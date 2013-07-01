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
use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;

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
    public function init(ServletConfig $config = null);

    /**
     * @abstract
     * @return ServletConfig
     */
    public function getServletConfig();

    /**
     * @abstract
     * @param ServletRequest $req
     * @param ServletResponse $res
     * @throws ServletException
     * @throws IOException
     * @return mixed
     */
    public function service(ServletRequest $req, ServletResponse $res);

    /**
     * @abstract
     * @return mixed
     */
    public function getServletInfo();

    /**
     * @abstract
     * @return mixed
     */
    public function destroy();

}
