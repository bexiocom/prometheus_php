<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Metric\Histogram.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\Action\Observe;
use Bexio\PrometheusPHP\Metric;
use Bexio\PrometheusPHP\Type\Observable;

/**
 * Histogram Metric
 *
 * A Histogram counts individual observations from an event or sample stream in configurable buckets. Similar to a
 * summary, it also provides a sum of observations and an observation count.
 *
 * On the Prometheus server, quantiles can be calculated from a Histogram using the histogram_quantile function in the
 * query language.
 *
 * Note that Histograms, in contrast to Summaries, can be aggregated with the Prometheus query language (see the
 * documentation for detailed procedures). However, Histograms require the user to pre-define suitable buckets, and
 * they are in general less accurate. The Observe method of a Histogram has a very low performance overhead in
 * comparison with the Observe method of a Summary.
 */
class Histogram extends Metric implements Observable
{
    /**
     * @param string   $name      The metric name
     * @param string   $help      The help information for this metric.
     * @param float[]  $buckets   The buckets upper including bounds.
     * @param string   $namespace (optional) The metric namespace.
     * @param string   $subsystem (optional) The metric subsystem.
     * @param string[] $labels    (optional) Key value pairs of static metric labels.
     *
     * @return Histogram
     */
    public static function createFromValues($name, $help, array $buckets = null, $namespace = null, $subsystem = null, array $labels = array())
    {
        $options = new HistogramOptions($name, $help, $buckets, $namespace, $subsystem, $labels);

        return new Histogram($options);
    }

    /**
     * @param HistogramOptions $options
     *
     * @return Histogram
     */
    public static function createFromOptions(HistogramOptions $options)
    {
        return new Histogram($options);
    }

    /**
     * Observes a value.
     *
     * @param float $value
     */
    public function observe($value)
    {
        $this->actions[] = Observe::createFromValue($this, $value);
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
