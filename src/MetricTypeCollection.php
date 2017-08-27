<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\MetricTypeCollection.
 */

namespace Bexio\PrometheusPHP;

use Bexio\PrometheusPHP\Exception\LabelMismatchException;

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
     *
     * @throws LabelMismatchException The set of labels must match the one of the collection.
     *                                This exception is thrown if this constraint would be violated.
     */
    public function withLabels(array $labels);
}
