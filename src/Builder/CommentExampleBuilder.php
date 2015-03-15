<?php

namespace Phamda\Builder;

class CommentExampleBuilder
{
    private $source;

    public function __construct(PhamdaFunction $source)
    {
        $this->source = $source;
    }

    public function getRows()
    {
        try {
            $examples = $this->getExamples();
        } catch (\InvalidArgumentException $e) {
            $examples = [];
        }

        return $examples ? array_merge(['```php'], $examples, ['```']) : [];
    }

    private function getExamples()
    {
        $helper = new ExampleHelper();
        $method = $this->source->getHelperMethodName('get%sData');
        if (! method_exists($helper, $method)) {
            return [];
        }

        $testData = $helper->$method();

        return [
            $this->getTestDataExample(...$testData[0]),
        ];
    }

    private function getTestDataExample($expected, ... $parameters)
    {
        $print = function ($variable) use (&$print) {
            if (is_callable($variable)) {
                return '{function}';
            } elseif (is_object($variable)) {
                return '{object}';
            } elseif (is_array($variable)) {
                return sprintf('[%s]', implode(', ', array_map($print, $variable)));
            } elseif (is_string($variable)) {
                return "'$variable'";
            } elseif (is_numeric($variable)) {
                return $variable;
            } elseif (is_bool($variable)) {
                return $variable ? 'true' : 'false';
            } elseif (is_null($variable)) {
                return 'null';
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid example variable of type "%s" for function "%s".',
                    gettype($variable),
                    $this->source->getName()
                ));
            }
        };

        return sprintf('Phamda::%s(%s); // %s',
            $this->source->getName(),
            implode(', ', array_map($print, $parameters)),
            $print($expected)
        );
    }
}
