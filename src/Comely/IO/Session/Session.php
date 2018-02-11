<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Session;

use Comely\IO\Session\Exception\SessionException;
use Comely\IO\Session\Storage\SessionStorageInterface;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Session
 * @package Comely\IO\Session
 */
class Session implements ComponentInterface
{
    /** @var Cookies */
    private $cookies;
    /** @var SessionStorageInterface */
    private $storage;
    /** @var array */
    private $sessions;

    /**
     * Session constructor.
     * @param SessionStorageInterface $storage
     */
    public function __construct(SessionStorageInterface $storage)
    {
        $this->cookies = new Cookies();
        $this->storage = $storage;
        $this->sessions = [];

        // Register shutdown handler
        register_shutdown_function([$this, "save"]);
    }

    /**
     * @param null|string $id
     * @param null|string $cookieName
     * @return ComelySession
     */
    public function resume(?string $id = null, ?string $cookieName = null): ComelySession
    {
        $sessionId = $id;
        if (!$sessionId && $cookieName) {
            $sessionId = $_COOKIE[$cookieName];
        }

        // Has session ID?
        if ($sessionId) {
            try {
                $read = base64_decode($this->storage->read($id));

                // Todo: Decryption

                $session = @unserialize($read, [
                    "allowed_classes" => [
                        'Comely\IO\Session\ComelySession',
                        'Comely\IO\Session\ComelySession\Bag'
                    ]
                ]);

                if (!$session instanceof ComelySession) {
                    throw new SessionException(sprintf('Failed to retrieve ComelySession id "%s"', $sessionId));
                }
            } catch (SessionException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
        }

        if (!isset($session) || !$session instanceof ComelySession) {
            $session = new ComelySession(); // Create new Session
        }

        $this->resumed($session, $cookieName); // Append index and set cookie
        return $session;
    }

    /**
     * @param ComelySession $session
     * @param null|string $cookieName
     */
    private function resumed(ComelySession $session, ?string $cookieName = null): void
    {
        $this->sessions[$session->id()] = $session;
        if ($cookieName) {
            $this->cookies->set($cookieName, $session->id());
        }
    }

    /**
     * @throws \Exception
     */
    public function save(): void
    {
        /**
         * @var string $id
         * @var ComelySession $session
         */
        foreach ($this->sessions as $id => $session) {
            try {
                $encoded = base64_encode(serialize($session));

                // Todo: Encryption

                $this->storage->write($id, $encoded);
            } catch (\Exception $e) {
                trigger_error(sprintf('Failed to write session "%s", %s', $e->getMessage()), E_USER_WARNING);
                throw $e;
            }
        }
    }

    /**
     * @return Cookies
     */
    public function cookies(): Cookies
    {
        return $this->cookies;
    }
}