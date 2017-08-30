<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Output\TextRenderer.
 */

namespace Bexio\PrometheusPHP\Output;

use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\Renderer;
use Bexio\PrometheusPHP\Sample;
use GuzzleHttp\Stream\StreamInterface;

/**
 * Renders the samples in the Prometheus text format.
 *
 * @see https://prometheus.io/docs/instrumenting/exposition_formats/
 */
class TextRenderer implements Renderer
{
    const MIME_TYPE = 'text/plain; version=0.0.4';

    const METADATA = <<<EOF
# HELP %s %s
# TYPE %1\$s %s

EOF;
    /**
     * {@inheritdoc}
     */
    public function render(MetricType $metric, array $samples, StreamInterface $stream)
    {
        $options = $metric->getOptions();
        $stream->write(sprintf(
            TextRenderer::METADATA,
            $options->getFullyQualifiedName(),
            $options->getHelp(),
            $metric->getType()
        ));

        foreach ($samples as $sample) {
            $stream->write($this->getSampleLine($sample));
        }
    }

    /**
     * @param Sample $sample
     *
     * @return string
     */
    private function getLabelsString(Sample $sample)
    {
        $labels = array();
        foreach ($sample->getLabels() as $labelName => $labelValue) {
            $labels[] = sprintf('"%s"="%s"', $labelName, $labelValue);
        }

        return empty($labels) ? '' : sprintf('{%s}', implode(',', $labels));
    }

    /**
     * @param Sample $sample
     *
     * @return string
     */
    private function getSampleLine(Sample $sample)
    {
        return sprintf("%s%s %s\n", $sample->getName(), $this->getLabelsString($sample), $sample->getValue());
    }
}
