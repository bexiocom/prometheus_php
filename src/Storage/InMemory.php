<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Storage\InMemory.
 */

namespace Bexio\PrometheusPHP\Storage;

use Bexio\PrometheusPHP\MetricTypeCollection;
use Bexio\PrometheusPHP\Sample;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\Options;
use Bexio\PrometheusPHP\Type\Settable;
use Bexio\PrometheusPHP\StorageAdapter;
use Bexio\PrometheusPHP\Type\Subtractable;

/**
 * Storage adapter which stores the metrics in memory.
 *
 * Use this storage adapter for testing purposes or when submitting metrics to a push gateway.
 */
class InMemory implements StorageAdapter
{
    const DEFAULT_VALUE_INDEX = 'default';

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

    public function set(Settable $metric, $value)
    {
        $this->withIndex($metric, function ($name, $labels) use ($value) {
            $this->data[$name][$labels] = $value;
        });
    }

    public function withIndex(MetricType $metric, callable $function)
    {
        $name = $metric->getOptions()->getFullyQualifiedName();
        $labels = $this->getLabelsKey($metric->getOptions());
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
     * Gets a unique identifier for the given options.
     *
     * @param Options $options
     *
     * @return string
     */
    private function getLabelsKey(Options $options)
    {
        $labels = $options->getLabels();
        ksort($labels);

        return empty($labels) ? 'default' : json_encode($labels);
    }

    /**
     * {@inheritdoc}
     */
    public function collectSamples(MetricType $metric)
    {
        return $metric instanceof MetricTypeCollection ?
            $this->collectCollectionSamples($metric)
            : $this->collectSingleSamples($metric);
    }

    /**
     * @param MetricTypeCollection $metric
     *
     * @return Sample[]
     */
    private function collectCollectionSamples(MetricTypeCollection $metric)
    {
        $result = array();

        $name = $metric->getOptions()->getFullyQualifiedName();
        if (isset($this->data[$name])) {
            foreach ($this->data[$name] as $key => $value) {
                $options = 'default' == $key ?
                    $metric->getOptions()
                    : $metric->withLabels(json_decode($key, true))->getOptions();
                $result[] = Sample::createFromOptions($options, $value);
            }
        }

        return $result;
    }

    /**
     * @param MetricType $metric
     *
     * @return Sample[]
     */
    private function collectSingleSamples(MetricType $metric)
    {
        $name = $metric->getOptions()->getFullyQualifiedName();
        $labels = $this->getLabelsKey($metric->getOptions());
        $value = isset($this->data[$name][$labels])
            ? $this->data[$name][$labels]
            : null;

        return array(Sample::createFromOptions($metric->getOptions(), $value));
    }
}
