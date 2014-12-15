<?php

require_once(dirname(__FILE__) . '/../vendor/autoload.php');

use Bi\BiClient;
use Bi\Core\BiUtils;

class BiUtilsTest extends PHPUnit_Framework_TestCase {
    private $_instance;

    protected function setUp() {
        parent::setUp();
        BiClient::initialize(array('app_id' => 'com.funplus.daota'), 'Bi\Consumer\BiTestingConsumer', array());
        $this->_instance = BiUtils::instance();
    }

    protected function tearDown() {
        parent::tearDown();
        $this->_instance = null;
    }

    public function testInitialize() {
        $this->assertInstanceOf('Bi\Core\BiUtils', $this->_instance);
        $this->assertTrue($this->_instance->is_initialized());
    }

    public function testGetConfig() {
        $this->assertEquals('com.funplus.daota', $this->_instance->get_config('app_id'));
        $this->assertNull($this->_instance->get_config('wrong_key'));
    }

    public function testGetAllConfigs() {
        $configs = $this->_instance->get_all_configs();
        $this->assertTrue(is_array($configs));
        $this->assertArrayHasKey('app_id', $configs);
        $this->assertArrayHasKey('consumer_type', $configs);
        $this->assertEquals('com.funplus.daota', $configs['app_id']);
    }

    public function testGetMeta() {
        $this->assertTrue(is_array($this->_instance->get_meta('item')));
        $this->assertTrue(is_array($this->_instance->get_meta('event')));
        $this->assertTrue(is_array($this->_instance->get_meta('resource')));
        $this->assertTrue(is_array($this->_instance->get_meta('property')));
        $this->assertTrue(is_array($this->_instance->get_meta('attribute')));
        $this->assertTrue(is_array($this->_instance->get_meta('collection')));
    }

    public function testGetBiVersion() {
        $this->assertTrue(is_string($this->_instance->get_bi_version()));
    }
}
