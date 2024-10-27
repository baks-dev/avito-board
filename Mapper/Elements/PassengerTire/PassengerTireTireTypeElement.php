<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Avito\Board\Mapper\Elements\PassengerTire;

use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\PassengerTireProduct;
use BaksDev\Field\Tire\Season\Type\TireSeasonEnum;

final class PassengerTireTireTypeElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'TireType';

    private const string LABEL = 'Сезонность шин';

    public function isMapping(): true
    {
        return true;
    }

    public function isRequired(): true
    {
        return true;
    }

    public function isChoices(): false
    {
        return false;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(array $data): ?string
    {
        if(null === $data[self::ELEMENT])
        {
            return null;
        }

        /** если связанный элемент не присутствует в маппере или его значение null, то не рендерим ОБЯЗАТЕЛЬНЫЙ элемент */
        if(false === isset($data[PassengerTireSpikesElement::ELEMENT]) || null === $data[PassengerTireSpikesElement::ELEMENT])
        {
            return null;
        }

        $spikes = match ($data[PassengerTireSpikesElement::ELEMENT])
        {
            'true' => 'шипованные',
            'false' => 'нешипованные',
        };

        $tireType = match ($data[self::ELEMENT])
        {
            TireSeasonEnum::WINTER->value => 'Зимние',
            TireSeasonEnum::SUMMER->value => 'Летние',
            TireSeasonEnum::ALL->value => 'Всесезонные',
        };

        if($tireType === 'Летние' || $tireType === 'Всесезонные')
        {
            return $tireType;
        }
        else
        {
            return sprintf('%s %s', $tireType, $spikes);
        }
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function getProduct(): string
    {
        return PassengerTireProduct::class;
    }
}
