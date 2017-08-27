<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Storage\Redis.
 */

namespace Bexio\PrometheusPHP\Storage;

use Bexio\PrometheusPHP\Exception\StorageException;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\MetricTypeCollection;
use Bexio\PrometheusPHP\Options;
use Bexio\PrometheusPHP\Sample;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\Type\Settable;
use Bexio\PrometheusPHP\Type\Subtractable;

class Redis implements StorageAdapter
{
    const DEFAULT_VALUE_INDEX = 'default';

    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var callable
     */
    private $openConnectionFunction;

    /**
     * Constructor.
     *
     * This storage adapter supports to lazily connect to redis only when it is needed. For that purpose, provide a
     * callable. This callable gets execution every time redis gets contacted. The callable should take care that the
     * redis connection is open and ready to receive queries.
     *
     * @param \Redis   $redis          Instance of Redis.
     * @param string   $prefix         (optional) Prefix prepended to any keys stored in Redis.
     *                                 Defaults to empty string.
     * @param callable $openConnection (optional) Callable which gets called right before accessing redis.
     */
    public function __construct(\Redis $redis, $prefix = '', callable $openConnection = null)
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
        $this->openConnectionFunction = $openConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(MetricType $metric)
    {
        foreach ($metric->getActions() as $action) {
            $action->execute($this);
        }

        $metric->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function inc(Incrementable $metric)
    {
        $this->openConnection();
        try {
            $this->redis->hIncrByFloat($this->getMetrickey($metric), $this->getLabelsKey($metric), 1);
        } catch (\Exception $e) {
            throw new StorageException('Failed to increment metric value', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dec(Decrementable $metric)
    {
        $this->openConnection();
        try {
            $this->redis->hIncrByFloat($this->getMetrickey($metric), $this->getLabelsKey($metric), -1);
        } catch (\Exception $e) {
            throw new StorageException('Failed to decrement metric value', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set(Settable $metric, $value)
    {
        $this->openConnection();
        try {
            $this->redis->hSet($this->getMetrickey($metric), $this->getLabelsKey($metric), $value);
        } catch (\Exception $e) {
            throw new StorageException('Failed to set metric value', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(Addable $metric, $value)
    {
        $this->openConnection();
        try {
            $this->redis->hIncrByFloat($this->getMetrickey($metric), $this->getLabelsKey($metric), $value);
        } catch (\Exception $e) {
            throw new StorageException('Failed to add metric value', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sub(Subtractable $metric, $value)
    {
        $this->openConnection();
        try {
            $this->redis->hIncrByFloat($this->getMetrickey($metric), $this->getLabelsKey($metric), $value * -1);
        } catch (\Exception $e) {
            throw new StorageException('Failed to subtract metric value', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collectSamples(MetricType $metric)
    {
        $this->openConnection();
        try {
            $result = $this->redis->hGetAll($this->getMetrickey($metric));

            return $metric instanceof MetricTypeCollection ?
                $this->collectCollectionSamples($metric, $result)
                : $this->collectSingleSamples($metric, $result);
        } catch (\Exception $e) {
            throw new StorageException('Failed to collect metric samples', 0, $e);
        }
    }

    /**
     * @param MetricTypeCollection $metric
     * @param float[]              $data
     *
     * @return Sample[]
     */
    private function collectCollectionSamples(MetricTypeCollection $metric, array $data)
    {
        $result = array();

        foreach ($data as $key => $value) {
            $options = Redis::DEFAULT_VALUE_INDEX == $key ?
                $metric->getOptions()
                : $metric->withLabels(json_decode($key, true))->getOptions();
            $result[] = Sample::createFromOptions($options, $value);
        }

        return $result;
    }

    /**
     * @param MetricType $metric
     * @param float[]    $data
     *
     * @return Sample[]
     */
    private function collectSingleSamples(MetricType $metric, array $data)
    {
        $labels = $this->getLabelsKey($metric);
        $value = isset($data[$labels])
            ? $data[$labels]
            : null;

        return array(Sample::createFromOptions($metric->getOptions(), $value));
    }


    /**
     * Opens the Redis connection.
     */
    private function openConnection()
    {
        if ($this->openConnectionFunction) {
            try {
                call_user_func($this->openConnectionFunction, $this->redis);
            } catch (\Exception $e) {
                throw new StorageException('Failed to connect to Redis', 0, $e);
            }
        }
    }

    /**
     * Gets the redis key for the metric.
     *
     * @param MetricType $metric
     *
     * @return string
     */
    private function getMetrickey(MetricType $metric)
    {
        return sprintf('%s%s', $this->prefix, $metric->getOptions()->getFullyQualifiedName());
    }

    /**
     * Gets a unique identifier for the given options.
     *
     * @param MetricType $metric
     *
     * @return string
     */
    private function getLabelsKey(MetricType $metric)
    {
        $labels = $metric->getOptions()->getLabels();
        ksort($labels);

        return empty($labels) ? Redis::DEFAULT_VALUE_INDEX : json_encode($labels);
    }
}
