<?php

namespace App\Logging;

use Monolog\Logger;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance with StructuredJsonFormatter.
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new StructuredJsonFormatter(config('app.name', 'muter-muter-app')));
        }
    }
}
