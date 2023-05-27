<?php
/**
 * DetailsGenerator
 * 
 * Extracts package-details data from plugin and theme files
 */

namespace Wp_Dev_Tools\PackageDetails\Generators;

use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;

abstract class DetailsGenerator
{
    /**
     * Inputs
     */
    private File $source;
    private ?File $readme;
    private ?Url $url;
    
    /**
     * Timestamp
     */
    private string $date_format;
    private string $default_date_format = DATE_RFC7231;

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
        $this->date_format = $extra_sources['date_format'] ?? $this->default_date_format;
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
            $details = array_replace($this->common_data(), $this->additional_data());
            $this->details = array_filter($details);
        }
        return $this->details;
    }
    
    /**
     * -----------
     *   D A T A
     * -----------
     */
    
    /**
     * Data common to all packages
     */
    private function common_data(): array
    {
        return array_merge(
            $this->headers(),
            [
                'slug' => $this->slug(),
                'last_updated' => $this->current_time(),
                'download_link' => $this->download_url(),
            ]
        );
    }
    
    /**
     * Generate header fields
     */
    private function headers(): array
    {
        $headers = [];
        foreach ($this->header_map() as $key => $header)
        {
            $headers[$key] = $this->extract_header($header);
        }
        return $headers;
    }

    /**
     * Map of json keys => header keys
     */
    abstract protected function header_map(): array;

    /**
     * Unique slug for plugin or theme
     */
    abstract protected function slug(): string;
    
    /**
     * Plugin- or theme-specific data
     */
    abstract protected function additional_data(): array;

    /**
     * -----------------------
     *   E X T R A C T I O N
     * -----------------------
     */

    /**
     * Extract header value from the source file
     * 
     * @see https://developer.wordpress.org/reference/functions/get_file_data/
     */
    protected function extract_header(string $key): string
    {
        $pattern = sprintf(
            '/^[ \t\/*#@]*%s:(.*)$/mi',
            preg_quote($key, '/')
        );
        return preg_match($pattern, $this->source(), $matches)
            ? trim($matches[1])
            : '';
    }

    /**
     * Extract section data from readme.txt
     * 
     * @see https://wordpress.org/plugins/developers/
     */
    protected function section_data(): array
    {
        $pattern = '/^\s*==([^=]+)==\s*$/m';
        $matches = preg_split(
            pattern: $pattern,
            subject: $this->readme(),
            flags: PREG_SPLIT_DELIM_CAPTURE
        );

        $sections = [];
        for ($i = 1; $i < count($matches); $i += 2)
        {
            $key = trim($matches[$i]);
            $content = trim($matches[$i + 1]);
            $sections[$key] = $content;
        }
        return $sections;
    }

    /**
     * ---------------------
     *   T I M E S T A M P
     * ---------------------
     */

    /**
     * Get the current timestamp as a human-readable string
     */
    private function current_time(): string
    {
        return date($this->date_format);
    }

    /**
     * ---------------
     *   I N P U T S
     * ---------------
     */

    /**
     * Get contents of input source file
     */
    protected function source(): string
    {
        return $this->source->contents();
    }

    /**
     * Get basename of the source file, with extension removed
     */
    protected function basename(): string
    {
        $file = $this->source->path();
        $suffix = '.' . pathinfo($file, PATHINFO_EXTENSION);
        return basename($file, $suffix);
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
    
    /**
     * Get download url
     */
    private function download_url(): string
    {
        return strval($this->url ?? '');
    }
}