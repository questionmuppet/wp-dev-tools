<?php
/**
 * ArgumentParser
 * 
 * Parses command-line options and arguments
 */

namespace Wp_Dev_Tools\Arguments;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\CommandInterface;
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
     * Output
     */
    private string $message = '';
    private int $error = 0;

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

        // Invoke GetOpt
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
     * -----------------
     *   O U T P U T S
     * -----------------
     */

    /**
     * Check for output generated during parsing
     */
    public function has_output(): bool
    {
        return strlen($this->output());
    }

    /**
     * Get the parse message (error or info)
     * 
     * @return string Message generated during the parse, or empty string
     */
    public function output(): string
    {
        return $this->message;
    }

    /**
     * Get error code
     * 
     * @return int Error code if encountered, 0 for successful parsing
     */
    public function error_code(): int
    {
        return $this->error;
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
     * Get the command passed to the script
     */
    public function command(): ?CommandInterface
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
            if (!$this->has_information_message())
            {
                error_log($e->getMessage());
                $this->message = PHP_EOL . $this->getopt->getHelpText();
                $this->error = $e instanceof Invalid ? self::INVALID_ARGUMENT : self::SYNTAX_ERROR;
            }
        }
        finally
        {
            $this->message .= $this->information_message();
        }
    }

    /**
     * Check for a non-error information message
     */
    private function has_information_message(): bool
    {
        return $this->option('help') || $this->option('version');
    }

    /**
     * Get the output for an information command
     */
    private function information_message(): string
    {
        if ($this->option('help')) {
            return $this->getopt->getHelpText();
        }
        if ($this->option('version')) {
            return $this->version_message();
        }
        return '';
    }
    
    /**
     * Get version message
     */
    private function version_message(): string
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
     * Collate common and user-provided options and sort
     * 
     * @return Option[]
     */
    public function collate_options(array $user_opts): array
    {
        $opts = array_replace($this->common_opts(), $user_opts);
        ksort($opts);
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
            'h' => Option::create('h', 'help')->setDescription('Show this help and quit'),
            'v' => Option::create('v', 'version')->setDescription('Show version information and quit'),
        ];
    }
}