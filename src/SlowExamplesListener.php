<?php

declare(strict_types=1);

namespace Timesplinter\PhpSpec\SlowExamples;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;
use PhpSpec\Loader\Node\ExampleNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class SlowExamplesListener implements EventSubscriberInterface
{
    private Stopwatch $stopwatch;

    private ConsoleIO $consoleIO;

    private int $thresholdMs;

    private array $slowExamples;

    public function __construct(ConsoleIO $consoleIO, Stopwatch $stopwatch, int $thresholdMs)
    {
        $this->consoleIO = $consoleIO;
        $this->thresholdMs = $thresholdMs;
        $this->stopwatch = $stopwatch;
        $this->slowExamples = [];
    }

    public function beforeExample(ExampleEvent $event): void
    {
        $example = $event->getExample();

        $exampleName = $this->getNameFromExample($example);

        if (null === $exampleName) {
            return;
        }

        $this->stopwatch->start($exampleName);
    }

    public function afterExample(ExampleEvent $event): void
    {
        $example = $event->getExample();

        $exampleName = $this->getNameFromExample($example);

        if (null === $exampleName) {
           return;
        }

        $event = $this->stopwatch->stop($exampleName);
        $durationMs = $event->getDuration();

        if ($durationMs > $this->thresholdMs) {
            $this->slowExamples[$exampleName] = $durationMs;
        }
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if (count($this->slowExamples) > 0) {
            $this->consoleIO->writeln(sprintf(
                'There were slow examples exceeding the threshold of <info>%dms</info>:',
                $this->thresholdMs
            ));

            foreach ($this->slowExamples as $slowExample => $duration) {
                $this->consoleIO->writeln(sprintf(' - %s: <warning>%dms</warning>', $slowExample, $duration));
            }

            $this->slowExamples = [];
        } else {
            $this->consoleIO->writeln(sprintf(
                'There were no examples that exceeded the threshold of <info>%dms</info>',
                $this->thresholdMs
            ));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'beforeExample' => ['beforeExample', -10],
            'afterExample' => ['afterExample', -10],
            'afterSuite' => ['afterSuite', -10],
        ];
    }

    private function getNameFromExample(ExampleNode $example): ?string
    {
        $name = null;

        if (null !== $spec = $example->getSpecification()) {
            $name = $spec->getClassReflection()->getName();
        }

        return strtr('%spec%::%example%', [
            '%spec%' => $name,
            '%example%' => $example->getFunctionReflection()->getName(),
        ]);
    }
}
