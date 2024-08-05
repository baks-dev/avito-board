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

namespace BaksDev\Avito\Board\Type\Mapper\Elements;

use BaksDev\Avito\Board\Type\Mapper\Products\AvitoProductInterface;

/**
 * Включение интернет-звонков через Авито. По объявлениям смогут звонить по интернету.
 *
 * Вы не пропустите звонки и не потеряете клиентов: если интернет плохой, в объявлении покажем временный номер,
 * а вся история вызовов сохранится в чате.
 *
 * Интернет-звонки работают корректно, если вы укажете устройство для приёма звонков в параметре CallsDevices.
 * Преимущества интернет-звонков:
 * — Легко отличить важные вызовы от спама: видно кто звонит и по какому объявлению.
 * — Сделки совершаются быстрее: пользователи с включенными интернет-звонками получают на 24% больше звонков.
 *
 * Входящие интернет-звонки через Авито можно отключить для всех объявлений в Настройках в любое время.
 *
 * Одно из значений:
 * — Да
 * — Нет
 */
class InternetCallsElement implements AvitoBoardElementInterface
{
    private const string INTERNET_CALLS_ELEMENT = 'InternetCalls';

    private const string INTERNET_CALLS_LABEL = 'Разноширокий комплект шин';

    public function __construct(
        private readonly ?AvitoProductInterface $product = null,
        protected null|string|false $data = false,
    ) {}

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): false
    {
        return false;
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

    public function getProduct(): null
    {
        return $this->product;
    }

    public function setData(string|array $data): void
    {
        // @TODO хардкодим временное значение пока, так как нет таблицы откуда забирать данные
        $this->data = 'Нет';

        //                $this->data = $profile['_address_from_avito_token_profile'];
    }

    public function fetchData(): ?string
    {
        if (false === $this->data)
        {
            // @TODO пишем в лог
            throw new \Exception('Не вызван метод setData' . __CLASS__);
        }

        return $this->data;
    }

    public function element(): string
    {
        return self::INTERNET_CALLS_ELEMENT;
    }

    public function label(): string
    {
        return self::INTERNET_CALLS_LABEL;
    }
}
