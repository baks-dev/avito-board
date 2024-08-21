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

namespace BaksDev\Avito\Board\Mapper\Elements\SweatersAndShirts;

use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\SweatersAndShirtsProduct;
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
        private UrlHelper $helper,
        #[Autowire(env: 'CDN_HOST')]
        private string $cdnHost,
    ) {}

    public function isMapping(): false
    {
        return false;
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
        $images = null;

        /**
         * @var object{
         *     product_img: string,
         *     product_img_cdn: bool,
         *     product_img_ext: string,
         *     product_img_root: bool} $image
         */
        foreach (json_decode($data['product_images'], false, 512, JSON_THROW_ON_ERROR) as $image)
        {
            // Если изображение не загружено - не рендерим
            if (null === $image)
            {
                return null;
            }

            $imgHost = $image->product_img_cdn ? $this->cdnHost : '';
            $imgDir = $image->product_img;
            $imgFile = ($imgHost === '' ? '/image.' : '/large.') . $image->product_img_ext;
            $imgPath = $this->helper->getAbsoluteUrl($imgHost . $imgDir . $imgFile);
            $element = sprintf('<Image url="%s"/>%s', $imgPath, PHP_EOL);
            $images .= $element;
        }

        return $images;
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
}
