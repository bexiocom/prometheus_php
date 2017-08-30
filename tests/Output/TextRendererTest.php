<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Output\TextRendererTest.
 */

namespace Bexio\PrometheusPHP\Tests\Output;

use Bexio\PrometheusPHP\Metric\Gauge;
use Bexio\PrometheusPHP\Metric\GaugeCollection;
use Bexio\PrometheusPHP\MetricType;
use Bexio\PrometheusPHP\Output\TextRenderer;
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
foo{"foo"="bar"} 3
foo{"foo"="baz"} 5

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
foo{"foo"="bar"} 3
foo{"foo"="baz"} 5

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
