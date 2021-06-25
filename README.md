# phpspec-slow-examples

This PHPspec extenction reports slow examples that exceed a certain threshold.

## Usage
Install using composer: 

```bash
$ composer require timesplinter/phpspec-slow-examples
```

Add the new installed extension to your `phpspec.yml` and define a threshold:

```yaml
# ...

extensions:
  spec\Timesplinter\PhpSpec\SlowExamples\SlowExamplesExtension:
    thresholdMs: 1000

# ...
```