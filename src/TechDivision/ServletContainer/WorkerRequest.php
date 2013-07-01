<?php

/**
 * TechDivision\ServletContainer\WorkerRequest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace TechDivision\ServletContainer;

use TechDivision\Socket\HttpClient;
use TechDivision\ServletContainer\Servlets\StaticResourceServlet;
use TechDivision\ServletContainer\Http\HttpServletResponse;
use TechDivision\ServletContainer\Http\HttpServletRequest;

/**
 * The stackable implementation that handles the request.
 *
 * @package     TechDivision\ServletContainer
 * @copyright  	Copyright (c) 2010 <info@techdivision.com> - TechDivision GmbH
 * @license    	http://opensource.org/licenses/osl-3.0.php
 *              Open Software License (OSL 3.0)
 * @author      Tim Wagner <tw@techdivision.com>
 */
class WorkerRequest extends \Stackable {

    /**
     * The client socket resource.
     * @var string
     */
    public $resource;

    /**
     * Initializes the request with the client socket.
     *
     * @param resource $resource The client socket instance
     * @return void
     */
    public function __construct($resource) {
        $this->resource = $resource;
    }

    /**
     * @see \Stackable::run()
     */
    public function run() {

        if ($this->worker) {
            // initialize a new client socket
            $client = new HttpClient();

            // set the client socket resource
            $client->setResource($this->resource);

            // read a line from the client
            $request = $client->receive();

            try {

                // initialize response container
                $request->setResponse($response = new HttpServletResponse());

                // load the application to handle the request
                $application = $this->worker->findApplication($request);

                // try to locate a servlet which could service the current request
                $servlet = $application->locate($request);

                // let the servlet process the request and store the result in the response
                $servlet->service($request, $response);

            } catch (\Exception $e) {

                ob_start();

                debug_print_backtrace();

                $response->setContent(get_class($e) . "\n\n" . $e . "\n\n" . ob_get_clean());
            }

            // prepare the headers
            $headers = $this->prepareHeader($response);

            // return the string representation of the response content to the client
            $client->send($headers . "\r\n" . $response->getContent());

            // close the socket connection to the client
            $client->close();
        }
    }

    /**
     * Prepares the headers for the given response and returns them.
     *
     * @param \TechDivision\ServletContainer\Interfaces\ServletResponse $response The response to prepare the header for
     * @return string The headers
     * @todo This is a dummy implementation, headers has to be handled in request/response
     */
    public function prepareHeader(HttpServletResponse $response)
    {
        // prepare the content length
        $contentLength = strlen($response->getContent());

        // prepare the dynamic headers
        $response->addHeader("Content-Length", $contentLength);

        // return the headers
        return $response->getHeadersAsString();
    }
}