<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica\ParseResult;

use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\char;

final class ParseResultTest extends TestCase
{

    /** @test */
    public function ParseSuccess_continueWith()
    {
        $input = "abc";
        $success = char('a')->run($input);
        $result = $success->continueWith(char('b'));
        $this->assertTrue($result->isSuccess());
        $this->assertEquals("c", $result->remainder());
    }

    /** @test */
    public function ParseFailure_continueWith()
    {
        $input = "abc";
        $fail = char('x')->run($input);
        $result = $fail->continueWith(char('a'));
        $this->assertTrue($result->isFail());
    }

}
