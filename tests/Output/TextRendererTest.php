<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Output\TextRendererTest.
 */

namespace Bexio\PrometheusPHP\Tests\Output;

use Bexio\PrometheusPHP\Metric\Gauge;
use Bexio\PrometheusPHP\Metric\GaugeCollection;
use Bexio\PrometheusPHP\Metric\Histogram;
use Bexio\PrometheusPHP\Metric\HistogramCollection;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\Output\TextRenderer;
use Bexio\PrometheusPHP\Storage\ArrayStorage;
use Bexio\PrometheusPHP\Storage\InMemory;
use GuzzleHttp\Stream\BufferStream;

class TextRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TextRenderer
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new TextRenderer();
    }

    public function getRenderData()
    {
        return array(
            'Simple metric' => array(
                Gauge::createFromValues('foo', 'Pharetra Dapibus'),
                array(
                    'foo' => array(InMemory::DEFAULT_VALUE_INDEX => 3)
                ),
                <<<EOF
# HELP foo Pharetra Dapibus
# TYPE foo gauge
foo 3

EOF
            ),
            'Pick correct values with namespace' => array(
                Gauge::createFromValues('bar', 'Pharetra Dapibus', 'foo'),
                array(
                    'foo' => array(InMemory::DEFAULT_VALUE_INDEX => 3),
                    'foo_bar' => array(InMemory::DEFAULT_VALUE_INDEX => 5),
                ),
                <<<EOF
# HELP foo_bar Pharetra Dapibus
# TYPE foo_bar gauge
foo_bar 5

EOF
            ),
            'Pick correct values with namespace and subsystem' => array(
                Gauge::createFromValues('baz', 'Pharetra Dapibus', 'foo', 'bar'),
                array(
                    'foo' => array(InMemory::DEFAULT_VALUE_INDEX => 3),
                    'foo_bar_baz' => array(InMemory::DEFAULT_VALUE_INDEX => 5),
                ),
                <<<EOF
# HELP foo_bar_baz Pharetra Dapibus
# TYPE foo_bar_baz gauge
foo_bar_baz 5

EOF
            ),
            'Collection' => array(
                GaugeCollection::createFromValues('foo', 'Pharetra Dapibus', array('foo')),
                array(
                    'foo' => array('{"foo":"bar"}' => 3, '{"foo":"baz"}' => 5),
                ),
                <<<EOF
# HELP foo Pharetra Dapibus
# TYPE foo gauge
foo{foo="bar"} 3
foo{foo="baz"} 5

EOF
            ),
            'Collection with default index' => array(
                GaugeCollection::createFromValues('foo', 'Pharetra Dapibus', array('foo')),
                array(
                    'foo' => array(
                        InMemory::DEFAULT_VALUE_INDEX => 7,
                        '{"foo":"bar"}' => 3,
                        '{"foo":"baz"}' => 5,
                    ),
                ),
                <<<EOF
# HELP foo Pharetra Dapibus
# TYPE foo gauge
foo 7
foo{foo="bar"} 3
foo{foo="baz"} 5

EOF
            ),
            'Simple Histogram' => array(
                Histogram::createFromValues('foo', 'Pharetra Dapibus', array(1, 10)),
                array(
                    'foo' => array(
                        '{"le":1}' => 7,
                        '{"le":10}' => 5,
                        '{"le":"+Inf"}' => 3,
                    ),
                    'foo_sum' => array(
                        ArrayStorage::DEFAULT_VALUE_INDEX => 228.5
                    )
                ),
                <<<EOF
# HELP foo Pharetra Dapibus
# TYPE foo histogram
foo_bucket{le="1"} 7
foo_bucket{le="10"} 12
foo_bucket{le="+Inf"} 15
foo_sum 228.5
foo_count 15

EOF
            ),
            'Histogram Collection' => array(
                HistogramCollection::createFromValues('foo', 'Pharetra Dapibus', array('foo'), array(1, 10)),
                array(
                    'foo' => array(
                        '{"le":1}' => 7,
                        '{"le":10}' => 5,
                        '{"le":"+Inf"}' => 3,
                        '{"foo":"bar","le":1}' => 11,
                        '{"foo":"bar","le":10}' => 7,
                        '{"foo":"bar","le":"+Inf"}' => 5,
                    ),
                    'foo_sum' => array(
                        ArrayStorage::DEFAULT_VALUE_INDEX => 228.5,
                        '{"foo":"bar"}' => 467,
                    ),
                ),
                <<<EOF
# HELP foo Pharetra Dapibus
# TYPE foo histogram
foo_bucket{le="1"} 7
foo_bucket{le="10"} 12
foo_bucket{le="+Inf"} 15
foo_sum 228.5
foo_count 15
foo_bucket{foo="bar",le="1"} 11
foo_bucket{foo="bar",le="10"} 18
foo_bucket{foo="bar",le="+Inf"} 23
foo_sum{foo="bar"} 467
foo_count{foo="bar"} 23

EOF
            ),
        );
    }

    /**
     * @param MetricType $metric
     * @param float[]    $data
     * @param string     $output
     *
     * @dataProvider getRenderData
     */
    public function testRender(MetricType $metric, array $data, $output)
    {
        $buffer = new BufferStream();
        $storage = new InMemory($data);
        $this->subject->render($metric, $storage->collectSamples($metric), $buffer);
        $this->assertEquals($output, $buffer->getContents());
    }
}
