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
define('VERSION', '1.0-alpha');

/**
 * Script parameters
 */
$options = [
    Option::create('p', 'pretty-print')
        ->setDescription('Prettify the JSON formatting in the output file.'),
    
    Option::create('r', null, GetOpt::REQUIRED_ARGUMENT)
        ->setArgumentName('readme-file')
        ->setDescription('Relative path to readme file. Used to generate the "sections" field in the output. This file should adhere to the WordPress plugin readme file standard.')
        ->setValidation('is_readable', 'Invalid path provided for %s. File "%s" does not exist or cannot be read.'),

    Option::create('u', null, GetOpt::REQUIRED_ARGUMENT)
        ->setArgumentName('download-url')
        ->setDescription('Url to package location. Used for the "download_url" field in the output.')
        ->setValidation(function($url) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }, 'Invalid url provided for %s. "%s" does not match a recognized url pattern.'),
];
$operands = [
    Operand::create('source-file', Operand::REQUIRED)
        ->setDescription('Plugin or theme file to extract headers from. Will usually be "plugin-name.php" for plugins and "style.css" for themes.')
        ->setValidation('is_readable', 'Invalid path provided for %s. File "%s" does not exist or cannot be read.'),

    Operand::create('output-file', Operand::OPTIONAL)
        ->setDescription('Relative path to output file. When omitted defaults to "package-details.json".')
        ->setDefaultValue('package-details.json'),
];
$commands = [
    Command::create('plugin', Controllers\CreatePluginDetailsController::class)
        ->setShortDescription('Create a package-details file for a plugin release. Try "plugin -h" for more information.')
        ->setDescription("Create a package-details file from source files.\n\nA plugin source file is required to read headers from. Readme.txt and download_url can be provided optionally.")
        ->addOptions($options)
        ->addOperands($operands),
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
