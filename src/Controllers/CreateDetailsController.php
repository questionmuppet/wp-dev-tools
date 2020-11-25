<?php
/**
 * CreateDetailsController
 * 
 * Executes a package-details create action
 */

namespace Wp_Dev_Tools\Controllers;

use Wp_Dev_Tools\Arguments\ArgumentParser;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;
use Wp_Dev_Tools\PackageDetails\Generators\DetailsGenerator;

abstract class CreateDetailsController implements ControllerInterface
{
    /**
     * Arguments object
     */
    private ArgumentParser $args;

    /**
     * Execute the requested action
     */
    public function execute(ArgumentParser $args): void
    {
        $this->args = $args;
        $generator = $this->generator($this->source(), $this->extras());
        $this->write_file($generator->json($this->prettify()));
    }

    /**
     * Create a details generator object
     */
    abstract protected function generator(File $source, array $extras): DetailsGenerator;

    /**
     * Write content to a file, creating folders as necessary
     */
    private function write_file(string $contents): void
    {
        $output = $this->output_file();
        $dir = dirname($output);
        !is_dir($dir) && mkdir($dir, 0777, true);   // Recurse subdirectories
        file_put_contents($output, $contents);
    }

    /**
     * -----------------------
     *   P A R A M E T E R S
     * -----------------------
     */

    /**
     * Plugin or theme source file
     */
    private function source(): File
    {
        return new File($this->args->operand('source-file'));
    }

    /**
     * Additional sources
     */
    private function extras(): array
    {
        return array_filter([
            'url' => $this->url(),
            'readme' => $this->readme(),
        ]);
    }

    /**
     * Whether to pretty-print the resulting JSON
     */
    private function prettify(): bool
    {
        return (bool) $this->args->option('pretty-print');
    }

    /**
     * Path to output file
     */
    private function output_file(): string
    {
        return $this->args->operand('output-file') ?? '';
    }

    /**
     * Url to download location
     */
    private function url(): ?Url
    {
        $url = $this->args->option('u');
        return $url ? new Url($url) : null;
    }

    /**
     * Readme.txt file
     */
    private function readme(): ?File
    {
        $readme = $this->args->option('r');
        return $readme ? new File($readme) : null;
    }

    /**
     * Get argument parser
     */
    protected function args(): ArgumentParser
    {
        return $this->args;
    }
}