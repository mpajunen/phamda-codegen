<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use Phamda\Builder\PhamdaFunction;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

class BasicTestMethodBuilder implements BuilderInterface
{
    private $factory;
    private $source;

    public function __construct(PhamdaFunction $source)
    {
        $this->source  = $source;
        $this->factory = new BuilderFactory();
    }

    public function build()
    {
        return $this->factory->method($this->getHelperMethodName('test%s'))
            ->setDocComment($this->createComment())
            ->addParams($this->createParams())
            ->addStmts($this->createStatements());
    }

    private function createComment()
    {
        return <<<EOT
/**
 * @dataProvider {$this->getHelperMethodName('get%sData')}
 */
EOT;
    }

    private function getHelperMethodName($format)
    {
        return sprintf($format, ucfirst(trim($this->source->getName(), '_')));
    }

    private function createParams()
    {
        $params = [$this->factory->param('expected')];

        foreach ($this->source->uses as $index => $use) {
            $params[] = $this->factory->param($use->var);
        }

        foreach ($this->source->params as $param) {
            $newParam = clone $param;
            if ($param->variadic && $this->source->getInnerFunctionParams() !== []) {
                $newParam->type     = 'array';
                $newParam->variadic = false;
            }

            $params[] = $newParam;
        }

        $params = array_merge($params, $this->source->getInnerFunctionParams());

        return $params;
    }

    private function createStatements()
    {
        return $this->source->uses
            ? $this->createUseStatements()
            : $this->createParameterStatements();
    }

    private function createUseStatements()
    {
        $function = new Expr\Variable('wrapped');

        return [
            new Expr\Assign($function, $this->createFunctionCall($this->source->uses)),
            $this->createAssert($this->createFunctionCall($this->source->params, $function))
        ];
    }

    private function createParameterStatements()
    {
        $statements = [];
        foreach (range(0, count($this->source->params)) as $offset) {
            $function       = null;
            $argumentSource = $this->source->params;

            if ($offset !== 0) {
                if ($this->source->getWrapType() === 'simple') {
                    break;
                }

                $function       = new Expr\Variable('curried' . ($offset - 1));
                $statements[]   = new Expr\Assign($function, $this->createFunctionCall(array_slice($this->source->params, 0, $offset - 1)));
                $argumentSource = array_slice($this->source->params, $offset - 1);
            }

            if ($this->source->returnsCallable()) {
                $call           = $this->createFunctionCall($argumentSource, $function);
                $function       = new Expr\Variable('main' . $offset);
                $statements[]   = new Expr\Assign($function, $call);
                $argumentSource = $this->source->getInnerFunctionParams();
            }

            $statements[] = $this->createAssert($this->createFunctionCall($argumentSource, $function));
        }

        return $statements;
    }

    private function createAssert(Expr $call)
    {
        return new Expr\MethodCall(new Expr\Variable('this'), 'assertSame', [
            new Expr\Variable('expected'),
            $call,
        ]);
    }

    private function createFunctionCall(array $argumentSource, Expr\Variable $function = null)
    {
        $arguments = $this->createArguments($argumentSource);

        return $function !== null
            ? new Expr\FuncCall($function, $arguments)
            : new Expr\StaticCall(new Name('Phamda'), $this->source->getName(), $arguments);
    }

    private function createArguments(array $sources)
    {
        $args = [];
        foreach ($sources as $source) {
            /** @var Expr\ClosureUse|Param $param */
            $args[] = new Arg(new Expr\Variable($source->name ?: $source->var), false, $source->variadic);
        }

        return $args;
    }
}
