<?php
/**
 * @file
 * Contains Bexio\PrometheusPHP\Tests\Storage\Redis\ExceptionTest.
 */

namespace Bexio\PrometheusPHP\Tests\Storage\Redis;

use Bexio\PrometheusPHP\Exception\StorageException;
use Bexio\PrometheusPHP\Metric\Gauge;
use Bexio\PrometheusPHP\Metric\GaugeCollection;
use Bexio\PrometheusPHP\Metric\Histogram;
use Bexio\PrometheusPHP\Storage\ArrayStorage;
use Bexio\PrometheusPHP\Storage\Redis;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Redis|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redis;

    protected function setUp()
    {
        $this->redis = $this->getMock(\Redis::class);
    }

    /**
     * @return array
     */
    public function getExceptionsForOperationsData()
    {
        return array(
            'increment' => array('inc', 'hIncrByFloat', 'Failed to increment metric value'),
            'decrement' => array('dec', 'hIncrByFloat', 'Failed to decrement metric value'),
            'add' => array('add', 'hIncrByFloat', 'Failed to add metric value'),
            'subtract' => array('sub', 'hIncrByFloat', 'Failed to subtract metric value'),
            'set' => array('set', 'hSet', 'Failed to set metric value'),
            'observe' => array('observe', 'eval', 'Failed to update Histogram'),
            'collect' => array('collectSamples', 'hGetAll', 'Failed to collect metric samples'),
        );
    }

    /**
     * @param string $call    The method called on the storage.
     * @param string $method  The method called on Redis.
     * @param string $message The expected error message.
     *
     * @dataProvider getExceptionsForOperationsData
     */
    public function testExceptionsForOperations($call, $method, $message)
    {
        $subject = new Redis($this->redis);
        $this->redis->expects($this->any())
            ->method($method)
            ->willThrowException(new \Exception());
        $metric = 'observe' != $call
            ? Gauge::createFromValues('foo', 'bar')
            : Histogram::createFromValues('foo', 'bar');
        $this->setExpectedException(StorageException::class, $message);
        $subject->$call($metric, 3);
    }

    /**
     * Tests exception when failed to collect keys on collections.
     */
    public function testExceptionForKeyRetrieval()
    {
        $subject = new Redis($this->redis);
        $this->redis->expects($this->once())
            ->method('hKeys')
            ->willThrowException(new \Exception());
        $metric = GaugeCollection::createFromValues('foo', 'bar', array('baz'));
        $this->setExpectedException(StorageException::class, 'Failed to collect metric');
        $subject->collectSamples($metric);
    }

    /**
     * Tests exception when failed to collect data on collections.
     */
    public function testExceptionForDataRetrieval()
    {
        $subject = new Redis($this->redis);
        $this->redis->expects($this->once())
            ->method('hKeys')
            ->willReturn(array(ArrayStorage::DEFAULT_VALUE_INDEX));
        $this->redis->expects($this->once())
            ->method('hGetAll')
            ->willThrowException(new \Exception());
        $metric = GaugeCollection::createFromValues('foo', 'bar', array('baz'));
        $this->setExpectedException(StorageException::class, 'Failed to collect metric');
        $subject->collectSamples($metric);
    }

    public function getOpenConnectionData()
    {
        return array(
            'increment' => array('inc'),
            'decrement' => array('dec'),
            'add' => array('add'),
            'subtract' => array('sub'),
            'set' => array('set'),
            'collect' => array('collectSamples'),
        );
    }

    /**
     * @param string $call The method called on the storage.
     *
     * @dataProvider getOpenConnectionData
     *
     * @expectedException \Bexio\PrometheusPHP\Exception\StorageException
     * @expectedExceptionMessage Failed to connect to Redis
     */
    public function testOpenConnection($call)
    {
        $subject = new Redis($this->redis, 'phpunit:', function () {
            throw new \Exception();
        });
        $metric = Gauge::createFromValues('foo', 'bar');
        $subject->$call($metric, 3);
    }
}
