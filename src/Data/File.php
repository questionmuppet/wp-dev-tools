<?php
/**
 * File
 * 
 * Path to a file on the local filesystem
 */

namespace Wp_Dev_Tools\Data;

final class File
{
    /**
     * File path
     */
    private $path;

    /**
     * Constructor
     */
    public function __construct(string $path)
    {
        if (!file_exists($path))
        {
            throw new \InvalidArgumentException(sprintf(
                "Invalid path provided while trying to instantiate a %s. No file found at path '%s'.",
                get_called_class(),
                $path
            ));
        }
        $this->path = $path;
    }

    /**
     * Get full file contents
     */
    public function contents(): string
    {
        return file_get_contents($this->path);
    }
}