<?php

namespace Foundation\Queue\Connectors;

use Foundation\Queue\NullQueue;

class NullConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        return new NullQueue;
    }
}
