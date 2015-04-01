<?php namespace Bi;

use Bi\Producer\BiProducer;
use Bi\Core\BiUtils;
use Bi\Exception\BiNotInitializeException;

/**
 * Client to interact with Funplus BI SDK
 */
class BiClient {

    const CURRENT_VERSION = '0.1.2';

    /**
     * @var BiClient The singleton instance of BiClient.
     */
    private static $_instance = null;

    /**
     * @var Producer the instance of event producer.
     */
    private $_producer;

    /**
     * Construct a new BiClient instance.
     */
    private function __construct() {
        if (self::is_initialized()) {
            $this->_producer = new BiProducer();
        }
    }

    /**
     * @return BiClient The singleton instance of BiClient.
     */
    public static function instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new BiClient();
        }
        return self::$_instance;
    }

    /**
     * @param string $config application configuration.
     * @param string $consumer_type the consumer we use.
     * @param array $consumer_options settings for consumer.
     *
     * @return boolean Success for failed.
     */
    public static function initialize($config, $consumer_type, $consumer_options) {
        if (!self::is_initialized()) {
            try {
                BiUtils::initialize($config, $consumer_type, $consumer_options);
                return true;
            } catch (BiLoadMetadataFailedException $e) {
                return false;
            }
        }
    }

    /**
     * @return boolean true if the sdk has been initialized, otherwise false.
     */
    public static function is_initialized() {
        return BiUtils::is_initialized();
    }

    /**
     * @param string $event the event name.
     * @param string $user_id the player's game id.
     * @param string $session_id the ongoing session's id.
     * @param string $properties the event's properties in JSON format.
     * @param string $collections the event's collections in JSON format, default is null.
     *
     * @return boolean true if succeeded to trace, otherwise false.
     */
    public function trace($event, $user_id, $session_id, $properties, $collections = null) {
        return $this->_producer->trace($event, $user_id, $session_id, $properties, $collections);
    }

    /**
     * Reset event producer.
     */
    public function reset() {
        $this->_producer->reset();
    }

    /**
     * @return Producer.
     */
    public function get_producer() {
        return $this->_producer;
    }

    /**
     * Trigger a message flushing manually.
     */
    public function flush() {
        $this->_producer->flush();
    }

    /**
     * @return string the version of BiClient sdk.
     */
    public static function version() {
        return self::CURRENT_VERSION;
    }

    public static function check_status() {
        if (self::is_initialized()) {
            print("BiClient SDK has been initialized.\n");
            var_dump(BiUtils::instance()->get_all_configs());
        } else {
            print("BiClient SDK has not been initialized yet.\n");
        }
    }
}
