<?php

namespace TechDivision\ServletContainer\Session;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TechDivision\ServletContainer\Http\Cookie;
use TechDivision\ApplicationServer\Utilities\Algorithms;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Session\Storage\StorageInterface;
use TechDivision\ServletContainer\Session\Exceptions\SessionNotStartedException;
use TechDivision\ServletContainer\Session\Exceptions\OperationNotSupportedException;
use TechDivision\ServletContainer\Session\Exceptions\DataNotSerializableException;
use TechDivision\ServletContainer\Session\Exceptions\InvalidRequestResponseException;

/**
 * A modular session implementation based on the caching framework.
 *
 * You may access the currently active session in userland code. In order to do this,
 * inject TYPO3\Flow\Session\SessionInterface and NOT just TYPO3\Flow\Session\Session.
 * The former will be a unique instance (singleton) representing the current session
 * while the latter would be a completely new session instance!
 *
 * You can use the Session Manager for accessing sessions which are not currently
 * active.
 *
 * @see \TYPO3\Flow\Session\SessionManager
 */
class ServletSession {

    const TAG_PREFIX = 'customtag-';

    /**
     * Cache storage for this session
     * @var \TechDivision\ServletContainer\Session\StorageInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $sessionCookieName;

    /**
     * @var integer
     */
    protected $sessionCookieLifetime = 0;

    /**
     * @var string
     */
    protected $sessionCookieDomain;

    /**
     * @var string
     */
    protected $sessionCookiePath;

    /**
     * @var boolean
     */
    protected $sessionCookieSecure = TRUE;

    /**
     * @var boolean
     */
    protected $sessionCookieHttpOnly = TRUE;

    /**
     * @var \TechDivision\ServletContainer\Http\Cookie
     */
    protected $sessionCookie;

    /**
     * @var integer
     */
    protected $inactivityTimeout;

    /**
     * @var integer
     */
    protected $lastActivityTimestamp;

    /**
     * @var array
     */
    protected $tags = array();

    /**
     * @var integer
     */
    protected $now;

    /**
     * @var float
     */
    protected $garbageCollectionProbability;

    /**
     * The session identifier
     *
     * @var string
     */
    protected $sessionIdentifier;

    /**
     * Internal identifier used for storing session data in the cache
     *
     * @var string
     */
    protected $storageIdentifier;

    /**
     * If this session has been started
     *
     * @var boolean
     */
    protected $started = FALSE;

    /**
     * If this session is remote or the "current" session
     *
     * @var boolean
     */
    protected $remote = FALSE;

    /**
     * @var \TechDivision\ServletContainer\Interfaces\Request
     */
    protected $request;

    /**
     * @var \TechDivision\ServletContainer\Interfaces\Response
     */
    protected $response;

    /**
     * Constructs this session
     *
     * If $sessionIdentifier is specified, this constructor will create a session
     * instance representing a remote session. In that case $storageIdentifier and
     * $lastActivityTimestamp are also required arguments.
     *
     * Session instances MUST NOT be created manually! They should be retrieved via
     * the Session Manager or through dependency injection (use SessionInterface!).
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request The request instance
     * @param \TechDivision\ServletContainer\Interfaces\Response The response instance
     * @param string $sessionIdentifier The public session identifier which is also used in the session cookie
     * @param string $storageIdentifier The private storage identifier which is used for cache entries
     * @param integer $lastActivityTimestamp Unix timestamp of the last known activity for this session
     * @param array $tags A list of tags set for this session
     * @throws \InvalidArgumentException
     */
    public function __construct(Request $request, $sessionIdentifier = NULL, $storageIdentifier = NULL, $lastActivityTimestamp = NULL, array $tags = array()) {

        $this->request = $request;
        $this->response = $request->getResponse();

        if ($sessionIdentifier !== NULL) {

            if ($storageIdentifier === NULL || $lastActivityTimestamp === NULL) {
                throw new \InvalidArgumentException('Session requires a storage identifier and last activity timestamp for remote sessions.', 1354045988);
            }

            $this->sessionIdentifier = $sessionIdentifier;
            $this->storageIdentifier = $storageIdentifier;
            $this->lastActivityTimestamp = $lastActivityTimestamp;
            $this->started = TRUE;
            $this->remote = TRUE;
            $this->tags = $tags;
        }

        $this->now = time();
    }

    /**
     * Injects the cache manager to persist session data.
     *
     * @param \TechDivision\ServletContainer\Session\Storage\StorageInterface $cache The session cache
     * @return void
     */
    public function injectCache(StorageInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * Injects the settings
     *
     * @param array $settings Settings for the session handling
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->sessionCookieName = $settings['session']['name'];
        $this->sessionCookieLifetime =  (integer)$settings['session']['cookie']['lifetime'];
        $this->sessionCookieDomain =  $settings['session']['cookie']['domain'];
        $this->sessionCookiePath =  $settings['session']['cookie']['path'];
        $this->sessionCookieSecure =  (boolean)$settings['session']['cookie']['secure'];
        $this->sessionCookieHttpOnly =  (boolean)$settings['session']['cookie']['httponly'];
        $this->garbageCollectionProbability = $settings['session']['garbageCollectionProbability'];
        $this->inactivityTimeout = (integer)$settings['session']['inactivityTimeout'];
    }

    public function setSessionIdentifier($sessionIdentifier) {
        $this->sessionIdentifier = $sessionIdentifier;
    }

    public function getSessionIdentifier() {
        return $this->sessionIdentifier;
    }

    /**
     * Tells if the session has been started already.
     *
     * @return boolean
     * @api
     */
    public function isStarted() {
        return $this->started;
    }

    /**
     * Tells if the session is local (the current session bound to the current HTTP
     * request) or remote (retrieved through the Session Manager).
     *
     * @return boolean TRUE if the session is remote, FALSE if this is the current session
     * @api
     */
    public function isRemote() {
        return $this->remote;
    }

    /**
     * Starts the session, if it has not been already started
     *
     * @return void
     * @api
     */
    public function start() {

        if ($this->request === NULL) {
            $this->initializeHttpAndCookie();
        }

        if ($this->started === FALSE) {

            $this->sessionIdentifier = Algorithms::generateRandomString(32);
            $this->storageIdentifier = Algorithms::generateUUID();
            $this->sessionCookie = new Cookie($this->sessionCookieName, $this->sessionIdentifier, $this->sessionCookieLifetime, NULL, $this->sessionCookieDomain, $this->sessionCookiePath, $this->sessionCookieSecure, $this->sessionCookieHttpOnly);

            $this->response->addCookie($this->sessionCookie);

            $this->lastActivityTimestamp = $this->now;
            $this->started = TRUE;

            $this->writeSessionInfoCacheEntry();
        }
    }

    /**
     * Returns TRUE if there is a session that can be resumed.
     *
     * If a to-be-resumed session was inactive for too long, this function will
     * trigger the expiration of that session. An expired session cannot be resumed.
     *
     * NOTE that this method does a bit more than the name implies: Because the
     * session info data needs to be loaded, this method stores this data already
     * so it doesn't have to be loaded again once the session is being used.
     *
     * @return boolean
     * @api
     */
    public function canBeResumed() {
        if ($this->request === NULL) {
            $this->initializeHttpAndCookie();
        }
        if ($this->sessionCookie === NULL || $this->request === NULL || $this->started === TRUE) {
            return FALSE;
        }
        $sessionInfo = $this->cache->get($this->sessionCookie->getValue());
        if ($sessionInfo === FALSE) {
            return FALSE;
        }
        $this->lastActivityTimestamp = $sessionInfo['lastActivityTimestamp'];
        $this->storageIdentifier = $sessionInfo['storageIdentifier'];
        $this->tags = $sessionInfo['tags'];
        return !$this->autoExpire();
    }

    /**
     * Resumes an existing session, if any.
     *
     * @return integer If a session was resumed, the inactivity of since the last request is returned
     * @api
     */
    public function resume() {

        if ($this->started === FALSE && $this->canBeResumed()) {

            $this->sessionIdentifier = $this->sessionCookie->getValue();
            $this->response->setCookie($this->sessionCookie);
            $this->started = TRUE;

            $sessionObjects = $this->cache->get($this->storageIdentifier . md5(__CLASS__));

            if (is_array($sessionObjects)) {

                foreach ($sessionObjects as $object) {

                    if (method_exists($object, '__wakeup')) {
                        $object->__wakeup();
                    }
                }

            } else {
                // Fallback for some malformed session data, if it is no array but something else.
                // In this case, we reset all session objects (graceful degradation).
                $this->cache->set($this->storageIdentifier . md5(__CLASS__), array(), array($this->storageIdentifier), 0);
            }

            $lastActivitySecondsAgo = ($this->now - $this->lastActivityTimestamp);
            $this->lastActivityTimestamp = $this->now;
            return $lastActivitySecondsAgo;
        }
    }

    /**
     * Returns the current session identifier
     *
     * @return string The current session identifier
     * @throws SessionNotStartedException
     * @api
     */
    public function getId() {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to retrieve the session identifier, but the session has not been started yet.)', 1351171517);
        }
        return $this->sessionIdentifier;
    }

    /**
     * Generates and propagates a new session ID and transfers all existing data
     * to the new session.
     *
     * @return string The new session ID
     * @throws SessionNotStartedException
     * @throws \TechDivision\ServletContainer\Exceptions\OperationNotSupportedException
     * @api
     */
    public function renewId() {

        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to renew the session identifier, but the session has not been started yet.', 1351182429);
        }

        if ($this->remote === TRUE) {
            throw new OperationNotSupportedException(sprintf('Tried to renew the session identifier on a remote session (%s).', $this->sessionIdentifier), 1354034230);
        }

        $this->removeSessionInfoCacheEntry($this->sessionIdentifier);
        $this->sessionIdentifier = Algorithms::generateRandomString(32);
        $this->writeSessionInfoCacheEntry();

        $this->sessionCookie->setValue($this->sessionIdentifier);
        return $this->sessionIdentifier;
    }

    /**
     * Returns the data associated with the given key.
     *
     * @param string $key An identifier for the content stored in the session.
     * @return mixed The contents associated with the given key
     * @throws SessionNotStartedException
     */
    public function getData($key) {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to get session data, but the session has not been started yet.', 1351162255);
        }
        return $this->cache->get($this->storageIdentifier . md5($key));
    }

    /**
     * Returns TRUE if a session data entry $key is available.
     *
     * @param string $key Entry identifier of the session data
     * @return boolean
     * @throws SessionNotStartedException
     */
    public function hasKey($key) {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to check a session data entry, but the session has not been started yet.', 1352488661);
        }
        return $this->cache->has($this->storageIdentifier . md5($key));
    }

    /**
     * Stores the given data under the given key in the session
     *
     * @param string $key The key under which the data should be stored
     * @param mixed $data The data to be stored
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\DataNotSerializableException
     * @throws SessionNotStartedException
     * @api
     */
    public function putData($key, $data) {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to create a session data entry, but the session has not been started yet.', 1351162259);
        }
        if (is_resource($data)) {
            throw new DataNotSerializableException('The given data cannot be stored in a session, because it is of type "' . gettype($data) . '".', 1351162262);
        }
        $this->cache->set($this->storageIdentifier . md5($key), $data, array($this->storageIdentifier), 0);
    }

    /**
     * Returns the unix time stamp marking the last point in time this session has
     * been in use.
     *
     * For the current (local) session, this method will always return the current
     * time. For a remote session, the unix timestamp will be returned.
     *
     * @return integer unix timestamp
     * @throws SessionNotStartedException
     * @api
     */
    public function getLastActivityTimestamp() {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to retrieve the last activity timestamp of a session which has not been started yet.', 1354290378);
        }
        return $this->lastActivityTimestamp;
    }

    /**
     * Tags this session with the given tag.
     *
     * Note that third-party libraries might also tag your session. Therefore it is
     * recommended to use namespaced tags such as "Acme-Demo-MySpecialTag".
     *
     * @param string $tag The tag – must match be a valid cache frontend tag
     * @return void
     * @throws SessionNotStartedException
     * @throws \InvalidArgumentException
     * @api
     */
    public function addTag($tag) {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to tag a session which has not been started yet.', 1355143533);
        }
        if (!$this->cache->isValidTag($tag)) {
            throw new \InvalidArgumentException(sprintf('The tag used for tagging session %s contained invalid characters. Make sure it matches this regular expression: "%s"', $this->sessionIdentifier, FrontendInterface::PATTERN_TAG));
        }
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Removes the specified tag from this session.
     *
     * @param string $tag The tag – must match be a valid cache frontend tag
     * @return void
     * @throws SessionNotStartedException
     * @api
     */
    public function removeTag($tag) {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to tag a session which has not been started yet.', 1355150140);
        }
        $index = array_search($tag, $this->tags);
        if ($index !== FALSE) {
            unset($this->tags[$index]);
        }
    }


    /**
     * Returns the tags this session has been tagged with.
     *
     * @return array The tags or an empty array if there aren't any
     * @throws SessionNotStartedException
     * @api
     */
    public function getTags() {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to retrieve tags from a session which has not been started yet.', 1355141501);
        }
        return $this->tags;
    }

    /**
     * Updates the last activity time to "now".
     *
     * @return void
     * @throws SessionNotStartedException
     */
    public function touch() {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to touch a session, but the session has not been started yet.', 1354284318);
        }

        // Only makes sense for remote sessions because the currently active session
        // will be updated on shutdown anyway:
        if ($this->remote === TRUE) {
            $this->lastActivityTimestamp = $this->now;
            $this->writeSessionInfoCacheEntry();
        }
    }

    /**
     * Explicitly writes and closes the session
     *
     * @return void
     * @api
     */
    public function close() {
        $this->shutdownObject();
    }

    /**
     * Explicitly destroys all session data
     *
     * @param string $reason A reason for destroying the session – used by the LoggingAspect
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\SessionNotStartedException
     * @api
     */
    public function destroy($reason = NULL) {
        if ($this->started !== TRUE) {
            throw new SessionNotStartedException('Tried to destroy a session which has not been started yet.', 1351162668);
        }
        if ($this->remote !== TRUE) {
            if (!$this->response->hasCookie($this->sessionCookieName)) {
                $this->response->setCookie($this->sessionCookie);
            }
            $this->sessionCookie->expire();
        }

        $this->removeSessionInfoCacheEntry($this->sessionIdentifier);
        $this->cache->flushByTag($this->storageIdentifier);
        $this->started = FALSE;
        $this->sessionIdentifier = NULL;
        $this->storageIdentifier = NULL;
        $this->tags = array();
    }

    /**
     * Iterates over all existing sessions and removes their data if the inactivity
     * timeout was reached.
     *
     * @return integer The number of outdated entries removed
     * @api
     */
    public function collectGarbage() {
        $sessionRemovalCount = 0;
        if ($this->inactivityTimeout !== 0) {
            foreach ($this->cache->getByTag('session') as $sessionInfo) {
                $lastActivitySecondsAgo = $this->now - $sessionInfo['lastActivityTimestamp'];
                if ($lastActivitySecondsAgo > $this->inactivityTimeout) {
                    $this->cache->flushByTag($sessionInfo['storageIdentifier']);
                    $sessionRemovalCount ++;
                }
            }
        }
        return $sessionRemovalCount;
    }

    /**
     * Shuts down this session
     *
     * This method must not be called manually – it is invoked by Flow's object
     * management.
     *
     * @return void
     */
    public function shutdownObject() {

        if ($this->started === TRUE && $this->remote === FALSE) {

            if ($this->cache->has($this->sessionIdentifier)) {

                /*
                // Security context can't be injected and must be retrieved manually
                // because it relies on this very session object:
                $securityContext = $this->objectManager->get('TYPO3\Flow\Security\Context');
                if ($securityContext->isInitialized()) {
                    $this->storeAuthenticatedAccountsInfo($securityContext->getAuthenticationTokens());
                }

                $this->putData('TYPO3_Flow_Object_ObjectManager', $this->objectManager->getSessionInstances());
                */

                $this->writeSessionInfoCacheEntry();
            }

            $this->started = FALSE;

            $decimals = strlen(strrchr($this->garbageCollectionProbability, '.')) - 1;
            $factor = ($decimals > -1) ? $decimals * 10 : 1;
            if (rand(0, 100 * $factor) <= ($this->garbageCollectionProbability * $factor)) {
                $this->collectGarbage();
            }
        }
    }

    /**
     * Automatically expires the session if the user has been inactive for too long.
     *
     * @return boolean TRUE if the session expired, FALSE if not
     */
    protected function autoExpire() {
        $lastActivitySecondsAgo = $this->now - $this->lastActivityTimestamp;
        $expired = FALSE;
        if ($this->inactivityTimeout !== 0 && $lastActivitySecondsAgo > $this->inactivityTimeout) {
            $this->started = TRUE;
            $this->sessionIdentifier = $this->sessionCookie->getValue();
            $this->destroy(sprintf('Session %s was inactive for %s seconds, more than the configured timeout of %s seconds.', $this->sessionIdentifier, $lastActivitySecondsAgo, $this->inactivityTimeout));
            $expired = TRUE;
        }
        return $expired;
    }

    /**
     * Initialize request, response and session cookie
     *
     * @return void
     */
    protected function initializeHttpAndCookie() {
        if ($this->request->hasCookie($this->sessionCookieName)) {
            $sessionIdentifier = $this->request->getCookie($this->sessionCookieName)->getValue();
            $this->sessionCookie = new Cookie($this->sessionCookieName, $sessionIdentifier, $this->sessionCookieLifetime, NULL, $this->sessionCookieDomain, $this->sessionCookiePath, $this->sessionCookieSecure, $this->sessionCookieHttpOnly);
        }
    }

    /**
     * Stores some information about the authenticated accounts in the session data.
     *
     * This method will check if a session has already been started, which is
     * the case after tokens relying on a session have been authenticated: the
     * UsernamePasswordToken does, for example, start a session in its authenticate()
     * method.
     *
     * Because more than one account can be authenticated at a time, this method
     * accepts an array of tokens instead of a single account.
     *
     * Note that if a session is started after tokens have been authenticated, the
     * session will NOT be tagged with authenticated accounts.
     *
     * @param array<\TYPO3\Flow\Security\Authentication\TokenInterface>
     * @return void
     */
    protected function storeAuthenticatedAccountsInfo(array $tokens) {
        $accountProviderAndIdentifierPairs = array();
        foreach ($tokens as $token) {
            $account = $token->getAccount();
            if ($token->isAuthenticated() && $account !== NULL) {
                $accountProviderAndIdentifierPairs[$account->getAuthenticationProviderName() . ':' . $account->getAccountIdentifier()] = TRUE;
            }
        }
        if ($accountProviderAndIdentifierPairs !== array()) {
            $this->putData('TYPO3_Flow_Security_Accounts', array_keys($accountProviderAndIdentifierPairs));
        }
    }

    /**
     * Writes the cache entry containing information about the session, such as the
     * last activity time and the storage identifier.
     *
     * This function does not write the whole session _data_ into the storage cache,
     * but only the "head" cache entry containing meta information.
     *
     * The session cache entry is also tagged with "session", the session identifier
     * and any custom tags of this session, prefixed with TAG_PREFIX.
     *
     * @return void
     */
    protected function writeSessionInfoCacheEntry() {
        $sessionInfo = array(
            'lastActivityTimestamp' => $this->lastActivityTimestamp,
            'storageIdentifier' => $this->storageIdentifier,
            'tags' => $this->tags
        );

        $tagsForCacheEntry = array_map(function($tag) {
            return ServletSession::TAG_PREFIX . $tag;
        }, $this->tags);
        $tagsForCacheEntry[] = 'session';
        $tagsForCacheEntry[] = $this->sessionIdentifier;

        $this->cache->set($this->sessionIdentifier, $sessionInfo, $tagsForCacheEntry, 0);
    }

    /**
     * Removes the session info cache entry for the specified session.
     *
     * Note that this function does only remove the "head" cache entry, not the
     * related data referred to by the storage identifier.
     *
     * @param string $sessionIdentifier
     * @return void
     */
    protected function removeSessionInfoCacheEntry($sessionIdentifier) {
        $this->cache->remove($sessionIdentifier);
    }
}