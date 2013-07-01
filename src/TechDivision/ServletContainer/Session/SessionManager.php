<?php

namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;

interface SessionManager {

    /**
     * Tries to find a session for the given request. If no session
     * is found, a new one is created and assigned to the request.
     *
     * @param ServletRequest $request
     * @return ServletSession
     */
    public function getSessionForRequest(ServletRequest $request);

}