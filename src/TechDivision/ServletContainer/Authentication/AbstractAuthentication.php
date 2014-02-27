<?php

/**
 * TechDivision\ServletContainer\ServletManager
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
 * @subpackage Authentication
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Authentication;

use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Http\ServletRequest;
use TechDivision\ServletContainer\Http\ServletResponse;

/**
 * Abstract class for authentication adapters.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Authentication
 * @author     Philipp Dittert <pd@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class AbstractAuthentication
{

    /**
     * Basic HTTP authentication method
     */
    const AUTHENTICATION_METHOD_BASIC = 'Basic';

    /**
     * Digest HTTP authentication method
     */
    const AUTHENTICATION_METHOD_DIGEST = 'Digest';

    /**
     * Holds Servlet Object
     *
     * @var \TechDivision\ServletContainer\Interfaces\Servlet
     */
    protected $servlet;

    /**
     * Holds Request object
     *
     * @var \TechDivision\ServletContainer\Http\ServletRequest
     */
    protected $servletRequest;

    /**
     * Holds Response
     *
     * @var \TechDivision\ServletContainer\Http\ServletResponse
     */
    protected $servletResponse;

    /**
     * alternative manuell called constructor
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet   $servlet         The servlet to process
     * @param \TechDivision\ServletContainer\Http\ServletRequest  $servletRequest  The request instance
     * @param \TechDivision\ServletContainer\Http\ServletResponse $servletResponse The response instance
     *
     * @return void
     */
    public function init(Servlet $servlet, ServletRequest $servletRequest, ServletResponse $servletResponse)
    {
        $this->setServlet($servlet);
        $this->setServletResponse($servletRequest);
        $this->setServletRequest($servletResponse);
    }

    /**
     * Set's Servlet object
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet A servlet instance
     *
     * @return void
     */
    protected function setServlet($servlet)
    {
        $this->servlet = $servlet;
    }

    /**
     * Returns Servlet object
     *
     * @return \TechDivision\ServletContainer\Interfaces\Servlet
     */
    protected function getServlet()
    {
        return $this->servlet;
    }

    /**
     * Sets servlet request object.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletRequest The request instance
     *
     * @return void
     */
    protected function setServletRequest(ServletRequest $servletRequest)
    {
        $this->servletRequest = $servletRequest;
    }

    /**
     * Returns servlet request object.
     *
     * @return \TechDivision\ServletContainer\Http\ServletRequest The servlet request instance
     */
    protected function getServletRequest()
    {
        return $this->servletRequest;
    }

    /**
     * Sets servlet response object.
     *
     * @param \TechDivision\ServletContainer\Http\ServletRequest $servletResponse The response instance
     *
     * @return void
     */
    protected function setServletResponse(ServletResponse $servletResponse)
    {
        $this->servletResponse = $servletResponse;
    }

    /**
     * Returns servlet response object.
     * 
     * @return \TechDivision\ServletContainer\Http\ServletResponse The servlet response instance
     */
    protected function getServletResponse()
    {
        return $this->servletResponse;
    }
}
