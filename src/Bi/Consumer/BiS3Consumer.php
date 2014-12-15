<?php namespace Bi\Consumer;

use Bi\Consumer\BiAbstractConsumer;
use Aws\S3\S3Client;
use Exception;

class BiS3Consumer extends BiAbstractConsumer {

    public function __construct($options = array()) {
        parent::__construct($options);
        $this->_option_keys = array('s3_bucket', 's3_key', 's3_secret', 's3_region');
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

        $bucket = $this->_options['s3_bucket'];
        $key = $this->_options['s3_key'];
        $secret = $this->_options['s3_secret'];
        $region = $this->_options['s3_region'];

        try {
            $client = S3Client::factory(array('key' => $key, 'secret' => $secret, 'region' => $region));

            $prefix = $this->_get_desired_prefix();
            $archive_index = 0;

            while ($client->doesObjectExist($bucket, sprintf('%s/archive_%d.gz', $prefix, $archive_index))) {
                $archive_index += 1;
            }

            $body = gzencode(join("\n", $batch));
            $result = $client->upload($bucket, sprintf('%s/archive_%d.gz', $prefix, $archive_index), $body);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
