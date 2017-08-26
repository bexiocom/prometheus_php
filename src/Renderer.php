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
     * Factory to create a renderer instance.
     *
     * @param StreamInterface $stream
     *
     * @return Renderer
     */
    public static function createFromStream(StreamInterface $stream);

    /**
     * Renders a single metric.
     *
     * @param MetricType $metric
     * @param Sample[]   $samples
     */
    public function render(MetricType $metric, array $samples);
}
