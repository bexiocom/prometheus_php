<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Storage\ArrayStorage.
 */

namespace Bexio\PrometheusPHP\Storage;

use Bexio\PrometheusPHP\Metric\HistogramOptions;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\MetricTypeCollection;
use Bexio\PrometheusPHP\Sample;
use Bexio\PrometheusPHP\StorageAdapter;

abstract class ArrayStorage implements StorageAdapter
{
    const DEFAULT_VALUE_INDEX = 'default';

    /**
     * Gets a unique identifier for the given options.
     *
     * @param MetricType $metric
     * @param float|null $value
     *
     * @return string
     */
    protected function getLabelsKey(MetricType $metric, $value = null)
    {
        $options = $metric->getOptions();
        $labels = $options->getLabels();
        if ($options instanceof HistogramOptions) {
            $buckets = $options->getBuckets();
            $bucket = '+Inf';
            foreach ($buckets as $le) {
                if ($value <= $le) {
                    $bucket = $le;
                    break;
                }
            }
            $labels = array_merge($labels, array('le' => $bucket));
        }
        ksort($labels);

        return empty($labels) ? ArrayStorage::DEFAULT_VALUE_INDEX : json_encode($labels);
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

        foreach ($this->getData($metric) as $key => $value) {
            $options = InMemory::DEFAULT_VALUE_INDEX == $key ?
                $metric->getOptions()
                : $metric->withLabels(json_decode($key, true))->getOptions();
            $result[] = Sample::createFromOptions($options, $value);
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
        $options = $metric->getOptions();
        $buckets = $options instanceof HistogramOptions
            ? array_merge($options->getBuckets(), array(PHP_INT_MAX))
            : array(null);
        $samples = array();
        $sum = null;
        $data = $this->getData($metric);
        foreach ($buckets as $le) {
            $labelsKey = $this->getLabelsKey($metric, $le);
            $value = isset($data[$labelsKey]) ? $data[$labelsKey] : null;

            $sum += $value;

            $samples[] = Sample::createFromOptions($options, $options instanceof HistogramOptions ? $sum : $value);
        }

        return $samples;
    }

    /**
     * Gets the data array for a metric.
     *
     * @param MetricType $metric
     *
     * @return float[]
     */
    abstract protected function getData(MetricType $metric);
}
