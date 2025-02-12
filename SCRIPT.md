# Генерируем API-клиент без помощи ChatGPT

## Готовим проект

1. Запускаем контейнеры командой `docker-compose up -d`
2. Входим в контейнер командой `docker exec -it php-internal sh`.
3. Устанавливаем зависимости командой `composer install`
4. Выполняем миграции командой `php bin/console doctrine:migrations:migrate`
5. Выходим из контейнера
6. Входим в контейнер командой `docker exec -it php-external sh`.
7. Устанавливаем зависимости командой `composer install`
8. Выполняем миграции командой `php bin/console doctrine:migrations:migrate`
9. Выходим из контейнера

## Проверяем работоспособность приложения

1. Выполняем запрос Create user из Postman-коллекции, видим идентификатор созданного пользователя
2. Выполняем запрос Get user из Postman-коллекции, видим данные пользователя
3. Выполняем запрос Create user profile из Postman-коллекции, видим успешный ответ и нулевой идентификатор
4. Выполняем запрос Get user profile из Postman-коллекции, видим успешный ответ с заполненными именем и фамилией

## Генерируем API-клиент

1. Заходим по адресу `http://localhost:7778/api/doc.json`, видим спецификацию API внутреннего сервиса
2. Входим в контейнер командой `docker exec -it php-internal sh`.
3. Выполняем команду `php bin/console nelmio:apidoc:dump --format=yaml >apidoc.yaml`, получаем соответствующий файл с
   описанием API
4. Выходим из контейнера
5. Добавляем новый сервис в docker-compose.yml
    ```yaml
    openapi-generator:
      image: openapitools/openapi-generator-cli:latest
      volumes:
        - ./:/local
      command: ["generate", "-i", "/local/internal/apidoc.yaml", "-g", "php-dt", "-o", "/local/api-client"]
    ```
6. Выполняем команду `docker-compose up openapi-generator`, видим сгенерированный клиент в директории `api-client`

## Устанавливаем API-клиент

1. В файле `docker-compose.yml` в сервис `php-fpm-external` добавляем `volume` `./api-client/:/api-client`
2. В файле `external/composer.json`
   1. Исправляем значение `minimum-stability` на `dev` 
   2. Добавляем секцию `repositories`
       ```json
       "repositories": [
           {
               "type": "path",
               "url": "/api-client"
           }
       ],
       ```
3. В файле `api-client/composer.json` исправляем поле `name` на `internal/api-client`
4. Останавливаем контейнер командой `docker-compose stop php-fpm-external`
5. Удаляем остановленный контейнер
6. Пересоздаём контейнер командой `docker-compose up -d --build`
7. Входим в контейнер командой `docker exec -it php-internal sh`.
8. Устанавливаем API-клиент командой `composer require internal/api-client`

## Подключаем API-клиент

1. Устанавливаем необходимые зависимости командой `composer require nyholm/psr7 symfony/http-client`
2. Во внешнем сервисе добавляем в конфигурацию параметр `internal_server_url: http://nginx-internal`
3. Добавляем во внешний сервис класс `App\Application\ApiClientConfigFactory`
    ```php
    <?php
    
    namespace App\Application;
    
    use App\ApiClient;
    use App\ApiClientFactory;
    use ArrayAccess;
    use ArrayObject;
    use Articus\DataTransfer\ClassMetadataProviderInterface;
    use Articus\DataTransfer\Factory;
    use Articus\DataTransfer\FieldMetadataProviderInterface;
    use Articus\DataTransfer\MetadataProvider\Annotation as MetadataProviderAnnotation;
    use Articus\DataTransfer\MetadataProvider\Factory\Annotation as MetadataProviderAnnotationFactory;
    use Articus\DataTransfer\Options;
    use Articus\DataTransfer\Service;
    use Articus\DataTransfer\Strategy\Factory\SimplePluginManager as StrategyFactorySimplePluginManager;
    use Articus\DataTransfer\Validator\Factory\SimplePluginManager as ValidatorFactorySimplePluginManager;
    use Articus\PluginManager\Factory\Chain;
    use OpenAPIGenerator\APIClient\ApiClientOptions;
    use OpenAPIGenerator\APIClient\BodyCoder\Factory\Json;
    use OpenAPIGenerator\APIClient\BodyCoder\Factory\PluginManager as OpenAPIGeneratorBodyCoderFactoryPluginManager;
    use OpenAPIGenerator\APIClient\SecurityProvider\Factory\PluginManager as OpenAPIGeneratorSecurityProviderFactoryPluginManager;
    use OpenAPIGenerator\Common\Strategy\Factory\ImmutableDate;
    use OpenAPIGenerator\Common\Strategy\Factory\ImmutableDateTime;
    use OpenAPIGenerator\Common\Strategy\Factory\NoArgObjectList;
    use OpenAPIGenerator\Common\Strategy\Factory\NoArgObjectMap;
    use OpenAPIGenerator\Common\Strategy\Factory\PluginManager as OpenAPIGeneratorStrategyFactoryPluginManager;
    use OpenAPIGenerator\Common\Strategy\Factory\ScalarList;
    use OpenAPIGenerator\Common\Strategy\Factory\ScalarMap;
    use OpenAPIGenerator\Common\Strategy\QueryStringScalar as StrategyQueryStringScalar;
    use OpenAPIGenerator\Common\Strategy\QueryStringScalarArray as StrategyQueryStringScalarArray;
    use OpenAPIGenerator\Common\Validator\Factory\PluginManager as OpenAPIGeneratorValidatorFactoryPluginManager;
    use OpenAPIGenerator\Common\Validator\QueryStringScalar as ValidatorQueryStringScalar;
    use OpenAPIGenerator\Common\Validator\QueryStringScalarArray as ValidatorQueryStringScalarArray;
    use OpenAPIGenerator\Common\Validator\Scalar;
    
    class ApiClientConfigFactory
    {
        public const string DT_STRATEGY_MANAGER_CHAIN = 'app_dt_strategy_manager_chain';
        public const string DT_VALIDATOR_MANAGER_CHAIN = 'app_dt_validator_manager_chain';
        public const string OAG_DT_STRATEGY_MANAGER = 'oag_dt_strategy_manager';
        public const string APP_DT_STRATEGY_MANAGER = 'app_dt_strategy_manager';
        public const string OAG_DT_VALIDATOR_MANAGER = 'oag_dt_validator_manager';
        public const string APP_DT_VALIDATOR_MANAGER = 'app_dt_validator_manager';
    
        protected ArrayAccess $config;
    
        public function __construct()
        {
            $this->config = new ArrayObject(
                [
                    self::DT_STRATEGY_MANAGER_CHAIN => [
                        'managers' => [self::OAG_DT_STRATEGY_MANAGER, self::APP_DT_STRATEGY_MANAGER],
                    ],
                    self::DT_VALIDATOR_MANAGER_CHAIN => [
                        'managers' => [self::OAG_DT_VALIDATOR_MANAGER, self::APP_DT_VALIDATOR_MANAGER],
                    ],
                    Options::DEFAULT_STRATEGY_PLUGIN_MANAGER => [
                        'invokables' => [
                            'QueryStringScalar' => StrategyQueryStringScalar::class,
                            'QueryStringScalarArray' => StrategyQueryStringScalarArray::class,
                        ],
                        'factories' => [
                            'Date' => ImmutableDate::class,
                            'DateTime' => ImmutableDateTime::class,
                            'ObjectList' => NoArgObjectList::class,
                            'ObjectMap' => NoArgObjectMap::class,
                            'ScalarList' => ScalarList::class,
                            'ScalarMap' => ScalarMap::class,
                        ],
                    ],
                    Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER => [
                        'invokables' => [
                            'Scalar' => Scalar::class,
                            'QueryStringScalar' => ValidatorQueryStringScalar::class,
                            'QueryStringScalarArray' => ValidatorQueryStringScalarArray::class,
                        ],
                    ],
                    ApiClientOptions::DEFAULT_BODY_CODER_PLUGIN_MANAGER => [
                        'factories' => [
                            'application/json; charset=utf-8' => Json::class,
                        ]
                    ]
                ]
            );
        }
    
        public function getConfig(): ArrayAccess
        {
            return $this->config;
        }
    }
    ```
4. Добавляем во внешний сервис класс `App\Application\ApiClientConfigCompilerPass`
    ```php
    <?php
    
    namespace App\Application;
    
    use App\ApiClient;
    use App\ApiClientFactory;
    use ArrayAccess;
    use Articus\DataTransfer\ClassMetadataProviderInterface;
    use Articus\DataTransfer\Factory;
    use Articus\DataTransfer\FieldMetadataProviderInterface;
    use Articus\DataTransfer\MetadataProvider\Annotation as MetadataProviderAnnotation;
    use Articus\DataTransfer\MetadataProvider\Factory\Annotation as MetadataProviderAnnotationFactory;
    use Articus\DataTransfer\Options;
    use Articus\DataTransfer\Service;
    use Articus\DataTransfer\Strategy\Factory\SimplePluginManager as StrategyFactorySimplePluginManager;
    use Articus\DataTransfer\Validator\Factory\SimplePluginManager as ValidatorFactorySimplePluginManager;
    use Articus\PluginManager\Chain as PluginManagerChain;
    use Articus\PluginManager\Factory\Chain as PluginManagerChainFactory;
    use Articus\PluginManager\Simple;
    use Nyholm\Psr7\Factory\Psr17Factory;
    use OpenAPIGenerator\APIClient\ApiClientOptions;
    use OpenAPIGenerator\APIClient\BodyCoder\Factory\PluginManager as OpenAPIGeneratorBodyCoderFactoryPluginManager;
    use OpenAPIGenerator\APIClient\SecurityProvider\Factory\PluginManager as OpenAPIGeneratorSecurityProviderFactoryPluginManager;
    use OpenAPIGenerator\Common\Strategy\Factory\PluginManager as OpenAPIGeneratorStrategyFactoryPluginManager;
    use OpenAPIGenerator\Common\Validator\Factory\PluginManager as OpenAPIGeneratorValidatorFactoryPluginManager;
    use Psr\Http\Client\ClientInterface;
    use Psr\Http\Message\RequestFactoryInterface;
    use Psr\Http\Message\ResponseFactoryInterface;
    use Psr\Http\Message\StreamFactoryInterface;
    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;
    use Symfony\Component\HttpClient\NativeHttpClient;
    use Symfony\Component\HttpClient\Psr18Client;
    
    class ApiClientConfigCompilerPass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            $container->register('config', ArrayAccess::class)
                ->setFactory([new Reference(ApiClientConfigFactory::class), 'getConfig'])
                ->setPublic(true);
            $containerRef = new Reference('service_container');
    
            $container->register(Factory::class)
                ->setClass(Factory::class);
            $container->register(Service::class)
                ->setClass(Service::class)
                ->setFactory(new Reference(Factory::class))
                ->setArguments([$containerRef, Service::class])
                ->setPublic(true);
    
            $container->register(MetadataProviderAnnotationFactory::class)
                ->setClass(MetadataProviderAnnotationFactory::class);
            $container->register(MetadataProviderAnnotation::class)
                ->setClass(MetadataProviderAnnotation::class)
                ->setFactory(new Reference(MetadataProviderAnnotationFactory::class))
                ->setArguments([$containerRef, MetadataProviderAnnotation::class])
                ->setPublic(true);
    
            $container->register(ApiClientConfigFactory::DT_STRATEGY_MANAGER_CHAIN)
                ->setClass(PluginManagerChainFactory::class)
                ->setArguments([ApiClientConfigFactory::DT_STRATEGY_MANAGER_CHAIN]);
            $container->register(Options::DEFAULT_STRATEGY_PLUGIN_MANAGER)
                ->setClass(PluginManagerChain::class)
                ->setFactory(new Reference(ApiClientConfigFactory::DT_STRATEGY_MANAGER_CHAIN))
                ->setArguments([$containerRef, Options::DEFAULT_STRATEGY_PLUGIN_MANAGER])
                ->setPublic(true);
    
            $container->register(ApiClientConfigFactory::DT_VALIDATOR_MANAGER_CHAIN)
                ->setClass(PluginManagerChainFactory::class)
                ->setArguments([ApiClientConfigFactory::DT_VALIDATOR_MANAGER_CHAIN]);
            $container->register(Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER)
                ->setClass(PluginManagerChain::class)
                ->setFactory(new Reference(ApiClientConfigFactory::DT_VALIDATOR_MANAGER_CHAIN))
                ->setArguments([$containerRef, Options::DEFAULT_VALIDATOR_PLUGIN_MANAGER])
                ->setPublic(true);
    
            $container->register(OpenAPIGeneratorStrategyFactoryPluginManager::class)
                ->setClass(OpenAPIGeneratorStrategyFactoryPluginManager::class);
            $container->register(ApiClientConfigFactory::OAG_DT_STRATEGY_MANAGER)
                ->setClass(Simple::class)
                ->setFactory(new Reference(OpenAPIGeneratorStrategyFactoryPluginManager::class))
                ->setArguments([$containerRef, ApiClientConfigFactory::OAG_DT_STRATEGY_MANAGER])
                ->setPublic(true);
    
            $container->register(StrategyFactorySimplePluginManager::class)
                ->setClass(StrategyFactorySimplePluginManager::class);
            $container->register(ApiClientConfigFactory::APP_DT_STRATEGY_MANAGER)
                ->setClass(Simple::class)
                ->setFactory(new Reference(StrategyFactorySimplePluginManager::class))
                ->setArguments([$containerRef, ApiClientConfigFactory::APP_DT_STRATEGY_MANAGER])
                ->setPublic(true);
    
            $container->register(OpenAPIGeneratorValidatorFactoryPluginManager::class)
                ->setClass(OpenAPIGeneratorValidatorFactoryPluginManager::class);
            $container->register(ApiClientConfigFactory::OAG_DT_VALIDATOR_MANAGER)
                ->setClass(Simple::class)
                ->setFactory(new Reference(OpenAPIGeneratorValidatorFactoryPluginManager::class))
                ->setArguments([$containerRef, ApiClientConfigFactory::OAG_DT_VALIDATOR_MANAGER])
                ->setPublic(true);
    
            $container->register(ValidatorFactorySimplePluginManager::class)
                ->setClass(ValidatorFactorySimplePluginManager::class);
            $container->register(ApiClientConfigFactory::APP_DT_VALIDATOR_MANAGER)
                ->setClass(Simple::class)
                ->setFactory(new Reference(ValidatorFactorySimplePluginManager::class))
                ->setArguments([$containerRef, ApiClientConfigFactory::APP_DT_VALIDATOR_MANAGER])
                ->setPublic(true);
    
            $container->register(OpenAPIGeneratorBodyCoderFactoryPluginManager::class)
                ->setClass(OpenAPIGeneratorBodyCoderFactoryPluginManager::class);
            $container->register(ApiClientOptions::DEFAULT_BODY_CODER_PLUGIN_MANAGER)
                ->setClass(Simple::class)
                ->setFactory(new Reference(OpenAPIGeneratorBodyCoderFactoryPluginManager::class))
                ->setArguments([$containerRef, ApiClientOptions::DEFAULT_BODY_CODER_PLUGIN_MANAGER])
                ->setPublic(true);
    
            $container->register(OpenAPIGeneratorSecurityProviderFactoryPluginManager::class)
                ->setClass(OpenAPIGeneratorSecurityProviderFactoryPluginManager::class);
            $container->register(ApiClientOptions::DEFAULT_SECURITY_PROVIDER_PLUGIN_MANAGER)
                ->setClass(Simple::class)
                ->setFactory(new Reference(OpenAPIGeneratorSecurityProviderFactoryPluginManager::class))
                ->setArguments([$containerRef, ApiClientOptions::DEFAULT_SECURITY_PROVIDER_PLUGIN_MANAGER])
                ->setPublic(true);
    
            $container->register(RequestFactoryInterface::class)
                ->setClass(Psr17Factory::class)
                ->setPublic(true);
            $container->register(ResponseFactoryInterface::class)
                ->setClass(Psr17Factory::class)
                ->setPublic(true);
            $container->register(StreamFactoryInterface::class)
                ->setClass(Psr17Factory::class)
                ->setPublic(true);
            $container->setAlias(ClassMetadataProviderInterface::class, MetadataProviderAnnotation::class)
                ->setPublic(true);
            $container->setAlias(FieldMetadataProviderInterface::class, MetadataProviderAnnotation::class)
                ->setPublic(true);
    
            $container->register('native_http_client')
                ->setClass(NativeHttpClient::class);
    
            $container->register(ClientInterface::class)
                ->setClass(Psr18Client::class)
                ->setArguments([new Reference('native_http_client')])
                ->setPublic(true);
    
            $container->register(ApiClientFactory::class)
                ->setClass(ApiClientFactory::class);
    
            $container->register(ApiClient::class)
                ->setClass(ApiClient::class)
                ->setFactory(new Reference(ApiClientFactory::class))
                ->setArguments(
                    [$containerRef, ApiClient::class, ['serverUrl' => $container->getParameter('internal_server_url')]]
                );
        }
    }
    ```
5. Во внешнем сервисе исправляем класс `App\Kernel`
    ```php
    <?php
    
    namespace App;
    
    use App\Application\ApiClientConfigCompilerPass;
    use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Kernel as BaseKernel;
    
    class Kernel extends BaseKernel
    {
        use MicroKernelTrait;
    
        protected function build(ContainerBuilder $container): void
        {
            $container->addCompilerPass(new ApiClientConfigCompilerPass());
        }
    }
    ```
6. Во внешнем сервисе исправляем класс `App\Infrastructure\Repository\UserRepository`
    ```php
    <?php
    
    namespace App\Infrastructure\Repository;
    
    use App\ApiClient;
    use App\Domain\DTO\CreateUserModel;
    use App\Domain\DTO\UserModel;
    use App\Domain\Repository\UserRepositoryInterface;
    use App\DTO\CreateUserRequest;
    use App\DTO\CreateUserResponse;
    use App\DTO\GetUserParameterData;
    use App\DTO\GetUserResponse;
    use Symfony\Component\HttpFoundation\Response;
    
    final readonly class UserRepository implements UserRepositoryInterface
    {
        public function __construct(private ApiClient $apiClient)
        {
        }
    
        public function findById(int $id): ?UserModel
        {
            $parameters = new GetUserParameterData();
            $parameters->id = $id;
            $response = $this->apiClient->getUser($parameters);
            if ($response[2] !== Response::HTTP_OK) {
                return null;
            }
            /** @var GetUserResponse $data */
            $data = $response[0];
    
            return new UserModel($data->id, $data->login, $data->password);
        }
    
        public function save(CreateUserModel $user): int
        {
            $request = new CreateUserRequest();
            $request->login = $user->login;
            $request->password = $user->password;
            $response = $this->apiClient->createUser($request);
            /** @var CreateUserResponse $data */
            $data = $response[0];
    
            return $data->id;
        }
    }
    ```
7. Выполняем запрос Create user profile из Postman-коллекции, видим успешный ответ с ненулевым идентификатором
8. Выполняем запрос Get user profile из Postman-коллекции, видим успешный ответ с полным набором данных
