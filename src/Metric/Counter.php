<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\MetricType\Counter.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Incrementable;
use Bexio\PrometheusPHP\Options;

/**
 * Counter Metric
 *
 * Counter is a Metric that represents a single numerical value that only ever goes up. That implies that it cannot be
 * used to count items whose number can also go down, e.g. request durations. Those "counters"
 * are represented by Gauges.
 *
 * A Counter is typically used to count requests served, tasks completed, errors occurred, etc.
 */
class Counter implements Incrementable, Addable
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
     * @return Counter
     */
    public static function createFromValues($name, $help, $namespace = null, $subsystem = null, array $labels = array())
    {
        $options = new CounterOptions($name, $help, $namespace, $subsystem, $labels);

        return new Counter($options);
    }

    /**
     * @param CounterOptions $options
     *
     * @return Counter
     */
    public static function createFromCounterOptions(CounterOptions $options)
    {
        return new Counter($options);
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
