<?php
declare(strict_types=1);

namespace Comely\IO\Session\Storage;

use Comely\IO\Database\Schema\AbstractTable;
use Comely\IO\Session\Exception\StorageException;

/**
 * Class Sessions
 * @package Comely\IO\Session\Database
 */
class Database extends AbstractTable implements StorageInterface
{
    const SCHEMA_TABLE  =   "comely_sessions";
    const SCHEMA_MODEL  =   null;

    /**
     * @throws \Comely\IO\Database\Exception\SchemaException
     */
    public function createTable()
    {
        $this->string("id", 64, self::STR_FIXED)->unique()
            ->charSet("ascii")
            ->collation("ascii_general_ci");
        $this->text("payload", self::TEXT_MEDIUM)
            ->charSet("utf8mb4")
            ->collation("utf8mb4_unicode_ci");
        $this->int("time_stamp")->unSigned();
        $this->primaryKey("id");
    }

    /**
     * @param string $id
     * @return string
     * @throws StorageException
     */
    public function read(string $id) : string
    {
        $read   =   call_user_func([$this, "findById"], $id);
        if(!is_array($read) || !array_key_exists("payload", $read) || empty($read["payload"])) {
            throw StorageException::readError(__METHOD__, 'Not found');
        }

        return $read["payload"];
    }

    /**
     * @param string $id
     * @param string $payload
     * @return int
     * @throws StorageException
     */
    public function write(string $id, string $payload) : int
    {
        $bytes  =   strlen($payload);
        $update =   $this->db->table(self::SCHEMA_TABLE)
            ->find("id=:id", ["id" => $id])
            ->update(
                [
                    "payload"   =>  $payload,
                    "time"  =>  time()
                ]
            );

        if($update  !== 1   ||  $this->db->lastQuery->error !== null) {
            throw StorageException::writeError(__METHOD__, $this->db->lastQuery->error ?? 'Failed');
        }

        return $bytes;
    }

    /**
     * @param string $id
     * @return bool
     * @throws StorageException
     */
    public function delete(string $id) : bool
    {
        $delete =   $this->db->table(self::SCHEMA_TABLE)
            ->find("id=?", [$id])
            ->delete();

        if($delete  !== 1   ||  $this->db->lastQuery->error !== null) {
            throw StorageException::deleteError(__METHOD__, $this->db->lastQuery->error ?? 'Failed');
        }

        return true;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id) : bool
    {
        $exists =   $this->db->table(self::SCHEMA_TABLE)
            ->find("id=?", [$id])
            ->select("id")
            ->fetchFirst();

        return is_array($exists);
    }

    /**
     * @return bool
     * @throws StorageException
     */
    public function flush() : bool
    {
        $flush  =   $this->db->query(
            sprintf('DELETE FROM `%1$s`', self::SCHEMA_TABLE),
            [],
            \Comely\IO\Database\Database::QUERY_EXEC
        );

        if(!$flush  ||  !$this->db->lastQuery->rows) {
            $error  =   $this->db->lastQuery->error ?? sprintf('Flushed %d files', $this->db->lastQuery->rows);
            throw StorageException::flushError(__METHOD__, $error);
        }

        return true;
    }
}