<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

class BasicTestMethodBuilder implements BuilderInterface
{
    private $factory;
    private $name;
    private $source;

    public function __construct($name, Expr\Closure $source)
    {
        $this->name   = $name;
        $this->source = $source;

        $this->factory = new BuilderFactory();
    }

    public function build()
    {
        return $this->factory->method($this->getHelperMethodName('test%s'))
            ->setDocComment($this->createComment())
            ->addParams($this->createParams())
            ->addStmts($this->createStatements())
        ;
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
        return sprintf($format, ucfirst(trim($this->name, '_')));
    }

    private function returnsCallable()
    {
        return $this->getReturnStatement()->expr instanceof Expr\Closure;
    }

    private function getReturnStatement()
    {
        foreach ($this->source->stmts as $statement) {
            if ($statement instanceof Stmt\Return_) {
                /** @var Stmt\Return_ $statement */
                return $statement;
            }
        }

        return null;
    }

    private function getInnerFunctionParams()
    {
        return $this->returnsCallable() ? $this->getReturnStatement()->expr->params : [];
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->uses as $index => $use) {
            $params[] = $this->factory->param($use->var);
        }

        foreach ($this->source->params as $param) {
            $newParam = clone $param;

            $params[] = $newParam;
        }

        $params[] = $this->factory->param('expected');

        $params = array_merge($params, $this->getInnerFunctionParams());

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
        foreach (range(0, count($this->source->params) - 1) as $offset) {
            $function       = null;
            $argumentSource = $this->source->params;

            if ($offset !== 0) {
                $function       = new Expr\Variable('curried' . $offset);
                $statements[]   = new Expr\Assign($function, $this->createFunctionCall(array_slice($this->source->params, 0, $offset)));
                $argumentSource = array_slice($this->source->params, $offset);
            }

            if ($this->returnsCallable()) {
                $call           = $this->createFunctionCall($argumentSource, $function);
                $function       = new Expr\Variable('main' . $offset);
                $statements[]   = new Expr\Assign($function, $call);
                $argumentSource = $this->getInnerFunctionParams();
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
            : new Expr\StaticCall(new Name('Phamda'), $this->name, $arguments);
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
