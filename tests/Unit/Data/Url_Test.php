<?php
declare(strict_types=1);

namespace Wp_Dev_Tools\Tests\Unit\Data;

use PHPUnit\Framework\TestCase;
use Wp_Dev_Tools\Data\Url;

final class Url_Test extends TestCase
{
    /**
     * -----------------
     *   F I X T U R E
     * -----------------
     */

    const URL = 'https://www.example.org/';

    /**
     * -------------
     *   T E S T S
     * -------------
     */

    public function test_Converting_to_string_returns_url_value(): void
    {
        $url = new Url(self::URL);

        $value = (string) $url;

        $this->assertEquals(self::URL, $value);
    }

    /**
     * @depends test_Converting_to_string_returns_url_value
     */
    public function test_Invalid_url_in_constructor_throws_InvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Url('invalid-url-string');
    }
}