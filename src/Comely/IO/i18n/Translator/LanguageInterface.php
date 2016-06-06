<?php
declare(strict_types=1);

namespace Comely\IO\i18n\Translator;

/**
 * Interface LanguageInterface
 * @package Comely\IO\i18n\Translator
 */
interface LanguageInterface
{
    /**
     * Return name of language
     * @return string
     */
    public function name() : string;

    /**
     * Save a Key/Value pair as translation
     * 
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value);

    /**
     *  Retrieve a translation using String key
     * 
     * @param string $key
     * @return string|null
     */
    public function get(string $key);
}