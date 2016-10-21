<?php
declare(strict_types=1);

namespace Comely\IO\i18n;

/**
 * Class i18nException
 * @package Comely\IO\i18n
 */
class i18nException extends \ComelyException
{
    /** @var string */
    protected static $componentId  =   __NAMESPACE__;
}