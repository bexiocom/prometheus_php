<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Metric\HistogramCollection.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\MetricCollection;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\MetricTypeCollection;
use Bexio\PrometheusPHP\Options;

/**
 * Collection of Histogram metrics sharing the same set of labels.
 */
class HistogramCollection extends MetricCollection implements MetricTypeCollection
{
    /**
     * @param string   $name       The metric name.
     * @param string   $help       The help information for this metric.
     * @param string[] $labelNames The names of the label set for this collection.
     * @param float[]  $buckets    The buckets upper including bounds.
     * @param string   $namespace  (optional) The metric namespace.
     * @param string   $subsystem  (optional) The metric subsystem.
     * @param string[] $labels     (optional) Key value pairs of static metric labels.
     *
     * @return HistogramCollection
     */
    public static function createFromValues(
        $name,
        $help,
        array $labelNames,
        array $buckets,
        $namespace = null,
        $subsystem = null,
        array $labels = array()
    ) {
        $options = new HistogramOptions($name, $help, $buckets, $namespace, $subsystem, $labels);

        return new HistogramCollection($options, $labelNames);
    }

    /**
     * @param HistogramOptions $options
     * @param string[]         $labels The names ot the label set for this collection.
     *
     * @return HistogramCollection
     */
    public static function createFromOptions(HistogramOptions $options, array $labels)
    {
        return new HistogramCollection($options, $labels);
    }

    /**
     * Metric factory.
     *
     * @param Options $options
     *
     * @return MetricType
     */
    protected function createMetricFromOptions(Options $options)
    {
        return Histogram::createFromOptions($options);
    }

    /**
     * The metric type identifier.
     *
     * @return string
     */
    public function getType()
    {
        return 'histogram';
    }
}
