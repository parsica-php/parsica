<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica;

use Verraes\Parsica\PHPUnit\ParserAssertions;
use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\float;

final class numericTest extends TestCase
{
    use ParserAssertions;

    /** @test */
    public function float()
    {
        $this->assertParse("0", float(), "0");
        $this->assertParse("0.1", float(), "0.1");
        $this->assertParse("123.456", float(), "123.456");
        $this->assertParse(123.456, float()->map('floatval'), "123.456");
    }
}
