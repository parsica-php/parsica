<?php declare(strict_types=1);

namespace Tests\Mathias\ParserCombinator;

use Mathias\ParserCombinator\PHPUnit\ParserTestCase;
use function Mathias\ParserCombinator\{crlf, eol, newline, tab};

final class SpaceTest extends ParserTestCase
{

    /** @test */
    public function newline()
    {
        $this->assertParse("\n", newline(), "\nabc");
        $this->assertNotParse(newline(), "\rabc");
    }

    /** @test */
    public function crlf()
    {
        $this->assertParse("\r\n", crlf(), "\r\nabc");
        $this->assertNotParse(crlf(), "\rabc", "crlf");
        $this->assertNotParse(crlf(), "\rabc", "crlf");
    }

    /** @test */
    public function eol()
    {
        $this->assertParse("\n", eol(), "\nabc");
        $this->assertParse("\r\n", eol(), "\r\nabc");
        $this->assertNotParse(eol(), "\rabc", "eol");
    }

    /** @test */
    public function tab()
    {
        $this->assertParse("\t", tab(), "\tabc");
        $this->assertNotParse(tab(), "abc", "tab");
    }

    /** @test */
    public function todo()
    {

        $this->fail("not implemented");
        /**
         *
         * -- | Skip /zero/ or more white space characters.
         * --
         * -- See also: 'skipMany' and 'spaceChar'.
         * space :: (MonadParsec e s m, Token s ~ Char) => m ()
         * space = void $ takeWhileP (Just "white space") isSpace
         * {-# INLINE space #-}
         *
         * -- | Like 'space', but does not accept newlines and carriage returns.
         * --
         * -- @since 8.1.0
         * hspace :: (MonadParsec e s m, Token s ~ Char) => m ()
         * hspace = void $ takeWhileP (Just "white space") isHSpace
         * {-# INLINE hspace #-}
         *
         * -- | Skip /one/ or more white space characters.
         * --
         * -- See also: 'skipSome' and 'spaceChar'.
         * --
         * -- @since 6.0.0
         * space1 :: (MonadParsec e s m, Token s ~ Char) => m ()
         * space1 = void $ takeWhile1P (Just "white space") isSpace
         * {-# INLINE space1 #-}
         *
         * -- | Like 'space1', but does not accept newlines and carriage returns.
         * --
         * -- @since 8.1.0
         * hspace1 :: (MonadParsec e s m, Token s ~ Char) => m ()
         * hspace1 = void $ takeWhile1P (Just "white space") isHSpace
         * {-# INLINE hspace1 #-}
         */
    }
}

