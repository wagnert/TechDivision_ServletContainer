<?php

namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

interface SessionManager {

    /**
     * Tries to find a session for the given request. If no session
     * is found, a new one is created and assigned to the request.
     *
     * @param Request $request
     * @return ServletSession
     */
    public function getSessionForRequest(Request $request);

}