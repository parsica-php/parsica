<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator\FP;

use Mathias\ParserCombinator\FP\Just;
use Mathias\ParserCombinator\FP\Nothing;
use PHPUnit\Framework\TestCase;

final class MaybeTest extends TestCase
{
    /** @test */
    public function functor()
    {
        $x = new Just("a");
        $this->assertEquals(new Just("A"), $x->fmap('strtoupper'));
        $this->assertEquals(new Nothing, (new Nothing())->fmap('strtoupper'));
    }

}
