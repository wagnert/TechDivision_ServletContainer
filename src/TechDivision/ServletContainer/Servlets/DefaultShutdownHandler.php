<?php
/**
 * TechDivision\ServletContainer\Servlets\DefaultShutdownHandler
 *
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Servlets;

use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Interfaces\Servlet;
use TechDivision\ServletContainer\Interfaces\ShutdownHandler;
use TechDivision\ServletContainer\Interfaces\HttpClientInterface;

/**
 * Default shutdown handler implementations.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Servlets
 * @author     Johann Zelger <jz@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */
class DefaultShutdownHandler implements ShutdownHandler
{

    /**
     * The Http client that handles the request
     *
     * @var HttpClientInterface
     */
    public $client;

    /**
     * The Http response instance.
     *
     * @var \TechDivision\ServletContainer\Http\HttpResponse
     */
    public $response;

    /**
     * Constructor
     *
     * @param \TechDivision\ServletContainer\Interfaces\HttpClientInterface $client   The Http client
     * @param \TechDivision\ServletContainer\Http\HttpResponse              $response The Http response instance
     *
     * @return void
     */
    public function __construct(HttpClientInterface $client, Response $response)
    {
        $this->client = $client;
        $this->response = $response;
    }

    /**
     * It registers a shutdown function callback on the given servlet object.
     * So every servlet implementation can handle the shutdown on its own.
     *
     * @param \TechDivision\ServletContainer\Interfaces\Servlet $servlet The servlet instance
     *
     * @return void
     */
    public function register(Servlet $servlet)
    {
        ob_start();
        register_shutdown_function(array(
            &$servlet,
            "shutdown"
        ), $this->client, $this->response);
    }
}
