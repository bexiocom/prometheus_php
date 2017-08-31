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
        if ($options instanceof HistogramOptions && null !== $value) {
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

        return $this->getLabelsKeyFromArray($labels);
    }

    /**
     * Gets a unique identifier a set of labels.
     *
     * @param string[] $labels
     *
     * @return string
     */
    protected function getLabelsKeyFromArray(array $labels)
    {
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
        $metrics = array();

        foreach ($this->getKeys($metric) as $key) {
            $labels = array();
            if (InMemory::DEFAULT_VALUE_INDEX != $key) {
                $decoded = json_decode($key, true);
                unset($decoded['le']);
                $labels = $decoded;
            }

            $metrics[json_encode($labels)] = $labels;
        }

        foreach ($metrics as $labels) {
            $collectMetric = empty($labels) ? $metric : $metric->withLabels($labels);
            $result = array_merge($result, $this->collectSingleSamples($collectMetric));
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
            ? array_merge($options->getBuckets(), array('+Inf'))
            : array(null);
        $samples = array();
        $sum = null;
        $data = $this->getData($metric);
        foreach ($buckets as $le) {
            $labelsKey = $this->getLabelsKey($metric, '+Inf' == $le ? PHP_INT_MAX : $le);
            $value = isset($data[$labelsKey]) ? $data[$labelsKey] : null;

            $sum += $value;

            $collectOptions = $options;
            if ($options instanceof HistogramOptions) {
                $value = $sum;
                $collectOptions = $options->withLabels(array('le' => $le));
            }

            $samples[] = Sample::createFromOptions($collectOptions, $value);
        }

        if ($options instanceof HistogramOptions) {
            $sumData = $this->getData($metric, '_sum');
            $sumLabelsKey = $this->getLabelsKeyFromArray($options->getLabels());
            $sumValue = isset($sumData[$sumLabelsKey]) ? $sumData[$sumLabelsKey] : null;

            $samples[] = Sample::createFromValues(
                $options->getFullyQualifiedName().'_sum',
                $options->getLabels(),
                $sumValue
            );
            $samples[] = Sample::createFromValues(
                $options->getFullyQualifiedName().'_count',
                $options->getLabels(),
                $sum
            );
        }

        return $samples;
    }

    /**
     * Gets the data array for a metric.
     *
     * @param MetricType $metric
     * @param string     $suffix
     *
     * @return float[]
     */
    abstract protected function getData(MetricType $metric, $suffix = '');

    /**
     * Gets the value keys for a metric.
     *
     * @param MetricType $metric
     *
     * @return string[]
     */
    abstract protected function getKeys(MetricType $metric);
}
