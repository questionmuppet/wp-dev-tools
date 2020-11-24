<?php
/**
 * CreatePluginDetailsController
 * 
 * Executes a package-details create action for a plugin
 */

namespace Wp_Dev_Tools\Controllers;

use Wp_Dev_Tools\PackageDetails\Generators\PluginDetailsGenerator;
use Wp_Dev_Tools\Data\File;

final class CreatePluginDetailsController extends CreateDetailsController
{
    /**
     * Create a plugin details generator
     */
    protected function generator(File $source, array $extras): PluginDetailsGenerator
    {
        return new PluginDetailsGenerator($source, $extras);
    }
}