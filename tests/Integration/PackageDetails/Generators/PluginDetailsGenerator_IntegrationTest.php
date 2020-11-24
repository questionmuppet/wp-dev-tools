<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Integration\PackageDetails\Generators;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\Data\Url;
use Wp_Dev_Tools\PackageDetails\Generators\PluginDetailsGenerator;

final class PluginDetailsGenerator_IntegrationTest extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const PLUGIN_FILE = WP_DEV_TOOLS_TEST_DIR . '/@files/my-plugin.php';
    const README_FILE = WP_DEV_TOOLS_TEST_DIR . '/@files/readme.txt';
    const URL = 'https://www.example.org';

    const HEADERS = [
        'name' => 'My Plugin',
        'homepage' => 'https://www.example.org/my-plugin',
        'version' => '1.0.0',
        'requires' => '5.4',
        'requires_php' => '7.4',
        'author' => 'questionmuppet',
        'author_profile' => 'https://www.example.org/questionmuppet',
    ];

    const SECTIONS = [
        'Description' =>
            "Here is a description of this nifty plugin." . PHP_EOL .
            PHP_EOL .
            "There are several lines in it.",
        'Installation' =>
            "1. Upload the plugin" . PHP_EOL .
            "2. Activate the plugin" . PHP_EOL .
            "3. Collect underpants" . PHP_EOL .
            "4. ???" . PHP_EOL .
            "5. Profit",
    ];

    private File $source;
    private File $readme;

    public function setUp(): void
    {
        $this->source = new File(self::PLUGIN_FILE);
        $this->readme = new File(self::README_FILE);
    }

    /**
     * -------------
     *   T E S T S
     * -------------
     */

    public function test_Details_contains_all_headers_from_plugin_input_file(): void
    {
        $generator = new PluginDetailsGenerator($this->source);

        $details = $generator->details();

        foreach (self::HEADERS as $key => $value)
        {
            $this->assertEquals($value, $details[$key] ?? '', "Failed to assert that a header with key '$key' contained the expected value.");
        }
    }

    /**
     * @depends test_Details_contains_all_headers_from_plugin_input_file
     */
    public function test_Details_contains_all_sections_from_readme_input_file(): void
    {
        $generator = new PluginDetailsGenerator($this->source, ['readme' => $this->readme]);

        $details = $generator->details();

        foreach (self::SECTIONS as $key => $value)
        {
            $this->assertArrayHasKey($key, $details['sections'], "Failed to assert that the details contained a doc section with key '$key'.");
            $this->assertEquals($value, $details['sections'][$key] ?? '', "Failed to assert that a section with key '$key' contained the expected content.");
        }
    }
}