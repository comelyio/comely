<?php
declare(strict_types=1);

namespace Comely\IO\DependencyInjection;

/**
 * Class GlobalContainer
 * Singleton container for global access
 *
 * @package Comely\IO\DependencyInjection
 */
class GlobalContainer
{
    /** @var Container */
    private static $instance;

    /**
     * @return Container
     */
    public static function getInstance() : Container
    {
        if(!isset(self::$instance)) {
            self::$instance =   new Container();
        }

        return self::$instance;
    }

    /**
     * GlobalContainer constructor.
     */
    private function __construct()
    {
    }
}