<?php

/**
 * TechDivision\ServletContainer\Socket\AbstractHttpWorker
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category  Appserver
 * @package   TechDivision_ServletContainer
 * @author    Johann Zelger <jz@techdivision.com>
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
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
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
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
    const HANDLE_REQUESTS = 20;

    /**
     * The main function which will be called by doing start()
     *
     * @return void
     */
    public function main()
    {
        
        try {
            
            // prepare server and path information
            $path = $this->getContainer()->getBaseDirectory(DIRECTORY_SEPARATOR . 'bin') . PATH_SEPARATOR . getenv('PATH');
            $serverSoftware = $this->getContainer()->getContainerNode()->getHost()->getServerSoftware();
            $serverAdmin = $this->getContainer()->getContainerNode()->getHost()->getServerAdmin();
            
            // the counter with the number of requests to handle
            $handleRequests = AbstractHttpWorker::HANDLE_REQUESTS;
            
            // declare the arrays with the preinitialized clients, requests + client sockets
            $clients = array();
            $requests = array();
            $clientSockets = array();
            
            // preinitialize the clients
            while ($z++ < $handleRequests) {
                
                 // initialize the Http request
                $request = $this->initialContext->newInstance('TechDivision\ServletContainer\Http\HttpRequest');
                
                // set server and path information
                $request->setServerVar('PATH', $path);
                $request->setServerVar('SERVER_SOFTWARE', $serverSoftware);
                $request->setServerVar('SERVER_ADMIN', $serverAdmin);
                
                // initialize the Http response/part
                $response = $this->initialContext->newInstance('TechDivision\ServletContainer\Http\HttpResponse');
                $httpPart = $this->initialContext->newInstance('TechDivision\ServletContainer\Http\HttpPart');
                
                // inject response und session manager
                $request->injectResponse($response);
                
                // initialize a new Http client
                $client = $this->initialContext->newInstance($this->getHttpClientClass());
                $client->injectHttpRequest($request);
                $client->injectHttpPart($httpPart);
                $client->setNewLine("\r\n\r\n");
                
                // add the initialize client to the array
                $clients[$z] = $client;
            }
            
            // handle requests and then QUIT (to free client sockets and memory)
            $i = 0;
            while ($i++ < $handleRequests) {
            
                // reinitialize the server socket
                $serverSocket = $this->initialContext->newInstance(
                    $this->getResourceClass(),
                    array(
                        $this->resource
                    )
                );
            
                // accept client connection and process the request
                if ($clientSockets[$i] = $serverSocket->accept()) {
            
                    // initialize the request
                    $requests[$i] = $this->initialContext->newInstance(
                        $this->threadType,
                        array(
                            $this->initialContext,
                            $this->container,
                            $clients[$i],
                            $clientSockets[$i]->getResource()
                        )
                    );
                    
                    // process the request itself
                    $requests[$i]->start(PTHREADS_INHERIT_ALL | PTHREADS_ALLOW_HEADERS);
                }
            }
            
            // wait till all requests has been finished
            foreach ($requests as $request) {
                $request->join();
            }
            
            // log a message that this worker has been closed successfully
            $this->getInitialContext()->getSystemLogger()->debug(sprintf('Now closing worker %s', $this->getThreadId()));
            
        } catch (\Exception $e) { // catch the exception if thrown, e. g. when socket can't be accepted
            $this->getInitialContext()->getSystemLogger()->critical($e->__toString());
        }
    }
}
