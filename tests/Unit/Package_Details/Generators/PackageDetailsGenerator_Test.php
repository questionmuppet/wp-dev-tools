<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\Package_Details\Generators;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Package_Details\Generators\PackageDetailsGenerator;

/**
 * Concrete implementation of abstract
 */
final class Concrete_Generator extends PackageDetailsGenerator
{
    protected function generate_data(): array { return []; }
    public function _source() { return $this->source(); }
    public function _readme() { return $this->readme(); }
}

/**
 * Test case
 */
final class PackageDetailsGenerator_Test extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const SOURCE = WP_DEV_TOOLS_TEST_DIR . '/@files/plugin.php';
    const README = WP_DEV_TOOLS_TEST_DIR . '/@files/readme.txt';
    const PLUGIN_NAME = 'My Plugin';
    const DATA = [
        'key_1' => 'One',
        'key_2' => 'Two',
    ];

    private function createMockGenerator(array $params): MockObject
    {
        return $this->getMockForAbstractClass(
            PackageDetailsGenerator::class,
            [$params],
            '',
            true,
            true,
            true,
            ['generate_data']
        );
    }
    
    /**
     * -------------
     *   T E S T S
     * -------------
     */

    public function test_Invalid_path_to_source_file_throws_InvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createMockGenerator([
            'source' => 'path/to/fake/file.txt',
        ]);
    }

    /**
     * @depends test_Invalid_path_to_source_file_throws_InvalidArgumentException
     */
    public function test_Invalid_and_nonempty_path_to_readme_file_throws_InvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createMockGenerator([
            'source' => self::SOURCE,
            'readme' => 'path/to/fake/file.txt',    
        ]);
    }

    public function test_Child_class_can_access_source_file_contents()
    {
        $generator = new Concrete_Generator([
            'source' => self::SOURCE
        ]);

        $contents = $generator->_source();

        $this->assertStringContainsString(sprintf('Plugin Name: %s', self::PLUGIN_NAME), $contents);
    }

    public function test_Child_class_can_access_readme_file_contents()
    {
        $generator = new Concrete_Generator([
            'source' => self::SOURCE,
            'readme' => self::README,
        ]);

        $contents = $generator->_readme();

        $this->assertStringContainsString(sprintf('=== %s ===', self::PLUGIN_NAME), $contents);
    }

    /**
     * @depends test_Child_class_can_access_readme_file_contents
     */
    public function test_Readme_file_contents_is_empty_string_when_no_readme_provided()
    {
        $generator = new Concrete_Generator([
            'source' => self::SOURCE,
        ]);

        $contents = $generator->_readme();

        $this->assertStringContainsString('', $contents);
    }

    public function test_Details_is_data_generated_by_child_class()
    {
        $generator = $this->createMockGenerator(['source' => self::SOURCE]);
        $generator
            ->method('generate_data')
            ->willReturn(self::DATA);

        $details = $generator->details();
        
        $this->assertEqualsCanonicalizing(self::DATA, $details);
    }

    /**
     * @depends test_Details_is_data_generated_by_child_class
     */
    public function test_Json_is_encoded_json_string_comprised_of_details_data()
    {
        $generator = $this->createMockGenerator(['source' => self::SOURCE]);
        $generator
            ->method('generate_data')
            ->willReturn(self::DATA);

        $json = $generator->json();
        
        $this->assertJsonStringEqualsJsonString(json_encode(self::DATA), $json);       
    }
}