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
