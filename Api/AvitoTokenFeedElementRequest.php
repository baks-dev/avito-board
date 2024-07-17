<?php

namespace BaksDev\Avito\Board\Api;

use BaksDev\Avito\Api\AvitoApi;
use BaksDev\Avito\Type\Authorization\AvitoAccessToken;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use DomainException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
final class AvitoTokenFeedElementRequest extends AvitoApi
{

    public function getFeedElement(AvitoTokenAuthorization $authorization = null): AvitoAccessToken
    {
        $client = $this->tokenHttpClient($authorization);

        $response = $client->request(
            'GET',
            '/web/1/autoload/user-docs/category/67016/field/110431/values-xml'
        );

        dd($response);

        $result = $response->toArray(false);
        dd($result);

        if (array_key_exists('error', $result))
        {
            $this->logger->critical($result['error'] . ': ' . $result['error_description'], [__FILE__ . ':' . __LINE__]);

            throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
        }

        if ($response->getStatusCode() !== 200)
        {
            throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
        }

        return new AvitoAccessToken($token, true);
    }
}
