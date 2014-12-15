<?php namespace Bi\Consumer;

use Bi\Consumer\BiAbstractConsumer;

class BiTestingConsumer extends BiAbstractConsumer {

    /**
     * @param array $batch array of messages to consume.
     * @return boolean success or fail.
     */
    public function persist($batch) {
        return true;
    }
}
