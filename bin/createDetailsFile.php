<?php
/**
 * createDetailsFile.php
 * 
 * Creates a package-details file in JSON format from WordPress plugin and theme files
 */

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Operand;
use GetOpt\Command;
use Wp_Dev_Tools\Controllers;
use Wp_Dev_Tools\Arguments\ArgumentParser;

require_once(__DIR__ . '/../vendor/autoload.php');

/**
 * Script constants
 */
define('SCRIPT', 'WP Package Details Generator');
define('VERSION', '@git_tag@');

/**
 * Script parameters
 */
$options = [
    Option::create('d', 'date-format', GetOpt::REQUIRED_ARGUMENT)
        ->setArgumentName('format')
        ->setDescription('Datetime format to use for the "last_updated" field in the output. See https://www.php.net/manual/en/datetime.format.php for possible values.'),
        
    Option::create('p', 'pretty-print')
        ->setDescription('Prettify the JSON formatting in the output file.'),
    
    Option::create('r', null, GetOpt::REQUIRED_ARGUMENT)
        ->setArgumentName('readme-file')
        ->setDescription('Relative path to readme file. Used to generate the "sections" field in the output. This file should adhere to the WordPress plugin readme file standard.')
        ->setValidation('is_readable', 'Invalid path provided for %s. File "%s" does not exist or cannot be read.'),

    Option::create('u', null, GetOpt::REQUIRED_ARGUMENT)
        ->setArgumentName('download-url')
        ->setDescription('Url to package location. Used for the "download_link" field in the output.')
        ->setValidation(function($url) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }, 'Invalid url provided for %s. "%s" does not match a recognized url pattern.'),
];
$operands = [
    'source' => Operand::create('source-file', Operand::REQUIRED)
        ->setDescription('Plugin or theme file to extract headers from. Use "plugin-name.php" for plugins and "style.css" for themes.')
        ->setValidation('is_readable', 'Invalid path provided for %s. File "%s" does not exist or cannot be read.'),

    'output' => Operand::create('output-file', Operand::OPTIONAL)
        ->setDescription('Relative path to output file. If omitted defaults to "package-details.json".')
        ->setDefaultValue('package-details.json'),

    'slug' => Operand::create('slug', Operand::REQUIRED)
        ->setDescription("Unique slug to identify the theme. Should correspond to the name of your theme's root directory."),
];
$commands = [
    Command::create('plugin', Controllers\CreatePluginDetailsController::class)
        ->setShortDescription('Create a package-details file for a plugin release. Try "plugin -h" for more information.')
        ->setDescription("Create a package-details file for a plugin release.\n\nA plugin source file is required to read headers from. This is usually your main plugin file in the root directory.\nReadme.txt and download_url can be provided optionally.")
        ->addOptions($options)
        ->addOperands([
            $operands['source'],
            $operands['output'],
        ]),

    Command::create('theme', Controllers\CreateThemeDetailsController::class)
        ->setShortDescription('Create a package-details file for a theme release. Try "theme -h" for more information.')
        ->setDescription("Create a package-details file for a theme release.\n\nA theme source file is required to read headers from. This is usually style.css in your theme's root directory.\nReadme.txt and download_url can be provided optionally.")
        ->addOptions($options)
        ->addOperands([
            $operands['source'],
            $operands['slug'],
            $operands['output'],
        ]),
];

/**
 * Parse command-line arguments
 */
$args = new ArgumentParser(['commands' => $commands], [
    'script' => SCRIPT,
    'version' => VERSION,
]);
$args->parse();

/**
 * Exit on error
 */
if ($args->has_error())
{
    echo $args->error_message() . PHP_EOL . PHP_EOL;
    echo $args->information_message();
    exit($args->error_code());
}

/**
 * Display information request and exit
 */
if ($args->is_information_request() || !$args->has_command())
{
    echo $args->information_message();
    exit(0);
}

/**
 * Execute requested command
 */
printf("Writing file to %s", $args->operand('output-file'));
$classname = $args->command()->getHandler();
$controller = new $classname();
$controller->execute($args);
