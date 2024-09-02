<?php

namespace BaksDev\Avito\Board\Mapper\Products;

use BaksDev\Core\Type\UidType\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class AvitoProductResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attr = $argument->getAttributes(ParamConverter::class);
        $paramConverter = current($attr);

        if(!$paramConverter)
        {
            return [];
        }

        $resolver = $paramConverter->resolver;

        if($resolver !== $this::class)
        {
            return [];
        }

        $value = $request->attributes->get($argument->getName());

        return match ($value)
        {
            'Triangle', PassengerTireProduct::PRODUCT_DIR => [PassengerTireProduct::PRODUCT_DIR],
            'Футболки', SweatersAndShirtsProduct::PRODUCT_DIR => [SweatersAndShirtsProduct::PRODUCT_DIR],
            default => ['default'],
        };
    }
}
