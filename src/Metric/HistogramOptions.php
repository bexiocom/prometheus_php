<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Metric\HistogramOptions.
 */

namespace Bexio\PrometheusPHP\Metric;

use Bexio\PrometheusPHP\Options;

/**
 * @see Options
 */
class HistogramOptions extends Options
{
    /**
     * Bucket boundaries.
     *
     * Buckets defines the buckets into which observations are counted. Each element in the slice is the upper inclusive
     * bound of a bucket. There is no need to add a highest bucket with +Inf bound, it will be added implicitly.
     *
     * @var float[]
     */
    private $buckets;

    /**
     * {@inheritdoc}
     * @param float[] $buckets The upper including bucket boundaries.
     */
    public function __construct(
        $name,
        $help,
        array $buckets = null,
        $namespace = null,
        $subsystem = null,
        $constantLabels = array()
    ) {
        parent::__construct($name, $help, $namespace, $subsystem, $constantLabels);
        $buckets = null === $buckets ? $this->getDefaultBuckets() : $buckets;
        sort($buckets);
        $this->buckets = $buckets;
    }

    /**
     * @return \float[]
     */
    public function getBuckets()
    {
        return $this->buckets;
    }

    /**
     * The default buckets.
     *
     * @return float[]
     */
    public function getDefaultBuckets()
    {
        return array(0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10);
    }
}
