<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Integration\PackageDetails\Generators;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Data\File;
use Wp_Dev_Tools\PackageDetails\Generators\ThemeDetailsGenerator;

final class ThemeDetailsGenerator_IntegrationTest extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const SLUG = 'my-theme';
    const SOURCE = WP_DEV_TOOLS_TEST_DIR . '/@files/style.css';
    const README = WP_DEV_TOOLS_TEST_DIR . '/@files/readme.txt';

    public function setUp(): void
    {
        $this->source = new File(self::SOURCE);
        $this->readme = new File(self::README);
    }

    /**
     * -------------
     *   T E S T S
     * -------------
     */

    public function test_Details_contains_slug_field_matching_provided_slug_value(): void
    {
        $sut = new ThemeDetailsGenerator($this->source, [
            'slug' => self::SLUG
        ]);

        $details = $sut->details();

        $this->assertArrayHasKey('slug', $details);
        $this->assertEquals(self::SLUG, $details['slug']);
    }

    /**
     * @depends test_Details_contains_slug_field_matching_provided_slug_value
     */
    public function test_Details_omits_slug_field_when_slug_not_provided(): void
    {
        $sut = new ThemeDetailsGenerator($this->source);

        $details = $sut->details();

        $this->assertArrayNotHasKey('slug', $details);
    }

    public function test_Details_contains_description_field_with_section_text_from_readme_input_file(): void
    {
        $sut = new ThemeDetailsGenerator($this->source, [
            'readme' => $this->readme
        ]);

        $details = $sut->details();

        $this->assertArrayHasKey('description', $details);
        $this->assertStringContainsString('Here is a description of this nifty plugin.', $details['description']);
    }
}