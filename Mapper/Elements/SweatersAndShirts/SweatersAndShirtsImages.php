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

namespace BaksDev\Avito\Board\Mapper\Elements\SweatersAndShirts;

use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\SweatersAndShirtsProduct;
use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\UrlHelper;

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
 *
 *  Список элементов для категории "Легковые шины"
 *  https://www.avito.ru/autoload/documentation/templates/67016?onlyRequiredFields=false&fileFormat=xml
 */
final readonly class SweatersAndShirtsImages implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Images';

    private const string LABEL = 'Фотографии';

    public function __construct(
        #[Autowire(env: 'CDN_HOST')] private string $cdnHost,
        #[Autowire(env: 'HOST')] private string $host,
    ) {}

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): ?string
    {
        $avitoIMG = $this->transform($data->getAvitoProductImages());

        if(true === empty($avitoIMG))
        {
            $avitoIMG = $this->transform($data->getProductImages());
        }

        return $avitoIMG;
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
        return SweatersAndShirtsProduct::class;
    }

    /** Формируем массив элементов с изображениями */
    private function transform(?array $images): ?string
    {
        if(is_null($images))
        {
            return null;
        }

        $render = null;

        // Сортировка массива элементов с изображениями по root = true
        usort($images, static function($f) {
            return $f->img_root === true ? -1 : 1;
        });

        /**
         * @var object{
         *     img: string,
         *     img_cdn: bool,
         *     img_ext: string,
         *     img_root: bool}|null $image
         */
        foreach($images as $image)
        {
            // Если изображение не загружено - не рендерим
            if(true === empty($image))
            {
                continue;
            }

            $imgHost = 'https://'.($image->img_cdn === true ? $this->cdnHost : $this->host);
            $imgDir = $image->img;
            $imgFile = ($image->img_cdn === true ? '/large.' : '/image.').$image->img_ext;
            $imgPath = $imgHost.$imgDir.$imgFile;
            $element = sprintf('<Image url="%s"/>%s', $imgPath, PHP_EOL);
            $render .= $element;
        }

        return $render ?: null;
    }
}
