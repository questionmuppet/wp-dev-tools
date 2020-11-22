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
    private ?File $readme;
    private ?Url $url;

    /**
     * Package details
     */
    private array $details;

    /**
     * Constructor
     */
    public function __construct(File $source, array $extra_sources = [])
    {
        $this->source = $source;
        $this->readme = $extra_sources['readme'] ?? null;
        $this->url = $extra_sources['url'] ?? null;
    }

    /**
     * Get package-details as encoded JSON
     */
    public function json(?bool $pretty_print = null): string
    {
        $opts = $pretty_print ? JSON_PRETTY_PRINT : 0;
        return json_encode($this->details(), $opts);
    }

    /**
     * Get package-details as associative array
     */
    public function details(): array
    {
        if (!isset($this->details))
        {
            $this->details = $this->generate_data();
            $this->details['last_updated'] = time();
            $this->has_download_url() && $this->details['download_url'] = $this->download_url();
        }
        return $this->details;
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
     * Get download url
     */
    private function download_url(): string
    {
        return $this->has_download_url() ? (string) $this->url : '';
    }

    /**
     * Check for download url
     */
    private function has_download_url(): bool
    {
        return isset($this->url);
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