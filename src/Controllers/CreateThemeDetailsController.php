<?php
/**
 * CreateThemeDetailsController
 * 
 * Executes a package-details create action for a plugin
 */

namespace Wp_Dev_Tools\Controllers;

use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\PackageDetails\Generators\ThemeDetailsGenerator;

final class CreateThemeDetailsController extends CreateDetailsController
{
    /**
     * Create a plugin details generator
     */
    protected function generator(File $source, array $extras): ThemeDetailsGenerator
    {
        $extras['slug'] = $this->args()->operand('slug') ?? '';
        return new ThemeDetailsGenerator($source, $extras);
    }
}