<?php

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

use Bi\BiClient;

class BiClientTest extends PHPUnit_Framework_TestCase {

    private $_instance;

    protected function setUp() {
        parent::setUp();
        BiClient::initialize(array('app_id' => 'com.funplus.daota'), 'Bi\Consumer\BiTestingConsumer', array());
        $this->_instance = BiClient::instance();
    }

    protected function tearDown() {
        parent::tearDown();
        $this->_instance = null;
    }

    public function testInitialize() {
        $this->assertInstanceOf('Bi\BiClient', $this->_instance);
        $this->assertEquals(true, BiClient::is_initialized());
    }

    public function testGetProducer() {
        $this->assertInstanceOf('Bi\Producer\BiProducer', $this->_instance->get_producer());
    }

    public function testTraceEvent() {
        $producer = $this->_instance->get_producer();

        for ($i = 0; $i < 10; $i++) {
            $this->_instance->trace(
                'new_user',
                '1001',
                '12345',
                '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.   1", "lang": "en", "level": 11, "install_ts": "123456"}'
            );
        }

        $this->assertEquals(10, $producer->queue_size());
    }

    public function testReset() {
        $producer = $this->_instance->get_producer();

        for ($i = 0; $i < 10; $i++) {
            $this->_instance->trace(
                'new_user',
                '1001',
                '12345',
                '{"app_version": "1.2", "os": "iOS", "os_version": "8.1", "ip": "192.168.0.   1", "lang": "en", "level": 11, "install_ts": "123456"}'
            );
        }

        $this->_instance->reset();
        $this->assertEquals(0, $producer->queue_size());
    }

    public function testGetVersion() {
        $version = BiClient::version();
        $this->assertEquals('0.1-dev', $version);
    }
}
