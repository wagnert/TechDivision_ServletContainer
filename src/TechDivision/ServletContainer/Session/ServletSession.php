<?php
/**
 * \TechDivision\ServletContainer\Session\ServletSession
 * PHP version 5
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Session
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 */

namespace TechDivision\ServletContainer\Session;

/*
 * This script belongs to the TYPO3 Flow framework. * * It is free software; you can redistribute it and/or modify it under * the terms of the GNU Lesser General Public License, either version 3 * of the License, or (at your option) any later version. * * The TYPO3 project - inspiring people to share! *
 */
use TechDivision\ServletContainer\Http\Cookie;
use TechDivision\ApplicationServer\Utilities\Algorithms;
use TechDivision\ApplicationServer\InitialContext\StorageInterface;
use TechDivision\ServletContainer\Interfaces\Request;
use TechDivision\ServletContainer\Interfaces\Response;
use TechDivision\ServletContainer\Session\Exceptions\SessionNotStartedException;
use TechDivision\ServletContainer\Session\Exceptions\OperationNotSupportedException;
use TechDivision\ServletContainer\Session\Exceptions\DataNotSerializableException;
use TechDivision\ServletContainer\Session\Exceptions\InvalidRequestResponseException;

/**
 * A modular session implementation based on the caching framework.
 * You may access the currently active session in userland code. In order to do this,
 * inject TYPO3\Flow\Session\SessionInterface and NOT just TYPO3\Flow\Session\Session.
 * The former will be a unique instance (singleton) representing the current session
 * while the latter would be a completely new session instance!
 * You can use the Session Manager for accessing sessions which are not currently
 * active.
 *
 * @category   Appserver
 * @package    TechDivision_ServletContainer
 * @subpackage Session
 * @author     Tim Wagner <tw@techdivision.com>
 * @copyright  2013 TechDivision GmbH <info@techdivision.com>
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link       http://www.appserver.io
 * @see        \TYPO3\Flow\Session\SessionManager
 */
class ServletSession implements \SessionHandlerInterface
{

    const TAG_PREFIX = 'customtag-';

    /**
     * Prefix for the session name.
     *
     * @var string
     */
    const SESSION_NAME = 'PHPSESSID';

    /**
     * Cache storage for this session
     *
     * @var \TechDivision\ApplicationServer\InitialContext\StorageInterface
     */
    protected $storage;

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
    protected $sessionCookieSecure = true;

    /**
     * @var boolean
     */
    protected $sessionCookieHttpOnly = true;

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
     * If this session has been started
     *
     * @var boolean
     */
    protected $started = false;

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
     * If $sessionIdentifier is specified, this constructor will create a session
     * instance representing a remote session. In that case $storageIdentifier and
     * $lastActivityTimestamp are also required arguments.
     * Session instances MUST NOT be created manually! They should be retrieved via
     * the Session Manager or through dependency injection (use SessionInterface!).
     *
     * @param \TechDivision\ServletContainer\Interfaces\Request $request               The request instance
     * @param string                                            $sessionIdentifier     The public session identifier which is also used in the session cookie
     * @param integer                                           $lastActivityTimestamp Unix timestamp of the last known activity for this session
     * @param array                                             $tags                  A list of tags set for this session
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Request $request,
        $sessionIdentifier = null,
        $lastActivityTimestamp = null,
        array $tags = array()
    ) {
        $this->request = $request;
        $this->response = $request->getResponse();

        if ($sessionIdentifier !== null) {
            $this->sessionIdentifier = $sessionIdentifier;
            $this->lastActivityTimestamp = $lastActivityTimestamp;
            $this->started = true;
            $this->tags = $tags;
        }

        $this->now = time();
    }

    /**
     * Injects the storage to persist session data.
     *
     * @param \TechDivision\ApplicationServer\InitialContext\StorageInterface $storage The session storage to use
     *
     * @return void
     */
    public function injectStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Injects the settings
     *
     * @param array $settings Settings for the session handling
     *
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->sessionCookieName = $settings['session']['name'];
        $this->sessionCookieLifetime = (integer)$settings['session']['cookie']['lifetime'];
        $this->sessionCookieDomain = $settings['session']['cookie']['domain'];
        $this->sessionCookiePath = $settings['session']['cookie']['path'];
        $this->sessionCookieSecure = (boolean)$settings['session']['cookie']['secure'];
        $this->sessionCookieHttpOnly = (boolean)$settings['session']['cookie']['httponly'];
        $this->garbageCollectionProbability = $settings['session']['garbageCollectionProbability'];
        $this->inactivityTimeout = (integer)$settings['session']['inactivityTimeout'];
    }

    /**
     * Return's the unique session identifier.
     *
     * @return string The unique session identifier
     */
    public function getSessionIdentifier()
    {
        return $this->sessionIdentifier;
    }

    /**
     * Set's the unique session identifier.
     *
     * @param string $sessionIdentifier The unique session identifier
     *
     * @return void
     */
    public function setSessionIdentifier($sessionIdentifier)
    {
        $this->sessionIdentifier = $sessionIdentifier;
    }

    /**
     * Tells if the session has been started already.
     *
     * @return boolean
     * @api
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Starts the session, if it has not been already started
     *
     * @return void
     * @api
     */
    public function start()
    {

        if ($this->started === false) {

            $this->sessionIdentifier = Algorithms::generateRandomString(32);
            $this->sessionCookie = new Cookie($this->sessionCookieName, $this->sessionIdentifier, $this->sessionCookieLifetime, null, $this->sessionCookieDomain, $this->sessionCookiePath, $this->sessionCookieSecure, $this->sessionCookieHttpOnly);
            $this->response->addCookie($this->sessionCookie);

            $this->lastActivityTimestamp = $this->now;
            $this->started = true;

            $this->writeSessionInfoCacheEntry();

        } else {
            $this->initializeHttpAndCookie();
        }
    }

    /**
     * Writes the cache entry containing information about the session, such as the
     * last activity time and the storage identifier.
     * This function does not write the whole session _data_ into the storage cache,
     * but only the "head" cache entry containing meta information.
     * The session cache entry is also tagged with "session", the session identifier
     * and any custom tags of this session, prefixed with TAG_PREFIX.
     *
     * @return void
     */
    protected function writeSessionInfoCacheEntry()
    {
        $sessionInfo = array(
            'lastActivityTimestamp' => $this->lastActivityTimestamp,
            'tags' => $this->tags
        );

        $tagsForCacheEntry = array_map(
            function ($tag) {
                return ServletSession::TAG_PREFIX . $tag;
            },
            $this->tags
        );
        $tagsForCacheEntry[] = 'session';

        $this->storage->set($this->sessionIdentifier, $sessionInfo, $tagsForCacheEntry, 0);
    }

    /**
     * Initialize request, response and session cookie
     *
     * @return void
     */
    protected function initializeHttpAndCookie()
    {
        if ($this->request->hasCookie($this->sessionCookieName)) {
            $sessionIdentifier = $this->request->getCookie($this->sessionCookieName)->getValue();
            $this->sessionCookie = new Cookie($this->sessionCookieName, $sessionIdentifier, $this->sessionCookieLifetime, null, $this->sessionCookieDomain, $this->sessionCookiePath, $this->sessionCookieSecure, $this->sessionCookieHttpOnly);
        }
    }

    /**
     * Resumes an existing session, if any.
     *
     * @return integer If a session was resumed, the inactivity of since the last request is returned
     * @api
     */
    public function resume()
    {
        if ($this->started === false && $this->canBeResumed()) {

            $this->sessionIdentifier = $this->sessionCookie->getValue();
            $this->response->setCookie($this->sessionCookie);
            $this->started = true;

            $sessionObjects = $this->storage->get($this->sessionIdentifier . md5(__CLASS__));

            if (is_array($sessionObjects)) {
                foreach ($sessionObjects as $object) {
                    if (method_exists($object, '__wakeup')) {
                        $object->__wakeup();
                    }
                }

            } else {
                $this->storage->set(
                    $this->sessionIdentifier . md5(__CLASS__),
                    array(),
                    array($this->sessionIdentifier),
                    0
                );
            }

            $lastActivitySecondsAgo = ($this->now - $this->lastActivityTimestamp);
            $this->lastActivityTimestamp = $this->now;
            return $lastActivitySecondsAgo;
        }
    }

    /**
     * Returns true if there is a session that can be resumed.
     * If a to-be-resumed session was inactive for too long, this function will
     * trigger the expiration of that session. An expired session cannot be resumed.
     * NOTE that this method does a bit more than the name implies: Because the
     * session info data needs to be loaded, this method stores this data already
     * so it doesn't have to be loaded again once the session is being used.
     *
     * @return boolean
     * @api
     */
    public function canBeResumed()
    {

        $this->initializeHttpAndCookie();

        if ($this->sessionCookie === null || $this->request === null || $this->started === true) {
            return false;
        }

        $sessionInfo = $this->storage->get($this->sessionCookie->getValue());
        if ($sessionInfo === false) {
            return false;
        }

        $this->lastActivityTimestamp = $sessionInfo['lastActivityTimestamp'];
        $this->tags = $sessionInfo['tags'];
        return !$this->autoExpire();
    }

    /**
     * Automatically expires the session if the user has been inactive for too long.
     *
     * @return boolean true if the session expired, false if not
     */
    protected function autoExpire()
    {
        $lastActivitySecondsAgo = $this->now - $this->lastActivityTimestamp;
        $expired = false;
        if ($this->inactivityTimeout !== 0 && $lastActivitySecondsAgo > $this->inactivityTimeout) {
            $this->started = true;
            $this->sessionIdentifier = $this->sessionCookie->getValue();
            $this->destroy(
                sprintf(
                    'Session %s was inactive for %s seconds, more than the configured timeout of %s seconds.',
                    $this->sessionIdentifier,
                    $lastActivitySecondsAgo,
                    $this->inactivityTimeout
                )
            );
            $expired = true;
        }
        return $expired;
    }

    /**
     * Explicitly destroys all session data
     *
     * @param string $session_id The session id hash
     *
     * @return boolean
     * @throws \TechDivision\ServletContainer\Exceptions\SessionNotStartedException
     * @see \SessionHandlerInterface::destroy()
     * @api
     */
    public function destroy($session_id)
    {

        error_log("Now destroy session with ID $session_id");

        /*
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to destroy a session which has not been started yet.', 1351162668);
        }

        if ($this->response->hasCookie($this->sessionCookieName) === false) {
            $this->response->addCookie($this->sessionCookie);
        }
        $this->sessionCookie->expire();

        $this->removeSessionInfoCacheEntry($this->sessionIdentifier);
        $this->storage->flushByTag($this->sessionIdentifier);
        $this->started = false;
        $this->sessionIdentifier = null;
        $this->tags = array();

        error_log("Successfully destroyed session with ID $session_id");
        */

        return true;
    }

    /**
     * Returns the current session identifier
     *
     * @return string The current session identifier
     * @throws SessionNotStartedException
     * @api
     */
    public function getId()
    {
        if ($this->started !== true) {
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
    public function renewId()
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to renew the session identifier, but the session has not been started yet.', 1351182429);
        }

        $this->removeSessionInfoCacheEntry($this->sessionIdentifier);
        $this->sessionIdentifier = Algorithms::generateRandomString(32);
        $this->writeSessionInfoCacheEntry();

        $this->sessionCookie->setValue($this->sessionIdentifier);
        return $this->sessionIdentifier;
    }

    /**
     * Removes the session info cache entry for the specified session.
     * Note that this function does only remove the "head" cache entry, not the
     * related data referred to by the storage identifier.
     *
     * @param string $sessionIdentifier The sessions identifier hash
     *
     * @return void
     */
    protected function removeSessionInfoCacheEntry($sessionIdentifier)
    {
        $this->storage->remove($sessionIdentifier);
    }

    /**
     * Returns the data associated with the given key.
     *
     * @param string $key An identifier for the content stored in the session.
     *
     * @return mixed The contents associated with the given key
     * @throws SessionNotStartedException
     */
    public function getData($key)
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to get session data, but the session has not been started yet.', 1351162255);
        }
        return $this->storage->get($this->sessionIdentifier . md5($key));
    }

    /**
     * Returns true if a session data entry $key is available.
     *
     * @param string $key Entry identifier of the session data
     *
     * @return boolean
     * @throws SessionNotStartedException
     */
    public function hasKey($key)
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to check a session data entry, but the session has not been started yet.', 1352488661);
        }
        return $this->storage->has($this->sessionIdentifier . md5($key));
    }

    /**
     * Stores the given data under the given key in the session
     *
     * @param string $key  The key under which the data should be stored
     * @param mixed  $data The data to be stored
     *
     * @return void
     * @throws \TechDivision\ServletContainer\Exceptions\DataNotSerializableException
     * @throws SessionNotStartedException
     * @api
     */
    public function putData($key, $data)
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to create a session data entry, but the session has not been started yet.', 1351162259);
        }
        if (is_resource($data)) {
            throw new DataNotSerializableException('The given data cannot be stored in a session, because it is of type "' . gettype(
                $data
            ) . '".', 1351162262);
        }
        $this->storage->set(
            $this->sessionIdentifier . md5($key),
            $data,
            array(
                $this->sessionIdentifier
            ),
            0
        );
    }

    /**
     * Returns the unix time stamp marking the last point in time this session has
     * been in use.
     * For the current (local) session, this method will always return the current
     * time. For a remote session, the unix timestamp will be returned.
     *
     * @return integer unix timestamp
     * @throws SessionNotStartedException @api
     */
    public function getLastActivityTimestamp()
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to retrieve the last activity timestamp of a session which has not been started yet.', 1354290378);
        }
        return $this->lastActivityTimestamp;
    }

    /**
     * Tags this session with the given tag.
     * Note that third-party libraries might also tag your session. Therefore it is
     * recommended to use namespaced tags such as "Acme-Demo-MySpecialTag".
     *
     * @param string $tag The tag – must match be a valid cache frontend tag
     *
     * @return void
     * @throws SessionNotStartedException
     * @throws \InvalidArgumentException @api
     */
    public function addTag($tag)
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to tag a session which has not been started yet.', 1355143533);
        }
        if (!$this->storage->isValidTag($tag)) {
            throw new \InvalidArgumentException(sprintf(
                'The tag used for tagging session %s contained invalid characters. Make sure it matches this regular expression: "%s"',
                $this->sessionIdentifier,
                FrontendInterface::PATTERN_TAG
            ));
        }
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
    }

    /**
     * Removes the specified tag from this session.
     *
     * @param string $tag The tag – must match be a valid cache frontend tag
     *
     * @return void
     * @throws SessionNotStartedException @api
     */
    public function removeTag($tag)
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to tag a session which has not been started yet.', 1355150140);
        }
        $index = array_search($tag, $this->tags);
        if ($index !== false) {
            unset($this->tags[$index]);
        }
    }

    /**
     * Returns the tags this session has been tagged with.
     *
     * @return array The tags or an empty array if there aren't any
     * @throws SessionNotStartedException @api
     */
    public function getTags()
    {
        if ($this->started !== true) {
            throw new SessionNotStartedException('Tried to retrieve tags from a session which has not been started yet.', 1355141501);
        }
        return $this->tags;
    }

    /**
     * Explicitly writes and closes the session
     *
     * @return boolean
     * @see \SessionHandlerInterface::close()
     * @api
     */
    public function close()
    {
        $this->shutdownObject();
        return true;
    }

    /**
     * Shuts down this session
     * This method must not be called manually – it is invoked by Flow's object
     * management.
     *
     * @return void
     */
    public function shutdownObject()
    {
        if ($this->started === true) {
            if ($this->storage->has($this->sessionIdentifier)) {
                $this->writeSessionInfoCacheEntry();
            }
            $this->started = false;
            $decimals = strlen(strrchr($this->garbageCollectionProbability, '.')) - 1;
            $factor = ($decimals > -1) ? $decimals * 10 : 1;
            if (rand(0, 100 * $factor) <= ($this->garbageCollectionProbability * $factor)) {
                $this->gc();
            }
        }
    }

    /**
     * Iterates over all existing sessions and removes their data if the inactivity
     * timeout was reached.
     *
     * @param string $maxlifetime The max lifetime
     *
     * @return boolean
     * @see \SessionHandlerInterface::gc()
     * @api
     */
    public function gc($maxlifetime)
    {

        error_log("Collection garbage for maximum lifetime $maxlifetime");

        /*
        $sessionRemovalCount = 0;
        if ($this->inactivityTimeout !== 0) {
            foreach ($this->storage->getByTag('session') as $sessionInfo) {
                $lastActivitySecondsAgo = $this->now - $sessionInfo['lastActivityTimestamp'];
                if ($lastActivitySecondsAgo > $this->inactivityTimeout) {
                    $this->storage->flushByTag($this->sessionIdentifier);
                    $sessionRemovalCount ++;
                }
            }
        }
        */

        error_log("Successfully flushed $sessionRemovalCount sessions");

        return true;
    }

    /**
     * Open's a new session by given save path and name
     *
     * @param string $save_path The path where session data save to
     * @param string $name      The session's name
     *
     * @return boolean
     * @see \SessionHandlerInterface::open()
     * @api
     */
    public function open($save_path, $name)
    {
        error_log("Now open session with save path $save_path and name $name");
    }

    /**
     * Read's data by given session id
     *
     * @param string $session_id The session's identifier
     *
     * @return string
     * @see \SessionHandlerInterface::read()
     * @api
     */
    public function read($session_id)
    {

        error_log("Now try to read from session with id $session_id");

        $data = $this->storage->get($session_id);

        error_log(var_export($data, true));

        return (string)$data;
    }

    /**
     * Writes data by given session id
     *
     * @param string $session_id   The session id
     * @param string $session_data The data to write
     *
     * @return boolean
     * @see \SessionHandlerInterface::write()
     * @api
     */
    public function write($session_id, $session_data)
    {
        error_log("Now try to write session data for session id $session_id");
        error_log(var_export($session_data, true));

        $this->storage->set($session_id, $session_data);

        return true;
    }
}
