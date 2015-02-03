<?php namespace Bi\Producer;

use Bi\Core\BiUtils;
use Bi\Core\BiEvent;
use Bi\Consumer\BiAbstractConsumer;
use Bi\Consumer\BiS3Consumer;
use Bi\Consumer\BiFileConsumer;
use Bi\Consumer\BiFunplusGlobalFrontEndConsumer;
use Bi\Consumer\BiTestingConsumer;
use Bi\Exception\BiConsumerNotExistException;
use Bi\Exception\BiEventSerializeFailedException;

/**
 * The event producer.
 */
class BiProducer {

    /**
     * @var array Cache queue to hold event messages in memory before flushing in batches.
     */
    private $_queue = array();

    /**
     * @var BiAbstractConsumer The consumer to persist events.
     */
    private $_consumer = null;

    /**
     * @var array build-in consumer types.
     */
    private static $_consumer_types = array(
        'Bi\\Consumer\\BiS3Consumer',
        'Bi\\Consumer\\BiFileConsumer',
        'Bi\\Consumer\\BiTestingConsumer',
        'Bi\\Consumer\\BiFunplusGlobalFrontEndConsumer'
    );

    /**
     * Construct a new BiProducer instance.
     *
     * @throws BiConsumerNotExistException
     */
    public function __construct() {
        $Consumer = BiUtils::instance()->get_config('consumer_type');
        if (!in_array($Consumer, self::$_consumer_types)) {
            throw new BiConsumerNotExistException('The given consumer type does not exists.');
        }

        $this->_consumer = new $Consumer(BiUtils::instance()->get_config('consumer_options'));
    }

    /**
     * The queue need to be flushed when we destruct the client.
     */
    public function __destruct() {
        $this->flush();
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
        $event = new BiEvent($event, $user_id, $session_id, $properties, $collections);
        try {
            $message = $event->serialize();
            $this->_enqueue($message);
            return true;
        } catch (BiEventSerializeFailedException $e) {
            $error_func = BiUtils::instance()->get_config('on_error');
            if (isset($error_func)) {
                call_user_func($error_func, 'Trace event failed.');
            }
            return false;
        }
    }

    /**
     * Flush the cache queue.
     *
     * @return boolean If succeeded return true, otherwise return false.
     */
    public function flush() {
        $queue_size = count($this->_queue);

        while ($queue_size > 0) {
            $batch_size = min(array($queue_size, BiUtils::instance()->get_config('max_queue_size')));
            $batch = array_splice($this->_queue, 0, $batch_size);
            $succeeded = $this->_consumer->persist($batch);

            if (!$succeeded) {
                $this->_queue = array_merge($batch, $this->_queue);
                $error_func = BiUtils::instance()->get_config('on_error');
                if (isset($error_func)) {
                    call_user_func($error_func, 'Event consumption failed.');
                }
                return false;
            }

            $queue_size = count($this->_queue);
        }

        return true;
    }

    /**
     * Empty the queue without persisting any of the messages.
     */
    public function reset() {
        $this->_queue = array();
    }

    /**
     * @return integer queue size.
     */
    public function queue_size() {
        return count($this->_queue);
    }

    /**
     * @param string $message The event message.
     */
    private function _enqueue($message) {
        array_push($this->_queue, $message);

        if (count($this->_queue) >= BiUtils::instance()->get_config('max_queue_size')) {
            // trigger consumer to persist data.
            $this->flush(BiUtils::instance()->get_config('max_queue_size'));
        }
    }

    /**
     * @return mixed
     */
    public function test_error_callback() {
        $error_func = BiUtils::instance()->get_config('on_error');
        if (isset($error_func)) {
            return call_user_func($error_func, 'test_error_callback');
        } else {
            return false;
        }
    }
}
