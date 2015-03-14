<?php

namespace Phamda\Builder;

use PhpParser\Builder\Method;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;

class SimpleMethodBuilder implements BuilderInterface
{
    const COMMENT_ROW_PREFIX = '     *';

    protected $source;

    public function __construct(PhamdaFunction $source)
    {
        $this->source = $source;
    }

    /**
     * @return Method
     */
    final public function build()
    {
        return (new BuilderFactory())->method($this->getName())
            ->setDocComment($this->createComment())
            ->addParams($this->createParams())
            ->addStmts($this->createStatements());
    }

    protected function getName()
    {
        return $this->source->getName();
    }

    protected function createComment()
    {
        $rows = explode("\n", $this->source->getDocComment());

        return implode("\n", array_merge(
            array_slice($rows, 0, 1),
            array_map(function ($row) {
                return self::COMMENT_ROW_PREFIX . ' ' . $row;
            }, $this->getCommentExampleRows()),
            [self::COMMENT_ROW_PREFIX],
            array_slice($rows, 1)
        ));
    }

    protected function createParams()
    {
        return $this->source->params;
    }

    protected function createStatements()
    {
        return $this->source->stmts;
    }

    protected function getHelperMethodName($format)
    {
        return sprintf($format, ucfirst(trim($this->source->getName(), '_')));
    }

    private function getCommentExampleRows()
    {
        try {
            $examples = $this->getCommentExamples();
        } catch (\InvalidArgumentException $e) {
            $examples = [];
        }

        return $examples ? array_merge(['```php'], $examples, ['```']) : [];
    }

    private function getCommentExamples()
    {
        $helper = new ExampleHelper();
        $method = $this->getHelperMethodName('get%sData');
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
