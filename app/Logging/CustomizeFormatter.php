<?php

namespace App\Logging;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance with StructuredJsonFormatter.
     */
    public function __invoke(mixed $logger): void
    {
        $handlers = method_exists($logger, 'getHandlers')
            ? $logger->getHandlers()
            : (method_exists($logger, 'getLogger') ? $logger->getLogger()->getHandlers() : []);

        foreach ($handlers as $handler) {
            $handler->setFormatter(new StructuredJsonFormatter(config('app.name', 'muter-muter-app')));
        }
    }
}
