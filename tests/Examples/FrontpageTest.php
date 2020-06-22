<?php declare(strict_types=1);

namespace Tests\Verraes\Parsica\Examples;

use PHPUnit\Framework\TestCase;
use function Verraes\Parsica\alphaChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\between;
use function Verraes\Parsica\char;

final class FrontpageTest extends TestCase
{
    /** @test */
    public function example_on_frontpage()
    {
        $parser = between(char('{'), char('}'), atLeastOne(alphaChar()));
        $result = $parser->try("{Hello}");
        echo $result->output(); // Hello

        $this->assertEquals('Hello', $result->output());
    }
}
