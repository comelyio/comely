<?php
/**
 * This file is part of Comely IO package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\Filesystem;

use Comely\IO\Filesystem\Exception\DiskException;

/**
 * Class Disk
 * Get an instance of this class to work in specified directory
 *
 * @package Comely\IO\Filesystem
 */
class Disk
{
    const WRITE_APPEND  =   1;
    const WRITE_FLOCK  =   2;
    const OVERRIDE_TARGET = 4;

    /** @var string */
    private $path;
    /** @var bool */
    private $privilegeRead;
    /** @var bool */
    private $privilegeWrite;

    /**
     * Disk constructor.
     * @param string $path
     * @throws DiskException
     */
    public function __construct(string $path = ".")
    {
        // Check if provided path is local stream
        if(!stream_is_local($path)) {
            throw DiskException::diskInit("Path to disk must be a local directory");
        }

        // Check if path is a symbolic link
        if(@is_link($path)  === true) {
            throw DiskException::diskInit("Path to disk cannot be a symbolic link");
        }

        // Resolve path
        $realPath   =   @realpath($path);

        // Check if path couldn't be resolved
        if(!$realPath) {
            // Create directory
            $this->createDir($path, 0777);
            $realPath   =   @realpath($path);
        }

        // Set resolved path
        $path   =   $realPath;

        // Confirm if path leads to a directory
        if(!$path   ||  !@is_dir($path)) {
            throw DiskException::diskInit("Disk must be provided with path to a directory");
        }

        // Set path variable for this instance
        // Disk path must have a trailing DIRECTORY_SEPARATOR
        $this->path  =    rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // Check and set privileges
        $this->privilegeRead    =   (@is_readable($this->path)) ? true : false;
        $this->privilegeWrite    =   (@is_writable($this->path)) ? true : false;

        if(!$this->privilegeRead    &&  !$this->privilegeWrite) {
            // Doesn't have both read/write privileges
            throw DiskException::diskInit("Disk doesn't have read and write privileges");
        }
    }

    /**
     * Get path of Disk with trailing DIRECTORY_SEPARATOR
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Get privileges of current Disk instance
     * @return string
     */
    public function diskPrivileges() : string
    {
        $privileges =   "";
        if($this->privilegeRead === true) $privileges   .=  "r";
        if($this->privilegeWrite === true) $privileges   .=  "w";

        return $privileges;
    }

    /**
     * Read a file from Disk
     *
     * @param string $fileName
     * @return string
     * @throws DiskException
     */
    public function read(string $fileName) : string
    {
        // Validate and build filePath
        $filePath   =   $this->validatePath($fileName, __METHOD__);
        $this->checkReadableFile($fileName, $filePath);

        // Read file
        $contents   =   @file_get_contents($filePath);
        if(!$contents) {
            $this->throwError(
                sprintf(
                    'Reading failed for file "%1$s" in "%2$s"',
                    basename($filePath),
                    dirname($filePath) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        }

        return $contents;
    }

    /**
     * Gets last modified timestamp for file
     * 
     * @param string $fileName
     * @return int
     */
    public function fileLastModified(string $fileName) : int
    {
        // Validate and build filePath
        $filePath   =   $this->validatePath($fileName, __METHOD__);
        $this->checkReadableFile($fileName, $filePath);

        $mTime  =   @filemtime($filePath);
        if(!$mTime) {
            $this->throwError(
                sprintf(
                    'Failed to retrieve last modification time for file "%1$s" in "%2$s"',
                    basename($filePath),
                    dirname($filePath) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        }

        return $mTime;
    }

    /**
     * Write a file to Disk
     *
     * @param string $filename
     * @param string $write
     * @param int $flag
     * @return int number of bytes written
     * @throws DiskException
     */
    public function write(string $filename, string $write, int $flag = 0) : int
    {
        // Check writing privilege of Disk
        if(!$this->privilegeWrite) {
            throw DiskException::writeError(__METHOD__);
        }

        // Validate and build filePath
        $filePath   =   $this->validatePath($filename, __METHOD__);

        // Already exists?
        if(@file_exists($filePath)) {
            // Make sure its a file and writable
            if(!$this->hasFile($filename)    ||  !$this->isWritable($filename)) {
                $this->throwError(
                    sprintf(
                        'File "%1$s" in "%2$s" exists but isn\' writable as file',
                        basename($filePath),
                        dirname($filePath) . DIRECTORY_SEPARATOR
                    ),
                    __METHOD__
                );
            }
        }

        // Writing flag
        $writeFlag  =   0;
        if($flag    === self::WRITE_APPEND) {
            $writeFlag  =   FILE_APPEND;
        } elseif($flag  === self::WRITE_FLOCK) {
            $writeFlag  =   LOCK_EX;
        }

        // Write file
        $written    =   file_put_contents($filePath, $write, $writeFlag);
        if($written === false   ||  !is_int($written)) {
            // Writing failed
            $this->throwError(
                sprintf(
                    'Failed to write "%1$s" in "%2$s"',
                    basename($filePath),
                    dirname($filePath) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        }

        return $written;
    }

    /**
     * Create director(y|ies) within Disk
     *
     * @param string $dirs
     * @param int $permissions
     * @throws DiskException
     */
    public function createDir(string $dirs, int $permissions = 0777)
    {
        // Check if disk path is set
        if(isset($this->path)) {
            // Not being called from constructor
            $dirs   =   $this->validatePath($dirs, __METHOD__);
        }

        // Create directories recursively
        if(@mkdir($dirs, $permissions, true)    !== true) {
            $this->throwError(sprintf('Failed to create "%s"', $dirs), __METHOD__);
        }
    }

    /**
     * Perform glob() within Disk
     *
     * @param string $pattern
     * @param int $flags
     * @return array
     * @throws DiskException
     */
    public function find(string $pattern, int $flags = 0) : array
    {
        // Check reading privilege
        if(!$this->privilegeRead) {
            throw DiskException::readError(__METHOD__);
        }

        // Validate path with glob wildcard (*) pattern
        $pattern    =   $this->validatePath($pattern, __METHOD__, "*");

        // Call glob() on validated pattern
        $glob   =   glob($pattern, $flags);
        if(is_array($glob)) {
            // Remove Disk path prefix from all results
            $pathLen    =   strlen($this->path);
            $glob   =   array_map(function($path) use($pathLen) {
                return substr($path, $pathLen);
            }, $glob);

            return $glob;
        } else {
            // Return an empty Array
            return [];
        }
    }

    /**
     * Perform chmod() within Disk
     *
     * @param string $file
     * @param int $permissions
     * @return bool
     * @throws DiskException
     */
    public function chmod(string $file, int $permissions = 0755) : bool
    {
        // Get validated path
        $path   =   $this->validatePath($file, __METHOD__);

        // Validate permissions argument
        if(!preg_match("/^0[0-9]{3}$/", (string) $permissions)) {
            $this->throwError(
                "Permissions argument must be a 4 digit octal number (starting with 0)",
                __METHOD__
            );
        }

        return chmod($path, $permissions);
    }

    /**
     * Check if a file exists within Disk
     *
     * @param string $filename
     * @return bool
     * @throws DiskException
     */
    public function hasFile(string $filename) : bool
    {
        // Check reading privilege of Disk
        if(!$this->privilegeRead) {
            throw DiskException::readError(__METHOD__);
        }

        // Check is_file()
        $filePath   =   $this->validatePath($filename, __METHOD__);
        return @is_file($filePath) ? true : false;
    }

    /**
     * Check if a directory exists within Disk
     *
     * @param string $dir
     * @return bool
     * @throws DiskException
     */
    public function hasDir(string $dir) : bool
    {
        // Check reading privilege of Disk
        if(!$this->privilegeRead) {
            throw DiskException::readError(__METHOD__);
        }

        // Check is_dir()
        $dirPath    =   $this->validatePath($dir, __METHOD__);
        return @is_dir($dirPath) ? true : false;
    }

    /**
     * Check if file/directory from Disk exists and is readable
     *
     * @param string $path
     * @return bool
     * @throws DiskException
     */
    public function isReadable(string $path) : bool
    {
        // Check reading privilege of Disk
        if(!$this->privilegeRead) {
            throw DiskException::readError(__METHOD__);
        }

        // Check is_readable()
        $readPath   =   $this->validatePath($path, __METHOD__);
        return @is_readable($readPath) ? true : false;
    }

    /**
     * Check if file/directory from Disk exists and is writable
     *
     * @param string $path
     * @return bool
     * @throws DiskException
     */
    public function isWritable(string $path) : bool
    {
        // Check writing privilege of Disk
        if(!$this->privilegeWrite) {
            throw DiskException::writeError(__METHOD__);
        }

        // Check is_writable()
        $writePath   =   $this->validatePath($path, __METHOD__);
        return @is_writable($writePath) ? true : false;
    }

    /**
     * Delete a file or directory within Disk
     *
     * @param string $path
     * @throws DiskException
     */
    public function delete(string $path)
    {
        // Check writing privilege of Disk
        if(!$this->privilegeWrite) {
            throw DiskException::writeError(__METHOD__);
        }

        // Validate deleting path
        $path   =   $this->validatePath($path, __METHOD__);

        // Check if file/directory exists
        if(!@file_exists($path)) {
            $this->throwError(
                sprintf(
                    'Cannot find "%1$s" in "%2$s" for deleting',
                    basename($path),
                    dirname($path) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        }

        if(@is_file($path)) {
            // Delete a regular file
            $delete =   @unlink($path);
            if(!$delete) {
                // Failed to delete regular file
                $this->throwError(
                    sprintf(
                        'Failed to delete file "%1$s" in "%2$s"',
                        basename($path),
                        dirname($path) . DIRECTORY_SEPARATOR
                    ),
                    __METHOD__
                );
            }
        } elseif(@is_dir($path)) {
            // We cannot delete a non-empty directory
            // Let's scan directory and delete all its contents
            $dirContents    =   @scandir($path);
            if(!is_array($dirContents)) {
                $this->throwError(sprintf('Failed to scan "%1$s" directory', $path), __METHOD__);
            }

            // Iterate through directory contents
            foreach($dirContents as $dirContent) {
                if(in_array($dirContent, [".",".."])) {
                    continue; // Skip dots
                }

                // Delete file or directory
                $this->delete(substr($path, strlen($this->path)) . DIRECTORY_SEPARATOR . $dirContent);
            }

            // Delete
            $delete =   @rmdir($path);
            if(!$delete) {
                $this->throwError(sprintf('Failed to delete "%1$s" directory', $path), __METHOD__);
            }
        }
    }

    /**
     * Move a uploaded file from $_FILES to Disk
     * Param must point to a single file upload in $_FILES
     * This method will check for "UPLOAD_ERR_OK" before proceeding
     * This method will check "tmp_name" of uploaded file with is_uploaded_file()
     *
     * @param string $param
     * @param string $destination
     * @throws DiskException
     */
    public function uploadFile(string $param, string $destination)
    {
        // Check writing privilege of Disk
        if(!$this->privilegeWrite) {
            throw DiskException::writeError(__METHOD__);
        }

        // Find uploaded file with param
        if(!array_key_exists($param, $_FILES)) {
            $this->throwError(sprintf('Parameter "$1$s" not found in $_FILES', $param), __METHOD__);
        }

        // Check if file upload was successful
        if(!is_int($_FILES[$param]["error"])    ||  $_FILES[$param]["error"] !== UPLOAD_ERR_OK) {
            // There was an error with upload
            $this->throwError(
                sprintf(
                    'There was an error [%2$d] with "$1$s" file upload',
                    $param,
                    $_FILES[$param]["error"]
                ),
                __METHOD__
            );
        }

        // Check integrity of temp. file name
        if(
            !is_string($_FILES[$param]["tmp_name"])  ||
            !@file_exists($_FILES[$param]["tmp_name"])   ||
            !@is_uploaded_file($_FILES[$param]["tmp_name"])
        ) {
            $this->throwError(sprintf('Uploaded file couldn\'t be found', $param), __METHOD__);
        }

        // Validate destination path
        $destination    =   $this->validatePath($destination, __METHOD__);

        // Move uploaded file
        $move   =   @move_uploaded_file($_FILES[$param]["tmp_name"], $destination);
        if(!$move) {
            $this->throwError(
                sprintf(
                    'Failed to move uploaded file from "$1$s" to "%2$s" in "%3$s"',
                    $_FILES[$param]["tmp_name"],
                    basename($destination),
                    dirname($destination) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        }
    }

    /**
     * Copy a file within Disk scope
     *
     * @param string $from
     * @param string $to
     * @param int $flag
     * @return bool
     * @throws DiskException
     */
    public function copy(string $from, string $to, int $flag = 0) : bool
    {
        // Prepare copy
        $this->prepareCopy(__METHOD__, $from, $to, $flag);

        // Validate Paths
        $fromPath   =   $this->validatePath($from, __METHOD__);
        $toPath   =   $this->validatePath($to, __METHOD__);

        // Copy
        return copy($fromPath, $toPath);
    }

    /**
     * Move a file within Disk scope
     *
     * @param string $from
     * @param string $to
     * @param int $flag
     * @return bool
     * @throws DiskException
     */
    public function move(string $from, string $to, int $flag = 0) : bool
    {
        // Prepare move
        $this->prepareCopy(__METHOD__, $from, $to, $flag);

        // Validate Paths
        $fromPath   =   $this->validatePath($from, __METHOD__);
        $toPath   =   $this->validatePath($to, __METHOD__);

        // Move
        return rename($fromPath, $toPath);
    }

    /**
     * Check files integrity
     * hasFile() and isReadable() methods will also check reading privilege of Disk
     *
     * @param string $name
     * @param string $path
     * @throws DiskException
     */
    private function checkReadableFile(string $name, string $path)
    {
        if(!$this->hasFile($name)) {
            // File not found, or it is not a file
            $this->throwError(
                sprintf(
                    'File "%1$s" not found in "%2$s"',
                    basename($path),
                    dirname($path) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        } elseif(!$this->isReadable($name)) {
            // File is not readable
            $this->throwError(
                sprintf(
                    'File "%1$s" found in "%2$s" but is not readable',
                    basename($path),
                    dirname($path) . DIRECTORY_SEPARATOR
                ),
                __METHOD__
            );
        }
    }

    /**
     * Prepare a file for copy/move within Disk scope
     * If destination file already exists, throw error (unless OVERRIDE_TARGET flag is set)
     *
     * @param string $method
     * @param string $from
     * @param string $to
     * @param int $flag
     * @throws DiskException
     */
    private function prepareCopy(string $method, string $from, string $to, int $flag = 0)
    {
        // Check writing privilege of Disk
        if(!$this->privilegeWrite) {
            throw DiskException::writeError($method);
        }

        // Validate Paths
        $fromPath   =   $this->validatePath($from, $method);
        $toPath   =   $this->validatePath($to, $method);

        // Check if we can copy
        if(!@file_exists($fromPath)) {
            // Source (from) file doesn't exist
            $this->throwError("Source file doesn't exist", $method);
        } elseif(@file_exists($toPath)) {
            // Destination file already exists
            if($flag    !== self::OVERRIDE_TARGET) {
                // flag OVERRIDE_TARGET is not set, throw exception
                $this->throwError("Destination file already exists", $method);
            }
        }

        // Check if destination's directory exists
        if(!@is_dir(dirname($toPath))) {
            $this->throwError(
                sprintf(
                    'Destination directory "%1$s" doesn\'t exist to store "%2$s"',
                    dirname($toPath) . DIRECTORY_SEPARATOR,
                    basename($toPath)
                ),
                $method
            );
        }
    }

    /**
     * @param string $message
     * @param string $method
     * @throws DiskException
     */
    private function throwError(string $message, string $method)
    {
        throw DiskException::fsError($method, $message);
    }

    /**
     * Validate path/filename passed to different methods of this class
     * Make sure given path/filename is within disk's directory
     * by ensuring it is not a symbolic link or contains any references
     *
     * @param string $path
     * @param string $method
     * @param string $whiteList
     * @return string
     * @throws DiskException
     */
    private function validatePath(string $path, string $method, string $whiteList = "") : string
    {
        // Check pattern
        // Starts with alphanumeric String, may contain \/._- characters
        $start  =   preg_quote($whiteList, "#");
        $whiteList =   "/\_.-" . $whiteList;
        if(!preg_match(sprintf("#^[\w%s]+[\w%s]*$#", $start, preg_quote($whiteList, "#")), $path)) {
            throw DiskException::invalidPath(
                $method,
                sprintf(
                    "Given path/filename must start with alphanumeric digits, may contain %s characters",
                    $whiteList
                )
            );
        }

        // Check if its symbolic link
        if(@is_link($path)  === true) {
            throw DiskException::invalidPath($method, "Path/filename is a symbolic link");
        }

        // Check if it contains references, i.e. "./" or ".."
        $illegals   =   ["./", ".\\", "..", "/.", "\\."];
        array_map(function($illegal) use ($path, $method) {
            if(strpos($path, $illegal)  !== false) {
                // Illegal character was found
                throw DiskException::invalidPath(
                    $method,
                    sprintf(
                        'Given path/filename contains illegal "%1$s" on position %2$d',
                        $illegal,
                        strpos($path, $illegal)+1
                    )
                );
            }
        }, $illegals);

        // Return path within Disk
        return $this->path . $path;
    }
}