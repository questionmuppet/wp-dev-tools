<?php
/**
 * PackageDetailsGenerator
 * 
 * Extracts package-details data from plugin and theme files
 */

namespace Wp_Dev_Tools\Package_Details\Generators;

use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;

abstract class PackageDetailsGenerator
{
    /**
     * Inputs
     */
    private File $source;
    private File $readme;
    private Url $url;

    /**
     * Package details
     */
    private array $details;

    /**
     * Constructor
     */
    public function __construct(File $source, Url $url, File $readme = null)
    {
        $this->source = $source;
        $this->url = $url;
        is_null($readme) || $this->readme = $readme;
    }

    /**
     * Get package-details as encoded JSON
     */
    public function json(): string
    {
        return json_encode($this->details());
    }

    /**
     * Get package-details as associative array
     */
    public function details(): array
    {
        if (!isset($this->details))
        {
            $this->details = $this->generate_data();
            $this->details['download_url'] = $this->download_url();
            $this->details['last_updated'] = time();
        }
        return $this->details;
    }
    
    /**
     * Get download url
     */
    private function download_url(): string
    {
        return (string) $this->url;
    }

    /**
     * Generate details data from the input files
     */
    abstract protected function generate_data(): array;

    /**
     * Get contents of input source file
     */
    protected function source(): string
    {
        return $this->source->contents();
    }

    /**
     * Get basename of the source file
     */
    protected function basename(): string
    {
        return basename($this->source->path(), '.php');
    }

    /**
     * Get contents of input readme file
     */
    protected function readme(): string
    {
        return $this->has_readme() ? $this->readme->contents() : '';
    }

    /**
     * Check for a readme input file
     */
    private function has_readme(): bool
    {
        return isset($this->readme);
    }
}