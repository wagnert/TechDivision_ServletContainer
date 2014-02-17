<?php
/**
 * TechDivision\ServletContainer\Socket\AbstractHttpWorker
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */

namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractWorker;
use TechDivision\ServletContainer\Session\PersistentSessionManager;

/**
 * The worker implementation that handles the request.
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2013 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.appserver.io
 */
abstract class AbstractHttpWorker extends AbstractWorker
{

    /**
     * The maximum number of requests handled by the worker before shutdown.
     * 
     * @var integer
     */
    const HANDLE_REQUESTS = 100;

    /**
     * The main function which will be called by doing start()
     *
     * @return void
     */
    public function main()
    {
        try {
            
            // the counter with the number of requests to handle
            $handleRequests = AbstractHttpWorker::HANDLE_REQUESTS;

            // initialize the session manager itself
            $sessionManager = $this->newInstance(
                'TechDivision\ServletContainer\Session\PersistentSessionManager',
                array(
                    $this->initialContext
                )
            );
            
            // handle requests and then QUIT (to free client sockets and memory)
            $i = 0;
            while ($i ++ < $handleRequests) {
            
                // reinitialize the server socket
                $serverSocket = $this->initialContext->newInstance($this->getResourceClass(), array(
                    $this->resource
                ));
            
                // accept client connection and process the request
                if ($clientSocket = $serverSocket->accept()) {
            
                    // initialize the HTTP request/response
                    $request = $this->initialContext->newInstance('TechDivision\ServletContainer\Http\HttpRequest');
                    $response = $this->initialContext->newInstance('TechDivision\ServletContainer\Http\HttpResponse');
                    $httpPart = $this->initialContext->newInstance('TechDivision\ServletContainer\Http\HttpPart');
            
                    // inject response und session manager
                    $request->injectResponse($response);
                    $request->injectSessionManager($sessionManager);
            
                    // initialize a new HTTP client
                    $client = $this->initialContext->newInstance($this->getHttpClientClass());
                    $client->injectHttpRequest($request);
                    $client->injectHttpPart($httpPart);
                    $client->setNewLine("\r\n\r\n");
            
                    // load the client resource
                    $resource = $clientSocket->getResource();
            
                    // initialize the params for thread handling the request
                    $params = array(
                        $this->initialContext,
                        $this->container,
                        $client,
                        $resource
                    );
            
                    // process the request
                    $request = $this->initialContext->newInstance($this->threadType, $params);
                    $request->start(PTHREADS_INHERIT_ALL | PTHREADS_ALLOW_HEADERS);
                }
            }
        } catch (\Exception $e) { // catch the exception if thrown, e. g. when socket can't be accepted
            $this->getInitialContext()->getSystemLogger()->critical($e->__toString());
        }
    }
}
