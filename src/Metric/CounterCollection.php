<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Metric\CounterCollection.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\MetricCollection;
use Bexio\PrometheusPHP\MetricTypeCollection;
use Bexio\PrometheusPHP\Options;

/**
 * Collection for Counter metrics.
 *
 * @see MetricTypeCollection
 */
class CounterCollection extends MetricCollection implements MetricTypeCollection
{
    /**
     * @param string   $name       The metric name.
     * @param string   $help       The help information for this metric.
     * @param string[] $labelNames The names of the label set for this collection.
     * @param string   $namespace  (optional) The metric namespace.
     * @param string   $subsystem  (optional) The metric subsystem.
     * @param string[] $labels     (optional) Key value pairs of static metric labels.
     *
     * @return CounterCollection
     */
    public static function createFromValues(
        $name,
        $help,
        array $labelNames,
        $namespace = null,
        $subsystem = null,
        array $labels = array()
    ) {
        $options = new CounterOptions($name, $help, $namespace, $subsystem, $labels);

        return new CounterCollection($options, $labelNames);
    }

    /**
     * @param CounterOptions $options
     * @param string[]       $labels The names ot the label set for this collection.
     *
     * @return CounterCollection
     */
    public static function createFromOptions(CounterOptions $options, array $labels)
    {
        return new CounterCollection($options, $labels);
    }

    /**
     * {@inheritdoc}
     */
    protected function createMetricFromOptions(Options $options)
    {
        return Counter::createFromCounterOptions($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'counter';
    }
}
