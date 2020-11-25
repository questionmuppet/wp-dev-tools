<?php
/**
 * ThemeDetailsGenerator
 * 
 * Generates package-details data for a theme
 */

namespace Wp_Dev_Tools\PackageDetails\Generators;

use Wp_Dev_Tools\Data\File;

final class ThemeDetailsGenerator extends DetailsGenerator
{
    /**
     * Theme header map
     */
    private $header_map = [
        'name' => 'Theme Name',
        'homepage' => 'Theme URI',
        'version' => 'Version',
        'requires' => 'Requires at least',
        'requires_php' => 'Requires PHP',
    ];

    /**
     * Theme slug
     */
    private string $slug;

    /**
     * Constructor
     */
    public function __construct(File $source, array $extra_sources = [])
    {
        parent::__construct($source, $extra_sources);
        $this->slug = $extra_sources['slug'] ?? '';
    }

    /**
     * Get map of json keys => header keys
     */
    protected function header_map(): array
    {
        return $this->header_map;
    }
    
    /**
     * Get theme slug passed in args
     */
    protected function slug(): string
    {
        return $this->slug;
    }

    /**
     * Generate plugin-specific data
     */
    protected function additional_data(): array
    {
        return [
            'description' => $this->description(),
        ];
    }
    
    /**
     * Get long description section from readme
     */
    private function description(): string
    {
        return $this->section_data()['Description'] ?? '';
    }
}