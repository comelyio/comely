<?php
declare(strict_types=1);

namespace Comely\IO\Session\Database;

use Comely\IO\Database\Schema\AbstractTable;

/**
 * Class Sessions
 * @package Comely\IO\Session\Database
 */
class Sessions extends AbstractTable
{
    const SCHEMA_TABLE  =   "comely_sessions";
    const SCHEMA_MODEL  =   null;

    /**
     * @throws \Comely\IO\Database\Exception\SchemaException
     */
    public function createTable()
    {
        $this->string("id", 64, self::STR_FIXED)->unique()->charSet("ASCII")->collation("utf8mb4_unicode_ci");
        $this->text("payload", self::TEXT_MEDIUM)->charSet("utf8mb4")->collation("utf8mb4_unicode_ci");
        $this->int("time_stamp")->unSigned();
        $this->primaryKey("id");
    }
}