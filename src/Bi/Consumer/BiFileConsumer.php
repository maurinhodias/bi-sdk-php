<?php namespace Bi\Consumer;

use Bi\Consumer\BiAbstractConsumer;

class BiFileConsumer extends BiAbstractConsumer {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->_option_keys = array('output_dir');
    }

    /**
     * @param array $batch array of messages to consume.
     * @return boolean success or fail.
     */
    public function persist($batch) {
        if (count($batch) == 0) {
            return true;
        }

        $dir = rtrim($this->_options['output_dir'], '/');
        if (!is_dir($dir)) {
            return false;
        }

        $prefix = str_replace('/', '_', $this->_get_desired_prefix());
        $archive_index = 0;

        while (file_exists(sprintf('%s/%s_archive_%d.gz', $dir, $prefix, $archive_index))) {
            $archive_index += 1;
        }

        $body = gzencode(join("\n", $batch));
        return file_put_contents(sprintf('%s/%s_archive_%d.gz', $dir, $prefix, $archive_index), $body);
    }
}
