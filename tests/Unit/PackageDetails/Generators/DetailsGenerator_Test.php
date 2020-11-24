<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\PackageDetails\Generators;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;
use Wp_Dev_Tools\PackageDetails\Generators\DetailsGenerator;

/**
 * Concrete implementation of abstract
 */
final class Concrete_Generator extends DetailsGenerator
{
    protected function header_map(): array { return []; }
    protected function additional_data(): array { return []; }
    public function _source() { return $this->source(); }
    public function _readme() { return $this->readme(); }
    public function _basename() { return $this->basename(); }
    public function _extract_header($key) { return $this->extract_header($key); }
    public function _section_data() { return $this->section_data(); }
}

/**
 * Test case
 */
final class DetailsGenerator_Test extends TestCase
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

    const SOURCE = WP_DEV_TOOLS_TEST_DIR . '/@files/my-plugin.php';
    const README = WP_DEV_TOOLS_TEST_DIR . '/@files/readme.txt';
    const HEADERS = [
        'version' => 'Version',
        'name' => 'Plugin Name',
        'requires' => 'Requires at least',
        'requires_php' => 'Requires PHP',
        'homepage' => 'Plugin URI',
        'author' => 'Author',
        'author_profile' => 'Author URI',
    ];

    private File $source;
    private File $readme;
    private Url $url;

    public function setUp(): void
    {
        // $this->source = $this->createMock(File::class);
        // $this->readme = $this->createMock(File::class);
        $this->source = new File(self::SOURCE);
        $this->readme = new File(self::README);
        $this->url = new Url(self::URL);
        // $this->source->method('contents')->willReturn(self::SOURCE_CONTENTS);
        // $this->readme->method('contents')->willReturn(self::README_CONTENTS);
        // $this->url->method('__toString')->willReturn(self::URL);
    }

    private function createMockGenerator(array $extra_sources = []): DetailsGenerator
    {
        return $this->getMockForAbstractClass(
            DetailsGenerator::class,
            [
                $this->source,
                $extra_sources
            ]
        );
    }
    
    /**
     * -------------
     *   T E S T S
     * -------------
     */

    public function test_Child_class_can_access_source_file_contents(): void
    {
        $sut = new Concrete_Generator($this->source);

        $contents = $sut->_source();

        $this->assertStringContainsString(self::SOURCE_CONTENTS, $contents);
    }

    public function test_Child_class_can_access_readme_file_contents(): void
    {
        $sut = new Concrete_Generator($this->source, [
            'readme' => $this->readme
        ]);

        $contents = $sut->_readme();

        $this->assertStringContainsString(self::README_CONTENTS, $contents);
    }

    /**
     * @depends test_Child_class_can_access_readme_file_contents
     */
    public function test_Readme_file_contents_is_empty_string_when_no_readme_provided(): void
    {
        $sut = new Concrete_Generator($this->source);

        $contents = $sut->_readme();

        $this->assertStringContainsString('', $contents);
    }

    public function test_Basename_is_basename_of_source_file(): void
    {
        $sut = new Concrete_Generator($this->source);

        $basename = $sut->_basename();

        $this->assertEquals(self::SLUG, $basename);
    }

    /**
     * -----------------------
     *   E X T R A C T I O N
     * -----------------------
     */

    /**
     * @depends test_Child_class_can_access_source_file_contents
     */
    public function test_Child_class_can_extract_header_from_source_file(): void
    {
        $sut = new Concrete_Generator($this->source);

        $header = $sut->_extract_header('Plugin Name');

        $this->assertEquals('My Plugin', $header);
    }

    /**
     * @depends test_Child_class_can_access_readme_file_contents
     */
    public function test_Child_class_can_extract_section_data_from_readme(): void
    {
        $sut = new Concrete_Generator($this->source, ['readme' => $this->readme]);

        $sections = $sut->_section_data();

        $this->assertArrayHasKey('Description', $sections);
        $this->assertArrayHasKey('Installation', $sections);
    }
    
    /**
     * -----------------
     *   D E T A I L S
     * -----------------
     */

    /**
     * @depends test_Child_class_can_extract_header_from_source_file
     */
    public function test_Details_contains_all_defined_header_fields(): void
    {
        $sut = $this->createMockGenerator();
        $sut->method('header_map')->willReturn(self::HEADERS);

        $details = $sut->details();

        foreach (self::HEADERS as $key => $header)
        {
            $this->assertArrayHasKey($key, $details, "Failed to assert that the package details contained an expected header '$header'.");
        }
    }

    public function test_Details_contains_data_generated_by_child_class(): void
    {
        $sut = $this->createMockGenerator();
        $sut->method('additional_data')->willReturn(self::DATA);

        $details = $sut->details();

        foreach (self::DATA as $key => $value)
        {
            $this->assertArrayHasKey($key, $details, "Failed to assert that the package details contained an expected key '$key'.");
            $this->assertEquals($value, $details[$key], "Failed to assert that a package details entry '$key' matched the expected value.");
        }
    }

    public function test_Details_contains_download_link_field_when_url_provided(): void
    {
        $sut = $this->createMockGenerator(['url' => $this->url]);

        $details = $sut->details();

        $this->assertArrayHasKey('download_link', $details);
        $this->assertEquals(self::URL, $details['download_link']);
    }

    /**
     * @depends test_Details_contains_download_link_field_when_url_provided
     */
    public function test_Details_contains_no_download_link_field_when_url_omitted(): void
    {
        $sut = $this->createMockGenerator();

        $details = $sut->details();

        $this->assertArrayNotHasKey('download_link', $details);
    }

    public function test_Details_contains_last_updated_field_with_current_timestamp(): void
    {
        $sut = $this->createMockGenerator();

        $details = $sut->details();

        $this->assertArrayHasKey('last_updated', $details);
        $this->assertEqualsWithDelta(time(), $details['last_updated'], 5);
    }

    /**
     * -----------
     *   J S O N
     * -----------
     */

    /**
     * @depends test_Details_contains_all_defined_header_fields
     * @depends test_Details_contains_data_generated_by_child_class
     */
    public function test_Json_is_encoded_json_string_comprised_of_details_data(): void
    {
        $sut = $this->createMockGenerator();
        $sut->method('additional_data')->willReturn(self::DATA);
        $details = $sut->details();

        $json = $sut->json();
        
        $this->assertJsonStringEqualsJsonString(json_encode($details), $json);
    }

    /**
     * @depends test_Json_is_encoded_json_string_comprised_of_details_data
     */
    public function test_Json_can_be_pretty_printed(): void
    {
        $sut = $this->createMockGenerator();
        $sut->method('additional_data')->willReturn(self::DATA);
        $details = $sut->details();

        $json = $sut->json(true);
        
        $this->assertJsonStringEqualsJsonString(json_encode($details), $json);
        $this->assertMatchesRegularExpression('/^\s+"key_1": "One",$/m', $json);
    }
}