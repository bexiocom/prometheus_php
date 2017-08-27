<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\MetricCollection.
 */

namespace Bexio\PrometheusPHP;

use Bexio\PrometheusPHP\Exception\LabelMismatchException;
use Bexio\PrometheusPHP\Metric\Counter;
use Bexio\PrometheusPHP\Metric\CounterOptions;

/**
 * Base class for metric collections.
 *
 * @see MetricTypeCollection
 */
abstract class MetricCollection implements MetricTypeCollection
{
    /**
     * @var CounterOptions
     */
    protected $options;
    /**
     * @var Counter[]
     */
    protected $metrics = array();
    /**
     * @var string[]
     */
    protected $labels;

    /**
     * Constructor.
     *
     * @param CounterOptions $options
     * @param \string[]      $labels
     */
    protected function __construct(CounterOptions $options, array $labels)
    {
        $this->options = $options;
        $this->labels = $labels;
    }

    /**
     * Metric factory.
     *
     * @param Options $options
     *
     * @return MetricType
     */
    abstract protected function createMetricFromOptions(Options $options);

    /**
     * {@inheritdoc}
     */
    public function withLabels(array $labels)
    {
        $index = $this->getLabelsKey($labels);
        if (!isset($this->metrics[$index])) {
            $metricOptions = $this->options->withLabels($labels);
            $labelNames = array_keys($metricOptions->getLabels());
            if (array_diff($labelNames, $this->labels) || array_diff($this->labels, $labelNames)) {
                throw new LabelMismatchException(
                    'Can not get metric with label set differing from the one of the collection'
                );
            }

            $this->metrics[$index] = $this->createMetricFromOptions($metricOptions);
        }

        return $this->metrics[$index];
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $result = array();

        foreach ($this->metrics as $metric) {
            $result = array_merge($result, $metric->getActions());
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        foreach ($this->metrics as $metric) {
            $metric->clear();
        }
    }

    /**
     * Gets a unique identifier for the given labels set.
     *
     * @param string[] $labels
     *
     * @return string
     */
    protected function getLabelsKey(array $labels)
    {
        ksort($labels);

        return json_encode($labels);
    }
}
