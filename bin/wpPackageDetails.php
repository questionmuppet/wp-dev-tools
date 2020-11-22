<?php
/**
 * wpPackageDetails
 * 
 * Creates a package-details file in JSON format from WordPress plugin and theme files
 */

use Wp_Dev_Tools\Arguments\ArgumentParser;
use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Operand;
use Wp_Dev_Tools\Package_Details\Generators\PluginDetailsGenerator;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;

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

/**
 * Parse command-line arguments
 */
$args = [
    'options' => $options,
    'operands' => $operands,
];
$settings = [
    'script' => SCRIPT,
    'version' => VERSION,
];
$parser = new ArgumentParser($args, $settings);
$parser->parse();

/**
 * Exit with error or information request
 */
if ($parser->has_output())
{
    echo $parser->output();
    exit($parser->error_code());
}

/**
 * Write file
 */
$output = $parser->operand('output-file');
$prettify = $parser->option('pretty-print');
$source = new File($parser->operand('source-file'));
$readme = $parser->option('readme');
$extras = array_filter([
    'url' => new Url($parser->option('u')),
    'readme' => $readme ? new File($readme) : null,
]);

$generator = new PluginDetailsGenerator($source, $extras);

echo "Writing file to '$output.'";
file_put_contents($output, $generator->json($prettify));
