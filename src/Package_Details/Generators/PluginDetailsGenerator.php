<?php
/**
 * PluginDetailsGenerator
 * 
 * Generates package-details data for a plugin
 */

namespace Wp_Dev_Tools\Package_Details\Generators;

final class PluginDetailsGenerator extends PackageDetailsGenerator
{
    /**
     * Plugin header map
     */
    private $header_map = [
        'version' => 'Version',
        'name' => 'Plugin Name',
        'requires' => 'Requires at least',
        'requires_php' => 'Requires PHP',
        'homepage' => 'Plugin URI',
        'author' => 'Author',
        'author_profile' => 'Author URI',
    ];

    /**
     * Generate details data
     */
    protected function generate_data(): array
    {
        $data = array_merge(
            $this->headers(),
            [
                'slug' => $this->basename(),
                'sections' => $this->section_data(),
            ]
        );
        return array_filter($data);
    }

    /**
     * Generate plugin headers
     */
    private function headers(): array
    {
        $headers = [];
        foreach ($this->header_map as $key => $header)
        {
            $headers[$key] = $this->extract_header($header);
        }
        return $headers;
    }

    /**
     * Extract header value from the plugin file
     * 
     * @see https://developer.wordpress.org/reference/functions/get_file_data/
     */
    private function extract_header(string $key): string
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
     * Generate section data
     */
    private function section_data(): array
    {
        $pattern = '/^\s*==([^=]+)==\s*$/m';
        $matches = preg_split($pattern, $this->readme(), null, PREG_SPLIT_DELIM_CAPTURE);

        $sections = [];
        for ($i = 1; $i < count($matches); $i += 2)
        {
            $key = trim($matches[$i]);
            $content = trim($matches[$i + 1]);
            $sections[$key] = $content;
        }
        return $sections;
    }
}