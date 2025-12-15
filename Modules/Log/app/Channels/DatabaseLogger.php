<?php

namespace Modules\Log\app\Channels;

use Monolog\Logger;
use Monolog\Level;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('database');

        $level = Level::fromName($config['level'] ?? 'debug');

        $logger->pushHandler(new DatabaseLogChannel($config, $level));

        return $logger;
    }
}
