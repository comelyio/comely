<?php
declare(strict_types=1);

namespace Comely\IO\Security\Forms;

/**
 * Interface SecureFormsInterface
 * @package Comely\IO\Security\Forms
 */
interface SecureFormsInterface
{
    /**
     * @return string
     */
    public function getHash() : string;

    /**
     * @return array
     */
    public function getArray() : array;
}