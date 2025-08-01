<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Avito\Board\Mapper\Elements\PassengerTire;

use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\PassengerTireProduct;
use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use BaksDev\Field\Tire\Season\Type\TireSeasonEnum;

final class PassengerTireTireTypeElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'TireType';

    private const string LABEL = 'Сезонность шин';

    public function isMapping(): bool
    {
        return true;
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): ?string
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): ?string
    {
        $AvitoBoardPropertyMapper = $data->getAvitoBoardPropertyMapper();

        if(false === isset($AvitoBoardPropertyMapper[self::ELEMENT]))
        {
            return $this->getDefault();
        }

        if(null === $AvitoBoardPropertyMapper[self::ELEMENT])
        {
            return null;
        }

        /** если связанный элемент не присутствует в маппере или его значение null, то не рендерим ОБЯЗАТЕЛЬНЫЙ элемент */
        if(false === isset($AvitoBoardPropertyMapper[PassengerTireSpikesElement::ELEMENT]) || null === $AvitoBoardPropertyMapper[PassengerTireSpikesElement::ELEMENT])
        {
            return null;
        }

        $tireType = match ($AvitoBoardPropertyMapper[self::ELEMENT])
        {
            TireSeasonEnum::WINTER->value => 'Зимние',
            TireSeasonEnum::SUMMER->value => 'Летние',
            TireSeasonEnum::ALL->value => 'Всесезонные',
        };

        if($tireType === 'Летние' || $tireType === 'Всесезонные')
        {
            return $tireType;
        }

        /** Если шина зимняя - проверяем, является ли она шипованная и конкатенируем */
        $spikes = match ($AvitoBoardPropertyMapper[PassengerTireSpikesElement::ELEMENT])
        {
            'true' => 'шипованные',
            'false' => 'нешипованные',
        };

        return sprintf('%s %s', $tireType, $spikes);
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
