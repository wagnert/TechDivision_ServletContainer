<?php
/**
 * TechDivision\ServletContainer\HttpServlet
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Http\Header;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Servlets\GenericServlet;
use TechDivision\ServletContainer\Exceptions\MethodNotImplementedException;
use TechDivision\ServletContainer\Exceptions\ServletException;
use TechDivision\ServletContainer\Exceptions\IOException;

/**
 * Abstract Http servlet implementation.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Markus Stockbauer <ms@techdivision.com>
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
abstract class HttpServlet extends GenericServlet
{

    /**
     * Implements http method CONNECT
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doConnect(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method DELETE
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doDelete(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method GET
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doGet(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method HEAD
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doHead(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method OPTIONS
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doOptions(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method POST
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doPost(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method PUT
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doPut(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Implements http method TRACE
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return void
     */
    public function doTrace(Request $req, Response $res)
    {
        throw new MethodNotImplementedException(sprintf('Method %s is not implemented in this servlet.', __METHOD__));
    }

    /**
     * Delegation method for specific http methods
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request  $req The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response $res The response instance
     *
     * @throws \TechDivision\ServletContainer\Exceptions\ServletException
     * @throws \TechDivision\ServletContainer\Exceptions\IOException
     * @throws \TechDivision\ServletContainer\Exceptions\MethodNotImplementedException
     * @return mixed
     */
    public function service(Request $req, Response $res)
    {
        
        // pre-initialize response
        $res->addHeader(Header::HEADER_NAME_SERVER, $req->getServerVar('SERVER_SOFTWARE'));

        // check if servlet needs authentication and return if authentication is not provided.
        if ($this->getAuthenticationRequired() && !$this->getAuthenticationManager()->handleRequest($req, $res, $this)) {
            return;
        }
        
        // load the information about the requested path
        $pathInfo = $req->getPathInfo();
        $documentRoot = $req->getServerVar('DOCUMENT_ROOT');
        
        // create a file info object to check if a directory is requested
        $fileInfo = new \SplFileInfo($documentRoot . $pathInfo);
        
        // check if a directory/webapp was been called without ending slash
        if ($fileInfo->isDir() && strrpos($pathInfo, '/') !== strlen($pathInfo) - 1) {
            
            // redirect to path with ending slash
            $res->addHeader(Header::HEADER_NAME_LOCATION, $pathInfo . '/');
            $res->addHeader(Header::HEADER_NAME_STATUS, 'HTTP/1.1 301 OK');
            $res->setContent(PHP_EOL);
            
            return;
        }
        
        // check the request method to invoke the appropriate method
        switch ($req->getMethod()) {
            case Request::CONNECT:
                $this->doConnect($req, $res);
                break;
            case Request::DELETE:
                $this->doDelete($req, $res);
                break;
            case Request::GET:
                $this->doGet($req, $res);
                break;
            case Request::HEAD:
                $this->doHead($req, $res);
                break;
            case Request::OPTIONS:
                $this->doOptions($req, $res);
                break;
            case Request::POST:
                $this->doPost($req, $res);
                break;
            case Request::PUT:
                $this->doPut($req, $res);
                break;
            case Request::TRACE:
                $this->doTrace($req, $res);
                break;
            default:
                throw new MethodNotImplementedException(
                    sprintf('%s is not implemented yet.', $req->getMethod())
                );
        }
    }
}
