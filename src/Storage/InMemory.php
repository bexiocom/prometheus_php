<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Storage\InMemory.
 */

namespace Bexio\PrometheusPHP\Storage;

use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\Options;
use Bexio\PrometheusPHP\Type\Observable;
use Bexio\PrometheusPHP\Type\Settable;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Subtractable;

/**
 * Storage adapter which stores the metrics in memory.
 *
 * Use this storage adapter for testing purposes or when submitting metrics to a push gateway.
 */
class InMemory extends ArrayStorage implements StorageAdapter
{
    /**
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
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
        $this->withIndex($metric, function ($name, $labels) {
            $this->data[$name][$labels]++;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function add(Addable $metric, $value)
    {

        $this->withIndex($metric, function ($name, $labels) use ($value) {
            $this->data[$name][$labels] += $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function dec(Decrementable $metric)
    {
        $this->withIndex($metric, function ($name, $labels) {
            $this->data[$name][$labels]--;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sub(Subtractable $metric, $value)
    {
        $this->withIndex($metric, function ($name, $labels) use ($value) {
            $this->data[$name][$labels] -= $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function set(Settable $metric, $value)
    {
        $this->withIndex($metric, function ($name, $labels) use ($value) {
            $this->data[$name][$labels] = $value;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function observe(Observable $metric, $value)
    {
        $this->withIndex($metric, function ($name, $labels) {
            $this->data[$name][$labels]++;
        }, $value);
        $this->withIndex($metric, function ($name, $labels) use ($value) {
            $this->data[$name][$labels] += $value;
        }, null, '_sum');
    }

    /**
     * @param MetricType $metric
     * @param callable   $function
     * @param float|null $value
     * @param string     $suffix
     */
    protected function withIndex(MetricType $metric, callable $function, $value = null, $suffix = '')
    {
        $name = $metric->getOptions()->getFullyQualifiedName() . $suffix;
        $labels = $this->getLabelsKey($metric, $value);
        $this->ensureIndex($name, $labels);
        $function($name, $labels);
    }

    /**
     * @param string $name   The metric name
     * @param string $labels The labels index
     */
    private function ensureIndex($name, $labels)
    {
        if (empty($this->data[$name][$labels])) {
            $this->data[$name][$labels] = 0;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getData(MetricType $metric, $suffix = '')
    {
        $name = $metric->getOptions()->getFullyQualifiedName() . $suffix;

        return isset($this->data[$name]) ? $this->data[$name] : array();
    }

    /**
     * {@inheritdoc}
     */
    protected function getKeys(MetricType $metric)
    {
        return array_keys($this->getData($metric));
    }
}
