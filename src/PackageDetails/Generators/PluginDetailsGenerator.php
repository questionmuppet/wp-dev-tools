<?php
/**
 * PluginDetailsGenerator
 * 
 * Generates package-details data for a plugin
 */

namespace Wp_Dev_Tools\PackageDetails\Generators;

final class PluginDetailsGenerator extends DetailsGenerator
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
     * Get map of json keys => header keys
     */
    protected function header_map(): array
    {
        return $this->header_map;
    }

    /**
     * Generate plugin-specific data
     */
    protected function additional_data(): array
    {
        return [
            'sections' => $this->section_data(),
        ];
    }

    /**
     * Get plugin slug from source file name
     */
    protected function slug(): string
    {
        return $this->basename();
    }
}