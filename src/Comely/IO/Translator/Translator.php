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

namespace Comely\IO\Translator;

use Comely\IO\FileSystem\Disk\Directory;
use Comely\Kernel\Extend\ComponentInterface;

/**
 * Class Translator
 * @package Comely\IO\Translator
 */
class Translator implements ComponentInterface
{
    /** @var self */
    private static $instance;

    /** @var Directory */
    private $directory;
    /** @var array */
    private $langs;


    private $cache;

    private $language;
    private $fallback;

    /**
     * @return Translator
     */
    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new self();
        return self::$instance;
    }

    /**
     * Translations constructor.
     */
    private function __construct()
    {
        $this->langs = [];
    }

    /**
     * @param Directory $dir
     * @return Translator
     */
    public function directory(Directory $dir): self
    {
        $this->directory = $dir;
        return $this;
    }

    public function language(string $lang): self
    {
        return $this;
    }

    public function fallback(string $lang): self
    {
        return $this;
    }

    public function files()
    {

    }

    public function translate(string $key, ?string $lang = null): ?string
    {

    }
}