<?php
/**
 * TechDivision\ServletContainer\ServletManager
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

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Servlet;

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
     * @var Servlet
     */
    protected $servlet;

    /**
     * Holds Request object
     *
     * @var Request
     */
    protected $request;

    /**
     * Holds Response
     *
     * @var Response
     */
    protected $response;

    /**
     * alternative manuell called constructor
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet  $servlet The servlet to process
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req     The request object
     * @param \TechDivision\ServletContainer\Interfaces\Response $res     The response object
     *
     * @return void
     */
    public function init(Servlet $servlet, Request $req, Response $res)
    {
        $this->setServlet($servlet);
        $this->setResponse($res);
        $this->setRequest($req);
    }

    /**
     * Set's Servlet object
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet A servlet instance
     *
     * @return $this
     */
    protected function setServlet($servlet)
    {
        $this->servlet = $servlet;
        return $this;
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
     * Sets Request object
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $request The request instance
     *
     * @return $this
     */
    protected function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Returns Request object
     *
     * @return \TechDivision\ServletContainer\Interfaces\Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * set Response object
     *
     * @param \TechDivision\ServletContainer\Interfaces\Response $response The response instance
     *
     * @return $this
     */
    protected function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Returns Response object
     *
     * @return Response
     */
    protected function getResponse()
    {
        return $this->response;
    }
}
