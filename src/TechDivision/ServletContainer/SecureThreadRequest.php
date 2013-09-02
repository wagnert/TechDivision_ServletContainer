<?php

/**
 * TechDivision\ServletContainer\SecureThreadRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer;

use TechDivision\ServletContainer\ThreadRequest;

/**
 * The thread implementation that handles the secure request.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Johann Zelger <jz@techdivision.com>
 */
class SecureThreadRequest extends ThreadRequest {
    
    
    /**
     * @see AbstractThread::main()
     */
    public function main() {
        
        // initialize a new client socket
        $client = $this->newInstance('TechDivision\ServletContainer\Socket\SecureHttpClient');
        stream_socket_enable_crypto($this->resource, true, STREAM_CRYPTO_METHOD_SSLv3_SERVER);        
        $client->setNewLine("\r\n\r\n");

        // set the client socket resource
        $client->setResource($this->resource);

        // initialize the response
        $response = $this->newInstance('TechDivision\ServletContainer\Http\HttpResponse');

        try {

            /** @var HttpRequest $request */
            // receive Request Object from client
            $request = $client->receive();
            
            // inject the request with the session manager
            $sessionManager = $this->newInstance('TechDivision\ServletContainer\Session\PersistentSessionManager', array($this->initialContext));
            $request->injectSessionManager($sessionManager);

            // initialize response container
            $request->setResponse($response);

            // load the application to handle the request
            $application = $this->findApplication($request);

            // try to locate a servlet which could service the current request
            $servlet = $application->locate($request);

            $servlet->injectShutdownHandler(
                $this->newInstance(
                    'TechDivision\ServletContainer\Servlets\DefaultShutdownHandler', array($client, $response))
            );

            // let the servlet process the request and store the result in the response
            $servlet->service($request, $response);

        } catch (\Exception $e) {

            error_log($e->__toString());

            $response->setContent($e->__toString());
        }

        $this->send($client, $response);
    }
}