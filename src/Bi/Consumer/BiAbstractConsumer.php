<?php namespace Bi\Consumer;

use Bi\Core\BiUtils;
use DateTime;
use DateTimeZone;

/**
 * Base abstract consumer class.
 */
abstract class BiAbstractConsumer {

    /**
     * @var array Consumer settings.
     */
    protected $_options;

    /**
     * @var array Keys that consumer settings required.
     */
    protected $_option_keys;

    /**
     * Construct a new Consumer instance.
     *
     * @param array $options Consumer settings.
     */
    public function __construct($options = array()) {
        $this->_options = $options;
    }

    /**
     * @return string Desired path prefix.
     */
    protected function _get_desired_prefix() {
        $date = new DateTime(null, new DateTimeZone('UTC'));
        return sprintf('events/%s/%s', BiUtils::instance()->get_config('app_id'), $date->format('Y/m/d/H'));
    }

    /**
     * @param array $batch Array of messages to consume.
     * @return boolean Success or fail.
     */
    abstract public function persist($batch);

    /**
     * @return array Consumer settings.
     */
    public function get_options() {
        return $this->_options;
    }

    /**
     * Check if custom $options is valid.
     *
     * @param array $options Custom  setttings.
     */
    protected static function check_options($options) {
        foreach (self::$_option_keys as $key) {
            if (!array_key_exists($key, $options)) {
                return false;
            }
        }

        return true;
    }
}
