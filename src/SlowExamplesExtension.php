<?php

declare(strict_types=1);

namespace Timesplinter\PhpSpec\SlowExamples;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Extension;
use PhpSpec\ServiceContainer;
use Symfony\Component\Stopwatch\Stopwatch;

final class SlowExampleExtension implements Extension
{
    public function load(ServiceContainer $container, array $params)
    {
        $container->define(
            'event_dispatcher.listeners.test_time',
            static function (ServiceContainer $container) use ($params): SlowExampleListener {
                /** @var ConsoleIO $consoleIO */
                $consoleIO = $container->get('console.io');

                return new SlowExampleListener($consoleIO, new Stopwatch(), $params['thresholdMs'] ?? 500);
            },
            ['event_dispatcher.listeners']
        );
    }
}
