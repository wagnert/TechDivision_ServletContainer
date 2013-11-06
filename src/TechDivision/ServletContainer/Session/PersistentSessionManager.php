<?php

/**
 * TechDivision\ServletContainer\Session\PersistentSessionManager
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */
namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

/**
 * A session manager implementation.
 *
 * @package TechDivision\ServletContainer
 * @copyright Copyright (c) 2013 <info@techdivision.com> - TechDivision GmbH
 * @license http://opensource.org/licenses/osl-3.0.php
 *          Open Software License (OSL 3.0)
 * @author Tim Wagner <tw@techdivision.com>
 */
class PersistentSessionManager implements SessionManager
{

    /**
     * The initial context instance.
     *
     * @var \TechDivision\ApplicationServer\InitialContext
     */
    protected $initialContext;
    
    protected $sessions = array();

    /**
     * Initialize the session manager with the inital context instance.
     *
     * @param \TechDivision\ApplicationServer\InitialContext $initialContext
     *            The initial context instance
     * @return void
     */
    public function __construct($initialContext)
    {
        $this->initialContext = $initialContext;
    }
    
    public function createSession(Request $request, $sessionId, $sessionName = ServletSession::SESSION_NAME)
    {
        
        // prepare the cookie path
        $cookiePath = '/';
        
        /*
        if (strstr($request->getServerVar('DOCUMENT_ROOT'), $webappName = $request->getWebappName())) {
            $cookiePath = $webappName;
        }
        */
        
        // initialize and return the session instance
        $sessionParams = array($request, $sessionId, $sessionId, time());

        // initialize the session settings
        $settings['session']['name'] = $sessionName;
        $settings['session']['cookie']['lifetime'] = time() + 86400;
        $settings['session']['cookie']['domain'] = $request->getServerName();
        $settings['session']['cookie']['path'] = $cookiePath;
        $settings['session']['cookie']['secure'] = false;
        $settings['session']['cookie']['httponly'] = false;
        $settings['session']['garbageCollectionProbability'] = 1;
        $settings['session']['inactivityTimeout'] = 1440;
        
        // initialize and return the session instance
        $persistentSession = $this->newInstance('TechDivision\ServletContainer\Session\ServletSession', $sessionParams);
        $persistentSession->injectSettings($settings);
        $persistentSession->injectStorage($this->initialContext->getStorage());
        
        $this->sessions[$sessionName] = $persistentSession;
        
        return $persistentSession;
    }

    /**
     * Tries to find a session for the given request. The session id will be 
     * searched in the cookie header of the request, and in the request query 
     * string. If both values are present, the value in the query string takes 
     * precedence. If no session id is found, a new one is created and assigned 
     * to the request.
     *
     * @param Request $request            
     * @return ServletSession
     */
    public function getSessionForRequest(Request $request, $sessionName = ServletSession::SESSION_NAME)
    {
        
        // try to load the session with the passed name
        if (array_key_exists($sessionName, $this->sessions)) {
            return $this->sessions[$sessionName];
        }
            
        // @todo merge refactoring for headers getter by bcmzero
        $headers = $request->getHeaders();
        $sessionId = null;
        
        // try to retrieve the session id from the cookies in request header
        if (isset($headers['Cookie'])) {
            foreach (explode(';', $headers['Cookie']) as $cookie) {
                list ($name, $value) = explode('=', $cookie);
                if ($name === $sessionName) {
                    $sessionId = $value;
                }
            }
        }
        
        // try to retrieve the session id from the request query string
        // @todo merge refactoring for query string parameters getter by bcmzero
        $params = array();
        parse_str($request->getQueryString(), $params);
        if (isset($params[$sessionName])) {
            $sessionId = $params[$sessionName];
        }
        
        return $this->createSession($request, $sessionId, $sessionName);
    }

    /**
     * Returns a new instance of the passed class name.
     *
     * @param string $className
     *            The fully qualified class name to return the instance for
     * @param array $args
     *            Arguments to pass to the constructor of the instance
     * @return object The instance itself
     * @todo Has to be refactored to avoid registering autoloader on every call
     */
    public function newInstance($className, array $args = array())
    {
        return $this->initialContext->newInstance($className, $args);
    }
}