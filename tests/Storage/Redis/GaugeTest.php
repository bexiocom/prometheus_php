<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\InMemory\GaugeTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\Redis;

use Bexio\PrometheusPHP\Metric\Gauge;
use Bexio\PrometheusPHP\Metric\GaugeCollection;
use Bexio\PrometheusPHP\Sample;
use Bexio\PrometheusPHP\Storage\InMemory;
use Bexio\PrometheusPHP\Storage\Redis;

class GaugeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemory
     */
    private $subject;

    /**
     * @var \Redis
     */
    private $redis;

    protected function setUp()
    {
        $this->redis = new \Redis();
        $this->redis->connect('localhost');
        $this->redis->hSet('phpunit:foo_bar_baz', 'default', 3);
        $this->redis->hSet('phpunit:foo_bar_baz', '{"foo":"bar"}', 5);
        $this->redis->hSet('phpunit:foo_bar_baz', '{"foo":"baz"}', 7);
        $this->subject = new Redis($this->redis, 'phpunit:');
    }

    public function tearDown()
    {
        $this->redis->eval("return redis.call('del', unpack(redis.call('keys', ARGV[1])))", array('phpunit:*'));
    }

    /**
     * @return array
     */
    public function getIncrementData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 4),
            array(GaugeTest::getLabeledGauge(), 6),
            array(Gauge::createFromValues('foo', null), 1),
        );
    }

    /**
     * @param Gauge   $metric
     * @param         $expected
     *
     * @dataProvider getIncrementData
     */
    public function testIncrement(Gauge $metric, $expected)
    {
        $this->subject->inc($metric);
        $samples = $this->subject->collectSamples($metric);
        $sample = reset($samples);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getDecrementData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 2),
            array(GaugeTest::getLabeledGauge(), 4),
            array(Gauge::createFromValues('foo', null), -1),
        );
    }

    /**
     * @param Gauge   $metric
     * @param         $expected
     *
     * @dataProvider getDecrementData
     */
    public function testDecrement(Gauge $metric, $expected)
    {
        $metric->dec();
        $this->subject->persist($metric);
        $samples = $this->subject->collectSamples($metric);
        $sample = reset($samples);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getAdditionData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 5, 8),
            array(GaugeTest::getLabeledGauge(), 7, 12),
            array(Gauge::createFromValues('foo', null), 3, 3),
        );
    }

    /**
     * @param Gauge $metric
     * @param float $value
     * @param float $expected
     *
     * @dataProvider getAdditionData
     */
    public function testAddition(Gauge $metric, $value, $expected)
    {
        $metric->add($value);
        $this->subject->persist($metric);
        $samples = $this->subject->collectSamples($metric);
        $sample = reset($samples);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSubtractionData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 5, -2),
            array(GaugeTest::getLabeledGauge(), 3, 2),
            array(Gauge::createFromValues('foo', null), 7, -7),
        );
    }

    /**
     * @param Gauge $metric
     * @param float $value
     * @param float $expected
     *
     * @dataProvider getSubtractionData
     */
    public function testSubtraction(Gauge $metric, $value, $expected)
    {
        $metric->sub($value);
        $this->subject->persist($metric);
        $samples = $this->subject->collectSamples($metric);
        $sample = reset($samples);
        $this->assertEquals($expected, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSetterData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 5),
            array(GaugeTest::getLabeledGauge(), 3),
            array(Gauge::createFromValues('foo', null), 7),
        );
    }

    /**
     * @param Gauge $metric
     * @param float $value
     *
     * @dataProvider getSetterData
     */
    public function testSetter(Gauge $metric, $value)
    {
        $metric->set($value);
        $this->subject->persist($metric);
        $samples = $this->subject->collectSamples($metric);
        $sample = reset($samples);
        $this->assertEquals($value, $sample->getValue());
    }

    /**
     * @return array
     */
    public function getSamplesData()
    {
        return array(
            array(GaugeTest::getDefaultGauge(), 'foo_bar_baz', array(), 3),
            array(GaugeTest::getLabeledGauge(), 'foo_bar_baz', array('foo' => 'bar'), 5),
            array(Gauge::createFromValues('foo', null), 'foo', array(), null),
        );
    }

    /**
     * @param Gauge    $metric
     * @param string   $name
     * @param string[] $labels
     * @param float    $value
     *
     * @dataProvider getSamplesData
     */
    public function testSamples(Gauge $metric, $name, array $labels, $value)
    {
        $samples = $this->subject->collectSamples($metric);
        $sample = reset($samples);
        $this->assertEquals($name, $sample->getName());
        $this->assertEquals($labels, $sample->getLabels());
        $this->assertEquals($value, $sample->getValue());
    }

    /**
     * Test retrieval of collection samples.
     */
    public function testCollectionSamples()
    {
        $collection = GaugeCollection::createFromValues('baz', 'Just a counter collection for testing', array(
            'foo',
        ), 'foo', 'bar');

        $samples = $this->subject->collectSamples($collection);

        $this->assertEquals(array(
            Sample::createFromOptions($collection->getOptions(), 3),
            Sample::createFromOptions($collection->getOptions()->withLabels(array('foo' => 'bar')), 5),
            Sample::createFromOptions($collection->getOptions()->withLabels(array('foo' => 'baz')), 7),
        ), $samples);
    }

    /**
     * Tests persisting of a metric collection.
     */
    public function testPersistCollection()
    {
        $collection = GaugeCollection::createFromValues('baz', 'Just a counter collection for testing', array(
            'foo',
        ), 'foo', 'bar');

        $foo = $collection->withLabels(array('foo' =>'foo'));
        $foo->sub(3);

        $bar = $collection->withLabels(array('foo' =>'bar'));
        $bar->sub(3);

        $baz = $collection->withLabels(array('foo' => 'baz'));
        $baz->sub(3);

        $this->subject->persist($collection);

        $fooSamples = $this->subject->collectSamples($foo);
        $barSamples = $this->subject->collectSamples($bar);
        $bazSamples = $this->subject->collectSamples($baz);
        $this->assertEquals(-3, reset($fooSamples)->getValue());
        $this->assertEquals(2, reset($barSamples)->getValue());
        $this->assertEquals(4, reset($bazSamples)->getValue());
    }

    /**
     * @return Gauge
     */
    private static function getDefaultGauge()
    {
        return Gauge::createFromValues('baz', null, 'foo', 'bar');
    }

    /**
     * @return Gauge
     */
    private static function getLabeledGauge()
    {
        return Gauge::createFromValues('baz', null, 'foo', 'bar', array(
            'foo' => 'bar',
        ));
    }
}
