<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Arguments\ArgumentParser;
use Wp_Dev_Tools\PackageDetails\Generators\DetailsGenerator;
use Wp_Dev_Tools\Controllers\CreateDetailsController;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;

final class CreateDetailsController_Test extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const SOURCE = WP_DEV_TOOLS_TEST_DIR . '/@files/my-plugin.php';
    const README = WP_DEV_TOOLS_TEST_DIR . '/@files/readme.txt';
    const URL = 'https://www.example.org';
    const OUTPUT_DIR = WP_DEV_TOOLS_TEST_DIR . '/../outputs/';
    const NESTED_OUTPUT_DIR = self::OUTPUT_DIR . 'inner/';
    const OUTPUT_FILE = 'details-test.json';
    const OUTPUT = self::OUTPUT_DIR . self::OUTPUT_FILE;

    const DATE_FORMAT = DATE_RFC7231;
    const JSON = '{"key": "value"}';
    const PRETTY_JSON = "{\n\"key\": \"value\"\n}";

    private array $default_input_args = [
        'date-format' => self::DATE_FORMAT,
        'pretty-print' => false,
        'u' => self::URL,
        'r' => self::README,
        'source-file' => self::SOURCE,
        'output-file' => self::OUTPUT,
    ];

    private DetailsGenerator $generator;
    private CreateDetailsController $controller;

    public function setUp(): void
    {
        self::deleteOutputArtifacts();
        $this->generator = $this->createMock(DetailsGenerator::class);
        $this->generator->method('json')->will($this->returnValueMap([
            [false, self::JSON],
            [true, self::PRETTY_JSON],
        ]));
        $this->controller = $this->getMockForAbstractClass(CreateDetailsController::class);
        $this->controller->method('generator')->willReturn($this->generator);
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteOutputArtifacts();
    }

    private function createMockParser(array $params = []): ArgumentParser
    {
        $options = array_intersect_key($params, [
            'date-format' => '',
            'pretty-print' => '',
            'u' => '',
            'r' => '',
        ]);
        $operands = array_intersect_key($params, [
            'source-file' => '',
            'output-file' => '',
        ]);
        $parser = $this->createMock(ArgumentParser::class);
        $parser->method('option')->will($this->returnValueMap($this->createValueMap($options)));
        $parser->method('operand')->will($this->returnValueMap($this->createValueMap($operands)));
        return $parser;
    }

    private function createValueMap(array $terms): array
    {
        $map = [];
        foreach ($terms as $key => $value)
        {
            $map[] = [$key, $value];
        }
        return $map;
    }

    private static function deleteOutputArtifacts(): void
    {
        // Delete default output file
        file_exists(self::OUTPUT) && unlink(self::OUTPUT);

        // Delete nested subdirectory
        $nested_file = self::NESTED_OUTPUT_DIR . self::OUTPUT_FILE;
        file_exists($nested_file) && unlink($nested_file);
        is_dir(self::NESTED_OUTPUT_DIR) && rmdir(self::NESTED_OUTPUT_DIR);
    }

    /**
     * -----------------
     *   S O U R C E S
     * -----------------
     */

    public function test_Execute_passes_all_sources_to_child_class_generator_method(): void
    {
        $args = $this->createMockParser($this->default_input_args);
        $sut = $this->controller;

        $this->controller
            ->expects($this->once())
            ->method('generator')
            ->with(
                $this->equalTo(new File(self::SOURCE)),
                $this->equalTo([
                    'url' => new Url(self::URL),
                    'readme' => new File(self::README),
                    'date_format' => self::DATE_FORMAT,
                ])
            );

        $sut->execute($args);
    }

    /**
     * @depends test_Execute_passes_all_sources_to_child_class_generator_method
     */
    public function test_Url_source_is_omitted_when_not_set_in_ArgumentParser(): void
    {
        $inputs = $this->default_input_args;
        unset($inputs['u']);
        $args = $this->createMockParser($inputs);
        $sut = $this->controller;

        $this->controller
            ->expects($this->once())
            ->method('generator')
            ->with(
                $this->anything(),
                $this->logicalNot($this->arrayHasKey('url'))
            );

        $sut->execute($args);
    }

    /**
     * @depends test_Execute_passes_all_sources_to_child_class_generator_method
     */
    public function test_Readme_source_is_omitted_when_not_set_in_ArgumentParser(): void
    {
        $inputs = $this->default_input_args;
        unset($inputs['r']);
        $args = $this->createMockParser($inputs);
        $sut = $this->controller;

        $this->controller
            ->expects($this->once())
            ->method('generator')
            ->with(
                $this->anything(),
                $this->logicalNot($this->arrayHasKey('readme'))
            );

        $sut->execute($args);
    }

    /**
     * @depends test_Execute_passes_all_sources_to_child_class_generator_method
     */
    public function test_Date_format_is_omitted_when_not_set_in_ArgumentParser(): void
    {
        $inputs = $this->default_input_args;
        unset($inputs['date-format']);
        $args = $this->createMockParser($inputs);
        $sut = $this->controller;

        $this->controller
            ->expects($this->once())
            ->method('generator')
            ->with(
                $this->anything(),
                $this->logicalNot($this->arrayHasKey('date_format'))
            );

        $sut->execute($args);
    }

    /**
     * ---------------
     *   O U T P U T
     * ---------------
     */

    public function test_Execute_writes_generator_json_to_output_file(): void
    {
        $args = $this->createMockParser($this->default_input_args);
        $sut = $this->controller;

        $sut->execute($args);

        $this->assertTrue(file_exists(self::OUTPUT));
        $this->assertEquals(self::JSON, file_get_contents(self::OUTPUT));
    }

    /**
     * @depends test_Execute_writes_generator_json_to_output_file
     */
    public function test_Output_json_is_prettified_when_pretty_print_set_in_ArgumentParser(): void
    {
        $inputs = $this->default_input_args;
        $inputs['pretty-print'] = true;
        $args = $this->createMockParser($inputs);
        $sut = $this->controller;

        $sut->execute($args);

        $this->assertEquals(self::PRETTY_JSON, file_get_contents(self::OUTPUT));
    }

    /**
     * @depends test_Execute_writes_generator_json_to_output_file
     */
    public function test_Directories_are_created_when_output_file_has_path_to_nonexistent_directory(): void
    {
        $inputs = $this->default_input_args;
        $inputs['output-file'] = self::NESTED_OUTPUT_DIR . self::OUTPUT_FILE;
        $args = $this->createMockParser($inputs);
        $sut = $this->controller;

        $sut->execute($args);

        $this->assertTrue(is_dir(self::NESTED_OUTPUT_DIR));
    }
}