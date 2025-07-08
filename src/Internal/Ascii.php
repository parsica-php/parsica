<?php declare(strict_types=1);
/**
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parsica\Parsica\Internal;

/**
 * @internal
 * @psalm-immutable
 */
final class Ascii
{
    private function __construct()
    {
    }

    /**
     * @psalm-pure
     */
    public static function printable(string $char): string
    {
        switch (mb_ord($char)) {
            case   0:
                return "<null>";
            case   1:
                return "<start of header>";
            case   2:
                return "<start of text>";
            case   3:
                return "<end of text>";
            case   4:
                return "<end of transmission>";
            case   5:
                return "<enquiry>";
            case   6:
                return "<acknowledge>";
            case   7:
                return "<bell>";
            case   8:
                return "<backspace>";
            case   9:
                return "<horizontal tab>";
            case  10:
                return "<line feed>";
            case  11:
                return "<vertical tab>";
            case  12:
                return "<form feed>";
            case  13:
                return "<carriage return>";
            case  14:
                return "<shift out>";
            case  15:
                return "<shift in>";
            case  16:
                return "<data link escape>";
            case  17:
                return "<device control 1>";
            case  18:
                return "<device control 2>";
            case  19:
                return "<device control 3>";
            case  20:
                return "<device control 4>";
            case  21:
                return "<negative acknowledge>";
            case  22:
                return "<synchronize>";
            case  23:
                return "<end of transmission block>";
            case  24:
                return "<cancel>";
            case  25:
                return "<end of medium>";
            case  26:
                return "<substitute>";
            case  27:
                return "<escape>";
            case  28:
                return "<file separator>";
            case  29:
                return "<group separator>";
            case  30:
                return "<record separator>";
            case  31:
                return "<unit separator>";
            case  32:
                return "<space>";
            case  34:
                return "<double quote>";
            case  39:
                return "<single quote>";
            case  47:
                return "<slash>";
            case  92:
                return "<backslash>";
            case  96:
                return "<accent>";
            case 127:
                return "<delete>";
            case 130:
                return "<single low-9 quotation mark>";
            case 132:
                return "<double low-9 quotation mark>";
            case 145:
                return "<left single quotation mark>";
            case 146:
                return "<right single quotation mark>";
            case 147:
                return "<left double quotation mark>";
            case 148:
                return "<right double quotation mark>";
            case 160:
                return "<non-breaking space>";
            default:
                return "'$char'";
        }
    }
}
