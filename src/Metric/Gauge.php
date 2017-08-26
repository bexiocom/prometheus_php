<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\MetricType\Gauge.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\Action\Add;
use Bexio\PrometheusPHP\Action\Decrement;
use Bexio\PrometheusPHP\Action\Increment;
use Bexio\PrometheusPHP\Action\Set;
use Bexio\PrometheusPHP\Action\Subtract;
use Bexio\PrometheusPHP\Metric;
use Bexio\PrometheusPHP\Type\Addable;
use Bexio\PrometheusPHP\Type\Decrementable;
use Bexio\PrometheusPHP\Type\Incrementable;
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
class Gauge extends Metric implements Incrementable, Decrementable, Addable, Subtractable, Settable
{
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
     * {@inheritdoc}
     */
    public function inc()
    {
        $this->actions[] = Increment::createFromValue($this);
    }

    /**
     * {@inheritdoc}
     */
    public function dec()
    {
        $this->actions[] = Decrement::createFromValue($this);
    }

    /**
     * {@inheritdoc}
     */
    public function add($value)
    {
        $this->actions[] = Add::createFromValue($this, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function sub($value)
    {
        $this->actions[] = Subtract::createFromValue($this, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        $this->actions[] = Set::createFromValue($this, $value);
    }
}
