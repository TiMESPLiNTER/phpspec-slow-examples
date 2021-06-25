<?php

declare(strict_types=1);

namespace Timesplinter\PhpSpec\SlowExamples;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Extension;
use PhpSpec\ServiceContainer;
use Symfony\Component\Stopwatch\Stopwatch;

final class SlowExamplesExtension implements Extension
{
    public function load(ServiceContainer $container, array $params)
    {
        $container->define(
            'event_dispatcher.listeners.test_time',
            static function (ServiceContainer $container) use ($params): SlowExamplesListener {
                /** @var ConsoleIO $consoleIO */
                $consoleIO = $container->get('console.io');

                return new SlowExamplesListener($consoleIO, new Stopwatch(), $params['thresholdMs'] ?? 500);
            },
            ['event_dispatcher.listeners']
        );
    }
}
