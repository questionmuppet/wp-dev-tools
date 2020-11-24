<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\PackageDetails\Generators;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;
use Wp_Dev_Tools\PackageDetails\Generators\PackageDetailsGenerator;

/**
 * Concrete implementation of abstract
 */
final class Concrete_Generator extends PackageDetailsGenerator
{
    protected function generate_data(): array { return []; }
    public function _source() { return $this->source(); }
    public function _readme() { return $this->readme(); }
    public function _basename() { return $this->basename(); }
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
    
    const SOURCE_CONTENTS = 'Plugin Name: My Plugin';
    const README_CONTENTS = '=== My Plugin ===';
    const URL = 'https://www.example.org/';
    const SLUG = 'my-plugin';
    const DATA = [
        'key_1' => 'One',
        'key_2' => 'Two',
    ];

    private File $source;
    private File $readme;
    private Url $url;

    public function setUp(): void
    {
        $this->source = $this->createMock(File::class);
        $this->readme = $this->createMock(File::class);
        $this->url = $this->createMock(Url::class);
        $this->source->method('contents')->willReturn(self::SOURCE_CONTENTS);
        $this->readme->method('contents')->willReturn(self::README_CONTENTS);
        $this->url->method('__toString')->willReturn(self::URL);
    }

    private function createMockGenerator(array $extra_sources = []): PackageDetailsGenerator
    {
        return $this->getMockForAbstractClass(
            PackageDetailsGenerator::class,
            [$this->source, $extra_sources],
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

    public function test_Child_class_can_access_source_file_contents(): void
    {
        $generator = new Concrete_Generator($this->source);

        $contents = $generator->_source();

        $this->assertStringContainsString(self::SOURCE_CONTENTS, $contents);
    }

    public function test_Child_class_can_access_readme_file_contents(): void
    {
        $generator = new Concrete_Generator($this->source, [
            'readme' => $this->readme
        ]);

        $contents = $generator->_readme();

        $this->assertStringContainsString(self::README_CONTENTS, $contents);
    }

    /**
     * @depends test_Child_class_can_access_readme_file_contents
     */
    public function test_Readme_file_contents_is_empty_string_when_no_readme_provided(): void
    {
        $generator = new Concrete_Generator($this->source);

        $contents = $generator->_readme();

        $this->assertStringContainsString('', $contents);
    }

    public function test_Basename_is_basename_of_source_file_without_extension(): void
    {
        $this->source->method('path')->willReturn(sprintf('path/to/%s.php', self::SLUG));
        $generator = new Concrete_Generator($this->source);

        $basename = $generator->_basename();

        $this->assertEquals(self::SLUG, $basename);
    }

    /**
     * -----------------
     *   D E T A I L S
     * -----------------
     */

    public function test_Details_contains_data_generated_by_child_class(): void
    {
        $generator = $this->createMockGenerator();
        $generator->method('generate_data')->willReturn(self::DATA);

        $details = $generator->details();

        foreach (self::DATA as $key => $value)
        {
            $this->assertArrayHasKey($key, $details, "Failed to assert that the package details contained an expected key.");
            $this->assertEquals($value, $details[$key], "Failed to assert that a package details entry matched the expected value.");
        }
    }

    public function test_Details_contains_download_url_field_when_url_provided(): void
    {
        $generator = $this->createMockGenerator(['url' => $this->url]);

        $details = $generator->details();

        $this->assertArrayHasKey('download_url', $details);
        $this->assertEquals(self::URL, $details['download_url']);
    }

    /**
     * @depends test_Details_contains_download_url_field_when_url_provided
     */
    public function test_Details_contains_no_download_url_field_when_url_omitted(): void
    {
        $generator = $this->createMockGenerator();

        $details = $generator->details();

        $this->assertArrayNotHasKey('download_url', $details);
    }

    public function test_Details_contains_last_updated_field_with_current_timestamp(): void
    {
        $generator = $this->createMockGenerator();

        $details = $generator->details();

        $this->assertArrayHasKey('last_updated', $details);
        $this->assertEqualsWithDelta(time(), $details['last_updated'], 5);
    }

    /**
     * @depends test_Details_contains_data_generated_by_child_class
     */
    public function test_Json_is_encoded_json_string_comprised_of_details_data(): void
    {
        $generator = $this->createMockGenerator();
        $generator->method('generate_data')->willReturn(self::DATA);
        $details = $generator->details();

        $json = $generator->json();
        
        $this->assertJsonStringEqualsJsonString(json_encode($details), $json);
    }

    /**
     * @depends test_Json_is_encoded_json_string_comprised_of_details_data
     */
    public function test_Json_can_be_pretty_printed(): void
    {
        $generator = $this->createMockGenerator();
        $generator->method('generate_data')->willReturn(self::DATA);
        $details = $generator->details();

        $json = $generator->json(true);
        
        $this->assertJsonStringEqualsJsonString(json_encode($details), $json);
        $this->assertMatchesRegularExpression('/^\s+"key_1": "One",$/m', $json);
    }
}