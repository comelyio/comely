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

use Comely\IO\Session\ComelySession\Bag;
use Comely\IO\Session\ComelySession\FlashMessages;
use Comely\IO\Session\ComelySession\Metadata;
use Comely\IO\Session\Exception\ComelySessionException;

/**
 * Class ComelySession
 * @package Comely\IO\Session
 */
class ComelySession implements \Serializable
{
    /** @var string */
    private $id;
    /** @var Bag */
    private $bags;
    /** @var FlashMessages */
    private $flash;
    /** @var Metadata */
    private $metadata;
    /** @var int */
    private $timeStamp;

    /**
     * ComelySession constructor.
     */
    public function __construct()
    {
        $this->id = $this->generateId();
        $this->bags = new Bag();
        $this->flash = new FlashMessages();
        $this->timeStamp = time();
    }

    /**
     * @return string
     * @throws ComelySessionException
     */
    private function generateId(): string
    {
        try {
            return bin2hex(random_bytes(32));
        } catch (\Exception $e) {
            throw new ComelySessionException('Failed to generate a random session ID');
        }
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            "id" => $this->id,
            "baggage" => serialize($this->bags),
            "flash" => serialize($this->flash),
            "metadata" => serialize($this->metadata),
            "timeStamp" => time()
        ]);
    }

    /**
     * @param string $serialized
     * @throws ComelySessionException
     */
    public function unserialize($serialized)
    {
        $unserialize = @unserialize($serialized);
        $sessionId = $unserialize["id"] ?? null;
        $timeStamp = $unserialize["timeStamp"] ?? null;

        // Session ID and timeStamp
        if (!is_string($sessionId) || !ctype_xdigit($sessionId) || !is_int($timeStamp)) {
            throw new ComelySessionException('ComelySession serialized data is incomplete or corrupted');
        }

        $this->id = $sessionId;
        $this->timeStamp = $timeStamp;

        // Baggage
        $baggage = @unserialize(strval($session["baggage"] ?? ""), [
            "allowed_classes" => ['Comely\IO\Session\ComelySession\Bag']
        ]);
        if (!$baggage instanceof Bag) {
            throw new ComelySessionException(
                sprintf('Failed to retrieve serialized baggage for session "%s"', $this->id)
            );
        }

        // Flash
        $flash = @unserialize(strval($session["flash"] ?? ""), [
            "allowed_classes" => [
                'Comely\IO\Session\ComelySession\FlashMessages',
                'Comely\IO\Session\ComelySession\FlashMessages\FlashMessage'
            ]
        ]);
        if (!$flash instanceof FlashMessages) {
            throw new ComelySessionException(
                sprintf('Failed to retrieve serialized flash messages for session "%s"', $this->id)
            );
        }

        // Metadata
        $metadata = @unserialize(strval($session["metadata"] ?? ""), [
            "allowed_classes" => ['Comely\IO\Session\ComelySession\Metadata']
        ]);
        if (!$metadata instanceof Metadata) {
            throw new ComelySessionException(
                sprintf('Failed to retrieve serialized metadata for session "%s"', $this->id)
            );
        }

        // Populate properties
        $this->bags = $baggage;
        $this->flash = $flash;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return Bag
     */
    public function bags(): Bag
    {
        return $this->bags;
    }

    /**
     * @return int
     */
    public function timeStamp(): int
    {
        return $this->timeStamp;
    }

    /**
     * @return int
     */
    public function touch(): int
    {
        return $this->timeStamp = time();
    }
}