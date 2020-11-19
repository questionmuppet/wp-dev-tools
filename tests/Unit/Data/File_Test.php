<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Data\File;

final class File_Test extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const PATH = WP_DEV_TOOLS_TEST_DIR . '/@files/my-plugin.php';

    /**
     * -------------
     *   T E S T S
     * -------------
     */
    
    public function test_Invalid_filepath_in_constructor_throws_InvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new File('path/to/nonexistent/file.txt');
    }

    /**
     * @depends test_Invalid_filepath_in_constructor_throws_InvalidArgumentException
     */
    public function test_Contents_are_file_contents(): void
    {
        $file = new File(self::PATH);

        $contents = $file->contents();

        $this->assertStringContainsString('Plugin Name: My Plugin', $contents);
    }
}