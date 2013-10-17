<?php
/**
 * Created by JetBrains PhpStorm.
 * User: schboog
 * Date: 07.04.13
 * Time: 00:09
 * To change this template use File | Settings | File Templates.
 */
namespace TechDivision\ServletContainer\Session;

use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;

class PersistentSessionManager implements SessionManager
{

    /**
     * Prefix for the session name.
     * 
     * @var string
     */
    const SESSION_NAME = 'PHPSESSID';

    /**
     * The initial context instance.
     * 
     * @var \TechDivision\ApplicationServer\InitialContext
     */
    protected $initialContext;

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

    /**
     * Tries to find a session for the given request.
     * The session id
     * is searched in the cookie header of the request, and in the
     * request query string. If both values are present, the value
     * in the query string takes precedence. If no session id
     * is found, a new one is created and assigned to the request.
     *
     * @param Request $request            
     * @return ServletSession
     */
    public function getSessionForRequest(Request $request)
    {
        // @todo merge refactoring for headers getter by bcmzero
        $headers = $request->getHeaders();
        $sessionId = null;
        
        // try to retrieve the session id from the cookies in request header
        if (isset($headers['Cookie'])) {
            
            foreach (explode(';', $headers['Cookie']) as $cookie) {
                
                list ($name, $value) = explode('=', $cookie);
                
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
        
        // prepare the cookie path
        if (! strstr($request->getServerVar('DOCUMENT_ROOT'), $webappName = $request->getWebappName())) {
            $cookiePath = '/';
        } else {
            $cookiePath = $webappName;
        }
        
        $settings['session']['name'] = self::SESSION_NAME;
        $settings['session']['cookie']['lifetime'] = time() + 86400;
        $settings['session']['cookie']['domain'] = $request->getServerName();
        $settings['session']['cookie']['path'] = $cookiePath;
        $settings['session']['cookie']['secure'] = false;
        $settings['session']['cookie']['httponly'] = false;
        $settings['session']['garbageCollectionProbability'] = 1;
        $settings['session']['inactivityTimeout'] = 1440;
        
        // initialize and return the session instance
        $sessionParams = array(
            $request,
            $sessionId,
            __CLASS__,
            time()
        );
        $persistentSession = $this->newInstance('TechDivision\ServletContainer\Session\ServletSession', $sessionParams);
        $persistentSession->injectSettings($settings);
        $persistentSession->injectStorage($this->initialContext->getStorage());
        return $persistentSession;
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