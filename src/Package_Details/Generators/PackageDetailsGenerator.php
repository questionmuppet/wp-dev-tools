<?php
/**
 * PackageDetailsGenerator
 * 
 * Extracts package-details data from plugin and theme files
 */

namespace Wp_Dev_Tools\Package_Details\Generators;

abstract class PackageDetailsGenerator
{
    /**
     * Input file paths
     */
    private string $source_path;
    private string $readme_path;

    /**
     * Package details
     */
    private array $details;

    /**
     * Constructor
     */
    public function __construct(array $params)
    {
        $this->set_source_path($params['source'] ?? '');
        $this->set_readme_path($params['readme'] ?? '');
    }
    
    /**
     * Set the source path
     */
    private function set_source_path(string $path): void
    {
        if (!file_exists($path))
        {
            throw new \InvalidArgumentException(sprintf(
                "Invalid source file provided while trying to instantiate a %s. No file found at path '%s'.",
                get_called_class(),
                $path
            ));
        }
        $this->source_path = $path;
    }
    
    /**
     * Set the readme path
     */
    private function set_readme_path(string $path): void
    {
        if (strlen($path) && !file_exists($path))
        {
            throw new \InvalidArgumentException(sprintf(
                "Invalid readme file provided while trying to instantiate a %s. No file found at path '%s'.",
                get_called_class(),
                $path
            ));
        }
        $this->readme_path = $path;
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
        return file_get_contents($this->source_path);
    }

    /**
     * Get contents of input readme file
     */
    protected function readme(): string
    {
        return strlen($this->readme_path)
            ? file_get_contents($this->readme_path)
            : '';
    }
}