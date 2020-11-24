<?php
/**
 * ArgumentParser
 * 
 * Parses command-line options and arguments
 */

namespace Wp_Dev_Tools\Arguments;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Invalid;

final class ArgumentParser
{
    /**
     * Error codes
     */
    const SYNTAX_ERROR = 1;
    const INVALID_ARGUMENT = 2;

    /**
     * Parse error
     */
    private string $error_message = '';
    private int $error_code = 0;

    /**
     * Settings
     */
    private array $settings = [
        'script' => '',
        'version' => '',
        'strict_operands' => true,
    ];

    /**
     * GetOpt library class
     */
    private GetOpt $getopt;

    /**
     * Constructor
     */
    public function __construct(array $args, array $settings = [])
    {
        // Parser settings
        $this->set_settings($settings);

        // Extract argument definitions
        $options = $this->collate_options($args['options'] ?? []);
        $commands = $args['commands'] ?? [];
        $operands = $args['operands'] ?? [];

        // Set up GetOpt
        $this->getopt = new GetOpt($options, $this->getopt_settings());
        $this->getopt->addCommands($commands);
        $this->getopt->addOperands($operands);
    }

    /**
     * Set parser settings
     */
    private function set_settings(array $settings): void
    {
        foreach ($settings as $key => $value)
        {
            array_key_exists($key, $this->settings) && $this->settings[$key] = $value;
        }
    }

    /**
     * Settings used to instantiate GetOpt object
     */
    private function getopt_settings(): array
    {
        return [
            GetOpt::SETTING_STRICT_OPERANDS => $this->settings['strict_operands'],
        ];
    }
    
    /**
     * -------------
     *   P A R S E
     * -------------
     */

    /**
     * Parse command-line arguments
     * 
     * @param mixed $arguments
     */
    public function parse($arguments = null): void
    {
        try
        {
            $this->getopt->process($arguments);
        }
        catch (ArgumentException $e)
        {
            if (!$this->is_information_request())
            {
                $this->error_message = 'Error: ' . $e->getMessage();
                $this->error_code = $e instanceof Invalid ? self::INVALID_ARGUMENT : self::SYNTAX_ERROR;
            }
        }
    }

    /**
     * -----------------
     *   O U T P U T S
     * -----------------
     */
    
    /**
     * Get error message
     */
    public function error_message(): string
    {
        return $this->error_message;
    }

    /**
     * Check for error
     */
    public function has_error(): bool
    {
        return (bool) $this->error_code();
    }

    /**
     * Get error code
     * 
     * @return int Error code if encountered, 0 for successful parsing
     */
    public function error_code(): int
    {
        return $this->error_code;
    }

    /**
     * Get an option value
     * 
     * @return mixed
     */
    public function option(string $key)
    {
        return $this->getopt->getOption($key);
    }

    /**
     * Check for a valid command
     */
    public function has_command(): bool
    {
        return !is_null($this->command());
    }

    /**
     * Get the command passed to the script
     */
    public function command(): ?Command
    {
        return $this->getopt->getCommand();
    }

    /**
     * Get a positional parameter value
     * 
     * @return mixed
     */
    public function operand(string $key)
    {
        return $this->getopt->getOperand($key);
    }

    /**
     * -------------------------
     *   I N F O   &   H E L P
     * -------------------------
     */

    /**
     * Check if user requested information
     */
    public function is_information_request(): bool
    {
        return $this->option('help') || $this->option('version');
    }

    /**
     * Get requested information message or help text
     */
    public function information_message(): string
    {
        return $this->option('version')
            ? $this->version_text()
            : $this->getopt->getHelpText();
    }
    
    /**
     * Get version information
     */
    private function version_text(): string
    {
        return sprintf(
            "%s: v%s" . PHP_EOL,
            $this->settings['script'],
            $this->settings['version']
        );
    }

    /**
     * -----------------
     *   O P T I O N S
     * -----------------
     */

    /**
     * Collate common and user-provided options and sort by short form
     * 
     * @return Option[]
     */
    private function collate_options(array $user_opts): array
    {
        $opts = array_merge($this->common_opts(), $user_opts);
        usort(
            $opts,
            function(Option $a, Option $b) {
                return strcmp($a->getShort(), $b->getShort());
            }
        );
        return $opts;
    }

    /**
     * Options common to all scripts
     * 
     * @return Option[]
     */
    private function common_opts(): array
    {
        return [
            Option::create('h', 'help')->setDescription('Show this help and quit'),
            Option::create('v', 'version')->setDescription('Show version information and quit'),
        ];
    }
}