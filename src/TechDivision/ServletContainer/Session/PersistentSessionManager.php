<?php
/**
 * Created by JetBrains PhpStorm.
 * User: schboog
 * Date: 07.04.13
 * Time: 00:09
 * To change this template use File | Settings | File Templates.
 */

namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Interfaces\ServletRequest;
use TechDivision\ServletContainer\Interfaces\ServletResponse;
use TechDivision\ServletContainer\Http\HttpServletRequest;
use TechDivision\ServletContainer\Session\Storage\MemcachedStorage;

class PersistentSessionManager implements SessionManager {

    /**
     * Prefix for the session name.
     * @var string
     */
    const SESSION_NAME = 'PHPSESSID';

    /**
     * Tries to find a session for the given request. The session id
     * is searched in the cookie header of the request, and in the
     * request query string. If both values are present, the value
     * in the query string takes precedence. If no session id
     * is found, a new one is created and assigned to the request.
     *
     * @param ServletRequest $request
     * @return ServletSession
     */
    public function getSessionForRequest(ServletRequest $request)
    {
        /** @var $request HttpServletRequest */
        // @todo merge refactoring for headers getter by bcmzero
        $headers = $request->getHeaders();
        $sessionId = null;

        // try to retrieve the session id from the cookies in request header
        if (isset($headers['cookie'])) {

            foreach (explode(';', $headers['cookie']) as $cookie) {

                list($name, $value) = explode('=', $cookie);

                if ($name === self::SESSION_NAME) {
                    $sessionId = $value;
                }
            }
        }

        // try to retrieve the session id from the request query string
        // @todo merge refactoring for query string parameters getter by bcmzero
        $params = array();

        parse_str($request->getQueryString(), $params);

        if (isset($params[self::SESSION_NAME])) {
            $sessionId = $params[self::SESSION_NAME];
        }

        /*
        // initialize a new session if none is present yet
        if ($sessionId == null) {
            // @todo make session id really unique over all requests
            $sessionId = uniqid(self::SESSION_NAME);
        }
        */

        $settings['session']['name'] = self::SESSION_NAME;
        $settings['session']['cookie']['lifetime'] = time() + 86400;
        $settings['session']['cookie']['domain'] = null;
        $settings['session']['cookie']['path'] = null;
        $settings['session']['cookie']['secure'] = false;
        $settings['session']['cookie']['httponly'] = false;
        $settings['session']['garbageCollectionProbability'] = 1;
        $settings['session']['inactivityTimeout'] = 1440;

        $persistentSession = new ServletSession($request, $sessionId, __CLASS__, time());
        $persistentSession->injectSettings($settings);
        $persistentSession->injectCache(new MemcachedStorage());

        /*
        // register the session id with php's standard logic
        session_id($sessionId);

        $connection = Factory::createContextConnection();

        $session = $connection->createContextSession();
        $context = $session->createInitialContext();


        $persistentSession = $context->lookup('\TechDivision\ServletContainer\Session\ServletSession');
        $persistentSession->setSessionId($sessionId);
        */

        return $persistentSession;
    }
}