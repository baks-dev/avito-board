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

use BaksDev\Avito\Board\Type\Mapper\Products\AvitoBoardProductInterface;

/**
 * Фотографии — вложенные элементы, по одному элементу «Image» на каждое изображение.
 * На файл фотографии можно ссылаться с помощью (указание обоих атрибутов не допускается):
 * — прямой HTTP-ссылки или ссылки на Яндекс Диск (подробнее) — атрибут «url»;
 *
 * — названия файла с указанием расширения — атрибут «name».
 * Такой способ используется при загрузке файла с объявлениями и архива с фотографиями вручную через Личный кабинет.
 *
 * Допустимые графические форматы: JPEG, PNG.
 * Максимальный размер одного изображения – 25 Мб.
 * К одному объявлению можно добавить не более 10 фотографий, остальные будут проигнорированы.
 *
 * При загрузке фото по ссылке проверьте, что изображение уже доступно и его можно открыть или скачать.
 * Чтобы изменить фотографию в объявлении, используйте другую ссылку.
 * Новое изображение по-прежнему url-адресу не будет загружено.
 */
class ImagesElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Images';

    private const string SUB_ELEMENT = 'Image';

    private const string LABEL = 'Фотографии';

    public function subElement(): string
    {
        return self::SUB_ELEMENT;
    }

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

    public function getProduct(): null
    {
        return null;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(string|array $data = null): ?string
    {
        return $data['product_images'];

        $productImages = $data['product_images'];

        $elementImages = null;
        foreach (json_decode($productImages, false, 512, JSON_THROW_ON_ERROR) as $image)
        {
            /** Если картинок нет - возвращаем null, не рендерим элемент */
            if (null === $image)
            {
                return null;
            }



            $path = $image->product_img . '/image.' . $image->product_img_ext;
            $element = sprintf('<Image url="%s"/>%s', $path, PHP_EOL);
            $elementImages .= $element;
        }

        return $elementImages;
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }
}
