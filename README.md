# A Prometheus client library for PHP

[![Build Status](https://img.shields.io/travis/bexiocom/prometheus_php.svg)](https://travis-ci.org/bexiocom/prometheus_php)
[![Code Climate](https://img.shields.io/codeclimate/github/bexiocom/prometheus_php.svg)](https://codeclimate.com/github/bexiocom/prometheus_php)
[![Test Coverage](https://img.shields.io/codeclimate/coverage/github/bexiocom/prometheus_php.svg)](https://codeclimate.com/github/bexiocom/prometheus_php/coverage)
[![Latest Stable Version](https://img.shields.io/packagist/v/bexiocom/prometheus_php.svg)](https://packagist.org/packages/bexiocom/prometheus_php)

This library aims to be a lightweight utility for instrumenting a PHP
application using [Prometheus](https://prometheus.io). It is heavily inspired
by the [golang client](https://github.com/prometheus/client_golang).

- The library is supposed to be compatible with the PHP version 5.5, 5.6, 7.0
  and 7.1.
- This library tries to do not take any assumptions about the environment it is
  used in. You should be able to use it without any magic or with what ever
  tool belt you are using. Be it a lightweight dependency injection, Sympfony
  or something else.
  
## Features

- Counter, Gauge and Histogram metric types.
- Redis and in memory storage.
- Rendering to text format.

## Missing features

- Summary metric types.
- Ability to submit metric samples to a PushGateway.
- Storage utilising filesystem, Memcached and APC.
- Rendering to Protocol buffer format
- Registry class to ease usage when using the library without andy dependency
  injection tool.
  
## Getting Started

Add this library to your project.

```bash
composer require bexiocom/prometheus_php:dev-master
```

## Usage

Simple counter with no labels attached:

```php
<?php

use Bexio\PrometheusPHP\Metric\Counter;
use Bexio\PrometheusPHP\Storage\InMemory;

$storage = new InMemory();
$counter = Counter::createFromValues('simple_counter', 'Just a simple counting');
$counter->inc();
$storage->persist($counter);
```

More enhanced counter with labels:

```php
<?php

use Bexio\PrometheusPHP\Metric\CounterCollection;
use Bexio\PrometheusPHP\Storage\InMemory;

$storage = new InMemory();
$collection = CounterCollection::createFromValues('labeled_counter', 'Counting with labels', ['type']);
$blueCounter = $collection->withLabels(['type' => 'blue']);
$blueCounter->inc();
$redCounter = $collection->withLabels(['type' => 'red']);
$redCounter->add(42);
$storage->persist($collection);
```

Expose the metrics:

```php
<?php

use Bexio\PrometheusPHP\Metric\Counter;
use Bexio\PrometheusPHP\Metric\CounterCollection;
use Bexio\PrometheusPHP\Output\TextRenderer;
use Bexio\PrometheusPHP\Storage\InMemory;
use GuzzleHttp\Stream\BufferStream;

$buffer = new BufferStream();
$renderer = TextRenderer::createFromStream($buffer);
$storage = new InMemory([
    'simple_counter' => [
        InMemory::DEFAULT_VALUE_INDEX => 1
    ],
    'labeled_counter' => [
        '{"type":"blue"}' => 1,
        '{"type":"red"}' => 42,
    ],
]);
$counter = Counter::createFromValues('simple_counter', 'Just a simple counting');
$renderer->render($counter, $storage->collectSamples($counter));
$collection = CounterCollection::createFromValues('labeled_counter', 'Counting with labels', ['type']);
$renderer->render($collection, $storage->collectSamples($collection));

header(sprintf('Content-Type: %s', TextRenderer::MIME_TYPE));
echo $buffer->getContents();
```

Use Redis as storage backend:

> NOTE: When using the Redis storage, it is highly suggested to add ```ext-redis``` as requirement to your project. 

```php
<?php

use Bexio\PrometheusPHP\Storage\Redis;

$redis = new \Redis();
$redis->connect('localhost');
// Optional: tune your redis connection.
$redis->setOption(\Redis::OPT_READ_TIMEOUT, 10);
$storage = new Redis($redis, 'PROMETHEUS:');
```
Use Redis but open connection lacily:

```php
<?php

use Bexio\PrometheusPHP\Storage\Redis;

$redis = new \Redis();
$storage = new Redis(new \Redis(), 'PROMETHEUS:', function(\Redis $redis) {
  if (!$redis->connect('localhost')) {
      throw new \Exception('Failed to connect to Redis server');
  }
  $redis->setOption(\Redis::OPT_READ_TIMEOUT, 10);
});
```
## How to contribute

First off, thank you for considering contributing to this PHP Prometheus client
library. It's people like you that make it useful for more than a handful of
people.

Pull requests are happily accepted, given they follow the following simple
rules:

- New feature must be covered with tests
- All tests must pass
    ```bash
    composer check
    ```
- Do not mix things up. Only one feature/fix per pull request.

