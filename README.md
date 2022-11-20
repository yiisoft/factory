<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii Factory</h1>
    <br>
</p>

This package provides abstract object factory allowing to create objects by given definition
with dependencies resolved by a [PSR-11](https://www.php-fig.org/psr/psr-11/) container.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/factory/v/stable.png)](https://packagist.org/packages/yiisoft/factory)
[![Total Downloads](https://poser.pugx.org/yiisoft/factory/downloads.png)](https://packagist.org/packages/yiisoft/factory)
[![Build Status](https://github.com/yiisoft/factory/workflows/build/badge.svg)](https://github.com/yiisoft/factory/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/factory/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/factory/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/factory/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/factory/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Ffactory%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/factory/master)
[![static analysis](https://github.com/yiisoft/factory/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/factory/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/factory/coverage.svg)](https://shepherd.dev/github/yiisoft/factory)

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with [composer](http://getcomposer.org/download/):

```shell
composer require yiisoft/definitions
```

## General usage

The factory is useful if you need to create objects using [definition syntax](https://github.com/yiisoft/definitions)
and/or want to configure defaults for objects created.

```php
$container = new PSR11DependencyInjectionContainer();
$factoryConfig = [
    EngineInterface::class => [
        'class' => EngineMarkOne::class,
        '__construct()' => [
            'power' => 42,
        ],
    ]
];

$factory = new Factory($container, $factoryConfig);

$one = $factory->create(EngineInterface::class);
$two = $factory->create([
    'class' => EngineInterface::class,
    '__construct()' => [
        'power' => 146,
    ],
]);
```

In the code above we define factory config specifying that when we need `EngineInterface`, an instance of `EngineMarkOne`
will be created with `power` constructor argument equals to 42. We also specify that all the dependencies requested by
the object created should be resolved by `PSR11DependencyInjectionContainer`.

First call to `create()` uses default configuration of `EngineInterface` as is. Second call specifies custom
configuration for `power` constructor argument. In this case, configuration specified is merged with default
configuration overriding its keys when the key name is the same.

### Tuning for production

By default, the factory validates definitions right when they are set. In production environment, it makes sense to
turn it off by passing `false` as a third constructor argument:

```php
$factory = new Factory($container, $factoryConfig, false);
```

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii Factory is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
