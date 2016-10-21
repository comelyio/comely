<?php
declare(strict_types=1);

namespace Comely\IO\Emails\Message;

use Comely\IO\Emails\Exception\MessageException;


/**
 * Class Attachment
 * @package Comely\IO\Emails\Message
 */
class Attachment
{
    /** @var string */
    private $path;
    /** @var string */
    private $type;
    /** @var string */
    private $name;
    /** @var null|string */
    private $id;
    /** @var string */
    private $disposition;

    /**
     * Attachment constructor.
     *
     * @param string $filePath
     * @param string|null $type
     * @throws MessageException
     */
    public function __construct(string $filePath, string $type = null)
    {
        // Check if file exists and is readable
        if(!@is_readable($filePath)) {
            throw MessageException::attachmentUnreadable(__METHOD__, $filePath);
        }

        $this->path =   $filePath; // Save file path
        $this->type =   $type; // Content type (if specified)
        $this->name =   basename($this->path);
        $this->id   =   null;
        $this->disposition  =   "attachment";

        // Check if content type is not explicit
        if(!$this->type) {
            // Check if "fileinfo" extension is loaded
            if(extension_loaded("fileinfo")) {
                $fileInfo   =   new \finfo(FILEINFO_MIME_TYPE);
                $this->type =   $fileInfo->file($this->path);
            }

            if(!$this->type) {
                $this->type =   self::fileType($this->name);
            }
        }
    }

    /**
     * @param string $name
     * @return Attachment
     */
    public function name(string $name) : self
    {
        $this->name =   $name;
        return $this;
    }

    /**
     * @param string $id
     * @return Attachment
     */
    public function contentId(string $id) : self
    {
        $this->id   =   $id;
        $this->disposition  =   "inline";
        return $this;
    }

    /**
     * @param string $disposition
     * @return Attachment
     */
    public function disposition(string $disposition) : self
    {
        $this->disposition  =   $disposition;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisposition() : string
    {
        return $this->disposition;
    }

    /**
     * @return array
     * @throws MessageException
     */
    public function getMime() : array
    {
        $read   =   @file_get_contents($this->path);
        if(!$read) {
            throw MessageException::attachmentUnreadable(__METHOD__, $this->path);
        }

        $mime[] =   sprintf('Content-Type: %1$s; name="%2$s"', $this->type, $this->name);
        $mime[] =   "Content-Transfer-Encoding: base64";
        $mime[] =   sprintf('Content-Disposition: %1$s', $this->disposition);
        if($this->id) {
            $mime[] =   sprintf('Content-ID: <%1$s>', $this->id);
        }

        $mime[] =   chunk_split(base64_encode($read));

        return $mime;
    }

    /**
     * Get suggested content type from file extension, defaults to "octet-stream"
     *
     * @param string $fileName
     * @return string
     */
    public static function fileType(string $fileName) : string
    {
        switch(pathinfo($fileName, PATHINFO_EXTENSION)) {
            case "txt":
                return "text/plain";
            case "zip":
                return "application/zip";
            case "tar":
                return "application/x-tar";
            case "pdf":
                return "application/pdf";
            case "psd":
                return "image/vnd.adobe.photoshop";
            case "swf":
                return "application/x-shockwave-flash";
            case "odt":
                return "application/vnd.oasis.opendocument.text";
            case "docx":
                return "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
            case "doc":
                return "application/msword";
            case "avi":
                return "video/x-msvideo";
            case "mp4":
                return "video/mp4";
            case "jpeg":
            case "jpg":
                return "image/jpeg";
            case "png":
                return "image/png";
            case "gif":
                return "image/gif";
            case "svg":
                return "image/svg+xml";
            default:
                return "application/octet-stream";
        }
    }
}