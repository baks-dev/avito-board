<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Avito\Board\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ElementFormatterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('element_format', [$this, 'elementFormat']),
        ];
    }

    public function elementFormat(string $element, string $value): string
    {
        return match ($element)
        {
            'Description' => sprintf('<![CDATA[%s]]>', $value),
            'RimDiameter' => preg_replace('/\D/', '', $value),
            'TireAspectRatio' => preg_replace('/\D/', '', $value),
            'TireSectionWidth' => preg_replace('/\D/', '', $value),
            default => $value
        };
    }
}
