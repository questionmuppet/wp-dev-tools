<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\Arguments;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Arguments\ArgumentParser;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\Operand;

final class ArgumentParser_Test extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const NARF = 'Narf!';
    const SCRIPT = 'my-script';
    const VERSION = '1.0';

    private static string $old_error_log;
    private static string $log_file;

    public static function setUpBeforeClass(): void
    {
        self::$old_error_log = ini_get('error_log');
        self::$log_file = rtrim(sys_get_temp_dir(), '/\\') . '/wp-dev-tools-tests.log';
        touch(self::$log_file);
    }

    public function tearDown(): void
    {
        file_put_contents(self::$log_file, '');
        ini_set('error_log', self::$old_error_log);
    }

    public static function tearDownAfterClass(): void
    {
        file_exists(self::$log_file) && unlink(self::$log_file);
    }

    /**
     * ---------------------
     *   A R G U M E N T S
     * ---------------------
     */

    public function test_Can_define_and_retrieve_option(): void
    {
        $options = [Option::create('x')];
        $parser = new ArgumentParser(['options' => $options]);
        $parser->parse('-x');

        $value = $parser->option('x');

        $this->assertEquals(1, $value);
    }

    public function test_Can_define_and_retrieve_operand(): void
    {
        $operands = [Operand::create('narf', Operand::REQUIRED)];
        $parser = new ArgumentParser(['operands' => $operands]);
        $parser->parse(self::NARF);

        $value = $parser->operand('narf');

        $this->assertEquals(self::NARF, $value);
    }

    public function test_Can_define_and_retrieve_command(): void
    {
        $commands = [Command::create('narf','strlen')];
        $parser = new ArgumentParser(['commands' => $commands]);
        $parser->parse('narf');

        $command = $parser->command();
        $this->assertTrue($parser->has_command());
        $this->assertInstanceOf(Command::class, $command);
        $this->assertEquals('narf', $command->getName());
    }

    /**
     * @depends test_Can_define_and_retrieve_command
     */
    public function test_Has_command_returns_false_when_command_not_requested(): void
    {
        $commands = [Command::create('narf','strlen')];
        $parser = new ArgumentParser(['commands' => $commands]);
        $parser->parse('');

        $this->assertFalse($parser->has_command());
    }
    
    /**
     * ---------------
     *   E R R O R S
     * ---------------
     */

    /**
     * @depends test_Can_define_and_retrieve_option
     * @depends test_Can_define_and_retrieve_operand
     * @depends test_Can_define_and_retrieve_command
     */
    public function test_Has_no_error_when_argument_list_is_valid(): void
    {
        $options = [Option::create('x')];
        $parser = new ArgumentParser(['options' => $options]);

        $parser->parse('');

        $this->assertFalse($parser->has_error());
        $this->assertEquals(0, $parser->error_code());
    }

    /**
     * @depends test_Has_no_error_when_argument_list_is_valid
     */
    public function test_Missing_argument_yields_error_message_and_SYNTAX_ERROR_error_code(): void
    {
        $operands = [Operand::create('narf', Operand::REQUIRED)];
        $parser = new ArgumentParser(['operands' => $operands]);
        
        $parser->parse('');

        $this->assertTrue($parser->has_error());
        $this->assertEquals(ArgumentParser::SYNTAX_ERROR, $parser->error_code());
        $this->assertStringContainsString('Operand narf is required', $parser->error_message());
    }

    /**
     * @depends test_Has_no_error_when_argument_list_is_valid
     */
    public function test_Unexpected_argument_yields_error_message_and_SYNTAX_ERROR_error_code(): void
    {
        $operands = [Operand::create('narf', Operand::REQUIRED)];
        $parser = new ArgumentParser(['operands' => $operands]);
        
        $parser->parse(self::NARF . ' Zort!');

        $this->assertTrue($parser->has_error());
        $this->assertEquals(ArgumentParser::SYNTAX_ERROR, $parser->error_code());
        $this->assertStringContainsString('No more operands expected', $parser->error_message());
    }

    /**
     * @depends test_Has_no_error_when_argument_list_is_valid
     */
    public function test_Invalid_argument_yields_error_message_and_INVALID_ARGUMENT_error_code(): void
    {
        $operands = [
            Operand::create('narf', Operand::REQUIRED)->setValidation(function($value) {
                return $value > 50;
            })
        ];
        $parser = new ArgumentParser(['operands' => $operands]);
        
        $parser->parse('42');

        $this->assertTrue($parser->has_error());
        $this->assertEquals(ArgumentParser::INVALID_ARGUMENT, $parser->error_code());
        $this->assertStringContainsString("Operand 'narf' has an invalid value", $parser->error_message());
    }

    /**
     * @depends test_Unexpected_argument_yields_error_message_and_SYNTAX_ERROR_error_code
     */
    public function test_Strict_operands_setting_can_be_overridden_in_constructor(): void
    {
        $operands = [Operand::create('narf', Operand::REQUIRED)];
        $settings = ['strict_operands' => false];
        $parser = new ArgumentParser(['operands' => $operands], $settings);
        
        $parser->parse(self::NARF . ' Zort!');

        $this->assertFalse($parser->has_error());
        $this->assertEquals(0, $parser->error_code());
    }
    
    /**
     * -----------------------------
     *   I N F O   R E Q U E S T S
     * -----------------------------
     */

    /**
     * @depends test_Has_no_error_when_argument_list_is_valid
     */
    public function test_Setting_no_info_flags_yields_noninformation_request_and_help_text_as_message(): void
    {
        $parser = new ArgumentParser([]);

        $parser->parse('');

        $this->assertFalse($parser->is_information_request());
        $this->assertStringContainsString('Usage:', $parser->information_message());
    }

    /**
     * @depends test_Setting_no_info_flags_yields_noninformation_request_and_help_text_as_message
     */
    public function test_Setting_version_flag_yields_information_request_with_version_message(): void
    {
        $settings = [
            'script' => self::SCRIPT,
            'version' => self::VERSION,
        ];
        $parser = new ArgumentParser([], $settings);

        $parser->parse('-v');

        $version = sprintf('%s: v%s', self::SCRIPT, self::VERSION);
        $this->assertTrue($parser->is_information_request());
        $this->assertStringContainsString($version, $parser->information_message());
    }

    /**
     * @depends test_Setting_version_flag_yields_information_request_with_version_message
     */
    public function test_Setting_help_flag_yields_information_request_with_help_message(): void
    {
        $parser = new ArgumentParser([]);

        $parser->parse('-h');

        $this->assertTrue($parser->is_information_request());
        $this->assertStringContainsString('Usage:', $parser->information_message());
    }

    /**
     * @depends test_Setting_help_flag_yields_information_request_with_help_message
     */
    public function test_Argument_errors_are_supressed_when_help_option_set(): void
    {
        $operands = [Operand::create('narf', Operand::REQUIRED)];
        $parser = new ArgumentParser(['operands' => $operands]);

        $parser->parse('-h');

        $this->assertEquals(0, $parser->error_code());
    }

    /**
     * @depends test_Argument_errors_are_supressed_when_help_option_set
     */
    public function test_Argument_errors_are_supressed_when_version_option_set(): void
    {
        $operands = [Operand::create('narf', Operand::REQUIRED)];
        $parser = new ArgumentParser(['operands' => $operands]);

        $parser->parse('-v');

        $this->assertEquals(0, $parser->error_code());
    }
}