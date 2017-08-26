<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\MetricTypeCollection.
 */

namespace Bexio\PrometheusPHP;

/**
 * Collection of metrics sharing the same name and set of label names.
 *
 * MetricTypeCollection bundles a set of Metrics that all share the same description, but have different values for
 * their variable labels. This is used if you want to measure the same thing partitioned by various dimensions (e.g.
 * number of HTTP requests, partitioned by response code and method).
 */
interface MetricTypeCollection extends MetricType
{
    /**
     * Gets a metric for the given labels.
     *
     * @param string[] $labels
     *
     * @return MetricType
     */
    public function withLabels(array $labels);
}
