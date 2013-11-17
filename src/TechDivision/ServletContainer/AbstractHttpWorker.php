<?php

/**
 * TechDivision\ServletContainer\Socket\AbstractHttpWorker
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer;

use TechDivision\ApplicationServer\AbstractWorker;

/**
 * The worker implementation that handles the request.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Johann Zelger <jz@techdivision.com>
 */
abstract class AbstractHttpWorker extends AbstractWorker
{
    
    /**
     * (non-PHPdoc)
     * 
     * @see \TechDivision\ApplicationServer\AbstractWorker::main()
     * @see \Thread::run()
     */
    public function main()
    {

        // the counter with the number of requests to handle
        $handleRequests = 100;
        
        // initialize the array with the clients to handle the requests
        $clients = array();
        
        // initialize the session manager itself
        $sessionManager = $this->newInstance('TechDivision\ServletContainer\Session\PersistentSessionManager', array(
            $this->initialContext
        ));
        
        // initialize the array with the preinitialized clients
        for ($i = 0; $i < $handleRequests; $i++) {

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
            
            // add the client to the array
            $clients[$i] = $client;
        }
        
        // handle requests and then QUIT (to free client sockets and memory)
        $i = 0;
        while ($i++ < $handleRequests) {
            
            // reinitialize the server socket
            $serverSocket = $this->initialContext->newInstance($this->getResourceClass(), array(
                $this->resource
            ));
            
            // accept client connection and process the request
            if ($clientSocket = $serverSocket->accept()) {
                
                // load the client resource
                $resource = $clientSocket->getResource();
                
                // initialize the params for thread handling the request
                $params = array(
                    $this->initialContext,
                    $this->container,
                    $clients[$i],
                    $resource
                );
                
                // process the request
                $request = $this->initialContext->newInstance($this->threadType, $params);
                $request->start();
            }
        }
    }
}