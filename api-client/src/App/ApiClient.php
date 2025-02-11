<?php
declare(strict_types=1);

namespace App;

use Articus\DataTransfer as DT;
use OpenAPIGenerator\APIClient as OAGAC;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * My App
 * This is an awesome app!
 * The version of the OpenAPI document: 1.0.0
 */
class ApiClient extends OAGAC\AbstractApiClient
{
    //region createUser
    /**
     * @param \App\DTO\CreateUserRequest $requestContent
     * @param string $requestMediaType
     * @param string $responseMediaType
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws DT\Exception\InvalidData
     */
    public function createUserRaw(
        \App\DTO\CreateUserRequest $requestContent,
        string $requestMediaType = 'application/json',
        string $responseMediaType = 'application/json'
    ): ResponseInterface
    {
        $request = $this->createRequest('POST', '/api/v1/create-user', [], []);
        $request = $this->addBody($request, $requestMediaType, $requestContent);
        $request = $this->addAcceptHeader($request, $responseMediaType);
        return $this->httpClient->sendRequest($request);
    }

    /**
     * @param \App\DTO\CreateUserRequest $requestContent
     * @param string $requestMediaType
     * @param string $responseMediaType
     * @return array
     * @throws ClientExceptionInterface
     * @throws DT\Exception\InvalidData
     * @throws OAGAC\Exception\InvalidResponseBodySchema
     */
    public function createUser(
        \App\DTO\CreateUserRequest $requestContent,
        string $requestMediaType = 'application/json',
        string $responseMediaType = 'application/json'
    ): array
    {
        $response = $this->createUserRaw($requestContent, $requestMediaType, $responseMediaType);
        $responseContent = null;
        $contentStrategy = null;
        $contentValidator = null;
        switch ($response->getStatusCode())
        {
            case 200:
                /* Успешный ответ */
                $responseContent = new \App\DTO\CreateUserResponse();
                break;
            case 400:
                /* Ошибка валидации */
                break;
        }
        $this->parseBody($response, $responseContent, $contentStrategy, $contentValidator);
        return [$responseContent, $response->getHeaders(), $response->getStatusCode(), $response->getReasonPhrase()];
    }

    /**
     * @param \App\DTO\CreateUserRequest $requestContent
     * @param string $requestMediaType
     * @param string $responseMediaType
     * @return \App\DTO\CreateUserResponse
     * @throws ClientExceptionInterface
     * @throws DT\Exception\InvalidData
     * @throws OAGAC\Exception\InvalidResponseBodySchema
     * @throws OAGAC\Exception\UnsuccessfulResponse
     */
    public function createUserResult(
        \App\DTO\CreateUserRequest $requestContent,
        string $requestMediaType = 'application/json',
        string $responseMediaType = 'application/json'
    ): \App\DTO\CreateUserResponse
    {
        return $this->getSuccessfulContent(...$this->createUser($requestContent, $requestMediaType, $responseMediaType));
    }
    //endregion

    //region getUser
    /**
     * @param \App\DTO\GetUserParameterData $parameters
     * @param string $responseMediaType
     * @return ResponseInterface
     * @throws ClientExceptionInterface
     * @throws DT\Exception\InvalidData
     */
    public function getUserRaw(
        \App\DTO\GetUserParameterData $parameters,
        string $responseMediaType = 'application/json'
    ): ResponseInterface
    {
        $request = $this->createRequest('GET', '/api/v1/get-user/{id}', $this->getPathParameters($parameters), []);
        $request = $this->addAcceptHeader($request, $responseMediaType);
        return $this->httpClient->sendRequest($request);
    }

    /**
     * @param \App\DTO\GetUserParameterData $parameters
     * @param string $responseMediaType
     * @return array
     * @throws ClientExceptionInterface
     * @throws DT\Exception\InvalidData
     * @throws OAGAC\Exception\InvalidResponseBodySchema
     */
    public function getUser(
        \App\DTO\GetUserParameterData $parameters,
        string $responseMediaType = 'application/json'
    ): array
    {
        $response = $this->getUserRaw($parameters, $responseMediaType);
        $responseContent = null;
        $contentStrategy = null;
        $contentValidator = null;
        switch ($response->getStatusCode())
        {
            case 200:
                /* Успешный ответ */
                $responseContent = new \App\DTO\GetUserResponse();
                break;
            case 404:
                /* Пользователь не найден */
                break;
        }
        $this->parseBody($response, $responseContent, $contentStrategy, $contentValidator);
        return [$responseContent, $response->getHeaders(), $response->getStatusCode(), $response->getReasonPhrase()];
    }

    /**
     * @param \App\DTO\GetUserParameterData $parameters
     * @param string $responseMediaType
     * @return \App\DTO\GetUserResponse
     * @throws ClientExceptionInterface
     * @throws DT\Exception\InvalidData
     * @throws OAGAC\Exception\InvalidResponseBodySchema
     * @throws OAGAC\Exception\UnsuccessfulResponse
     */
    public function getUserResult(
        \App\DTO\GetUserParameterData $parameters,
        string $responseMediaType = 'application/json'
    ): \App\DTO\GetUserResponse
    {
        return $this->getSuccessfulContent(...$this->getUser($parameters, $responseMediaType));
    }
    //endregion
}

