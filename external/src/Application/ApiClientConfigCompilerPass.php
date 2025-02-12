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
