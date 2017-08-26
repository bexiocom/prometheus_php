<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\MetricType\Gauge.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\Options;
use Bexio\PrometheusPHP\Type\Settable;
use Bexio\PrometheusPHP\Type\Subtractable;

/**
 * Gauge Metric
 *
 * Gauge is a Metric that represents a single numerical value that can arbitrarily go up and down.
 *
 * A Gauge is typically used for measured values like temperatures or current memory usage, but also "counts" that can
 * go up and down, like request durations.
 */
class Gauge implements Incrementable, Decrementable, Addable, Subtractable, Settable
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @param string   $name      The metric name
     * @param string   $help      The help information for this metric.
     * @param string   $namespace (optional) The metric namespace.
     * @param string   $subsystem (optional) The metric subsystem.
     * @param string[] $labels    (optional) Key value pairs of static metric labels.
     *
     * @return Gauge
     */
    public static function createFromValues($name, $help, $namespace = null, $subsystem = null, array $labels = array())
    {
        $options = new GaugeOptions($name, $help, $namespace, $subsystem, $labels);

        return new Gauge($options);
    }

    /**
     * @param GaugeOptions $options
     *
     * @return Gauge
     */
    public static function createFromOptions(GaugeOptions $options)
    {
        return new Gauge($options);
    }

    /**
     * Constructor.
     *
     * @param Options $options
     */
    private function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }
}
