<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Renderer.
 */

namespace Bexio\PrometheusPHP;

use GuzzleHttp\Stream\StreamInterface;

interface Renderer
{
    /**
     * Renders a single metric.
     *
     * @param MetricType      $metric
     * @param Sample[]        $samples
     * @param StreamInterface $stream
     */
    public function render(MetricType $metric, array $samples, StreamInterface $stream);
}
