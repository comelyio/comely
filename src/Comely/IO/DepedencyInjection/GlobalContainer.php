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
    private static $instance;

    public static function getInstance() : Container
    {
        if(!isset(self::$instance)) {
            self::$instance =   new Container();
        }

        self::$instance;
    }

    private function __construct()
    {
    }
}