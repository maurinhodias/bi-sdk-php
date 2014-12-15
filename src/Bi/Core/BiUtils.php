<?php namespace Bi\Core;

use Bi\Exception\BiNotInitializeException;
use Bi\Exception\BiLoadMetadataFailedException;

/**
 * Utility class for BI SDK.
 */
class BiUtils {

    /**
     * @var BiUtils The singleton instance of BiUtils.
     */
    private static $_instance = null;

    /**
     * @var string version of BI Spec.
     */
    private $_bi_version = null;

    /**
     * @var array BI's metadata.
     */
    private $_metadata = null;

    /**
     * @var array default configuration that can be overridden via the $config arg in BiUtils::initialize().
     */
    private $_defaults = array(
        'app_id' => null,                   // unique application ID
        'max_queue_size' => 512,            // max size for the cache queue
        'debug' => true,                    // enable/disable debug mode
        'environment' => 'development',     // "development"/"sandbox"/"production"
        'consumer_type' => null,            // consumer to use
        'consumer_options' => null,         // consumer's configuration
        'on_error' => null                  // callback on consumption failures
    );

    /**
     * @var array application configuration.
     */
    private $_config = array();

    /**
     * Construct a new BiUtils instance.
     *
     * @throws BiLoadMetadataFailedException
     */
    private function __construct($config, $consumer_type, $consumer_options = array()) {
        $this->_config = array_merge($this->_defaults, $config);
        $this->_config['consumer_type'] = $consumer_type;
        $this->_config['consumer_options'] = $consumer_options;

        try {
            $json = file_get_contents('http://config.funplusgame.com/get-bi-metadata');
            $bi_spec = json_decode($json, true);
            $this->_bi_version = $bi_spec['version'];
            $this->_metadata = $bi_spec['metadata'];

            if (!isset($bi_spec)) {
                throw new BiLoadMetadataFailedException('Missing version information in BI metadata.');
            }
        } catch (Exception $e) {
            throw new BiLoadMetadataFailedException('Failed to load BI metadata, please check if bi.json exists and its content is of valid JSON format.');
        }
    }

    /**
     * @return BiUtils The singleton instance of BiUtils.
     *
     * @throws BiNotInitializeException
     */
    public static function instance() {
        if (self::is_initialized()) {
            return self::$_instance;
        } else {
            throw new BiNotInitializeException('BiClient has not been initialized yet, please initialize it before calling BiClient::instance().');
        }
    }

    /**
     * @param array $options custom config values.
     */
    public static function initialize($config, $consumer_type, $consumer_options) {
        if (!self::is_initialized()) {
            self::$_instance = new BiUtils($config, $consumer_type, $consumer_options);
        }// else {
        //    throw new Exception('BI SDK has already been initialized.');
        //}
    }

    /**
     * @return boolean true if the BiUtils object has been initialized, otherwise false.
     */
    public static function is_initialized() {
        return isset(self::$_instance) &&
               isset(self::$_instance->_config['app_id']) &&
               isset(self::$_instance->_metadata);
    }

    /**
     * @param string $key the key to get config value.
     * @param mixed $default the default value to use if no config value found.
     *
     * @return mixed the config value.
     */
    public function get_config($key, $default = null) {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        return $default;
    }

    /**
     * @return array the full application configuration.
     */
    public function get_all_configs() {
        return $this->_config;
    }

    /**
     * @return string version of BI spec.
     */
    public function get_bi_version() {
        return $this->_bi_version;
    }

    /**
     * @param string $key the key to get metadata.
     *
     * @return mix metadata for $key or null.
     */
    public function get_meta($key) {
        if (isset($this->_metadata[$key])) {
            return $this->_metadata[$key];
        }
        return null;
    }
}
