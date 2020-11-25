<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Integration\Controllers;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Arguments\ArgumentParser;
use Wp_Dev_Tools\Controllers\CreateThemeDetailsController;

final class CreateThemeDetailsController_IntegrationTest extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const SLUG = 'my-theme';
    const SOURCE = WP_DEV_TOOLS_TEST_DIR . '/@files/style.css';
    const OUTPUT = WP_DEV_TOOLS_TEST_DIR . '/../outputs/plugin-details-test.json';

    private ArgumentParser $args;

    public function setUp(): void
    {
        self::deleteOutputArtifacts();
        $this->args = $this->createMock(ArgumentParser::class);
        $this->args->method('operand')->will($this->returnValueMap([
            ['source-file', self::SOURCE],
            ['output-file', self::OUTPUT],
            ['slug', self::SLUG],
        ]));
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteOutputArtifacts();
    }

    private static function deleteOutputArtifacts(): void
    {
        file_exists(self::OUTPUT) && unlink(self::OUTPUT);
    }

    /**
     * -------------
     *   T E S T S
     * -------------
     */

    public function test_Executing_controller_generates_details_file(): void
    {
        $sut = new CreateThemeDetailsController();

        $sut->execute($this->args);

        $this->assertTrue(file_exists(self::OUTPUT));
        $this->assertStringContainsString('"name":"My Theme"', file_get_contents(self::OUTPUT));
    }
}