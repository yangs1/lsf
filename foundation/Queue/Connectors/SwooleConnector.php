<?php

namespace Foundation\Queue\Connectors;

use Foundation\Queue\SwooleQueue;

class SwooleConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new SwooleQueue();
    }
}
