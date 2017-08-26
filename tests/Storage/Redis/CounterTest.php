<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\InMemory\CounterTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\Redis;

use Bexio\PrometheusPHP\Metric\Counter;
use Bexio\PrometheusPHP\Metric\CounterCollection;
use Bexio\PrometheusPHP\Sample;
use Bexio\PrometheusPHP\Storage\InMemory;
use Bexio\PrometheusPHP\Storage\Redis;

class CounterTest extends \PHPUnit_Framework_TestCase
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
            array(CounterTest::getDefaultCounter(), 4),
            array(CounterTest::getLabeledCounter(), 6),
            array(Counter::createFromValues('foo', null), 1),
        );
    }

    /**
     * @param Counter $metric
     * @param         $expected
     *
     * @dataProvider getIncrementData
     */
    public function testIncrement(Counter $metric, $expected)
    {
        $metric->inc();
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
            array(CounterTest::getDefaultCounter(), 5, 8),
            array(CounterTest::getLabeledCounter(), 7, 12),
            array(Counter::createFromValues('foo', null), 3, 3),
        );
    }

    /**
     * @param Counter $metric
     * @param float   $value
     * @param float   $expected
     *
     * @dataProvider getAdditionData
     */
    public function testAddition(Counter $metric, $value, $expected)
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
    public function getSamplesData()
    {
        return array(
            array(CounterTest::getDefaultCounter(), 'foo_bar_baz', array(), 3),
            array(CounterTest::getLabeledCounter(), 'foo_bar_baz', array('foo' => 'bar'), 5),
            array(Counter::createFromValues('foo', null), 'foo', array(), null),
        );
    }

    /**
     * @param Counter  $metric
     * @param string   $name
     * @param string[] $labels
     * @param float    $value
     *
     * @dataProvider getSamplesData
     */
    public function testSamples(Counter $metric, $name, $labels, $value)
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
        $collection = CounterCollection::createFromValues('baz', 'Just a counter collection for testing', array(
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
        $collection = CounterCollection::createFromValues('baz', 'Just a counter collection for testing', array(
            'foo',
        ), 'foo', 'bar');

        $foo = $collection->withLabels(array('foo' => 'foo'));
        $foo->inc();

        $bar = $collection->withLabels(array('foo' =>'bar'));
        $bar->inc();

        $baz = $collection->withLabels(array('foo' => 'baz'));
        $baz->inc();

        $this->subject->persist($collection);

        $fooSamples = $this->subject->collectSamples($foo);
        $barSamples = $this->subject->collectSamples($bar);
        $bazSamples = $this->subject->collectSamples($baz);
        $this->assertEquals(1, reset($fooSamples)->getValue());
        $this->assertEquals(6, reset($barSamples)->getValue());
        $this->assertEquals(8, reset($bazSamples)->getValue());
    }

    /**
     * @return Counter
     */
    private static function getDefaultCounter()
    {
        return Counter::createFromValues('baz', null, 'foo', 'bar');
    }

    /**
     * @return Counter
     */
    private static function getLabeledCounter()
    {
        return Counter::createFromValues('baz', null, 'foo', 'bar', array(
            'foo' => 'bar',
        ));
    }
}
