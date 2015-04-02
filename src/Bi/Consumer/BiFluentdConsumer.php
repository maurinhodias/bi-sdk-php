<?php namespace Bi\Consumer;

use Bi\Core\BiUtils;
use Bi\Consumer\BiAbstractConsumer;
use Fluent\Logger\FluentLogger;
use Exception;

class BiFluentdConsumer extends BiAbstractConsumer {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->_option_keys = array('socket');
    }

    /**
     * @param array $batch array of messages to consume.
     *
     * @return boolean success or fail.
     */
    public function persist($batch) {
        if (count($batch) == 0) {
            return true;
        }

        $socket = $this->_options['socket'];
        $app_id = BiUtils::instance()->get_config('app_id');

        $logger = new FluentLogger($socket);

        foreach ($batch as $msg) {
            $arr = json_decode($msg, true);
            if (empty($arr['collections'])) {
                $arr['collections'] = '{}';
            }
            $logger->post($app_id, $arr);
        }

        return true;
    }
}
