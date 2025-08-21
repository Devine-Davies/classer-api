<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(
                new LineFormatter(
                    "%message%\n",
                    dateFormat: null,
                    allowInlineLineBreaks: true,
                    ignoreEmptyContextAndExtra: true
                )
            );
        }
    }
}
