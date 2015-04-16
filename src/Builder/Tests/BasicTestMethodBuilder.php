<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\AbstractMethodBuilder;
use Phamda\Builder\PhamdaFunction;
use Phamda\Phamda;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String;
use PhpParser\Node\Stmt;

class BasicTestMethodBuilder extends AbstractMethodBuilder
{
    protected $factory;

    public function __construct(PhamdaFunction $source)
    {
        parent::__construct($source);
        $this->factory = new BuilderFactory();
    }

    protected function getName()
    {
        return $this->source->getHelperMethodName('test%s');
    }

    protected function createComment()
    {
        return <<<EOT
/**
 * @dataProvider {$this->source->getHelperMethodName('get%sData')}
 */
EOT;
    }

    protected function createParams()
    {
        $convertVariadic = function (Param $param) {
            if ($param->variadic && $this->source->getInnerFunctionParams() !== []) {
                $param->type     = 'array';
                $param->variadic = false;
            }
        };

        $process = Phamda::pipe(
            Phamda::map(Phamda::clone_()),
            Phamda::each($convertVariadic),
            Phamda::prepend($this->factory->param('expected')),
            Phamda::merge(Phamda::_(), $this->source->getInnerFunctionParams())
        );

        return $process($this->source->params);
    }

    protected function createStatements()
    {
        return array_merge($this->createResultTestStatements(), $this->createCurryTestStatements());
    }

    private function createResultTestStatements()
    {
        $statements     = [];
        $function       = null;
        $argumentSource = $this->source->params;

        if ($this->source->returnsCallable()) {
            $call           = $this->createFunctionCall($argumentSource, $function);
            $function       = new Expr\Variable('main0');
            $statements[]   = new Expr\Assign($function, $call);
            $argumentSource = $this->source->getInnerFunctionParams();
        }

        $statements[] = $this->createAssert($this->createFunctionCall($argumentSource, $function), true);

        return $statements;
    }

    private function createCurryTestStatements()
    {
        if (count($this->source->params) === 0 || ! $this->source->isCurried()) {
            return [];
        }

        $result    = new Expr\Variable('result');
        $arguments = $this->source->params;

        if ($this->source->returnsCallable()) {
            $resultExpr = $this->createFunctionCall($this->source->getInnerFunctionParams(), $result);
        } elseif ($this->source->isVariadic()) {
            $resultExpr = $this->createFunctionCall(array_slice($this->source->params, -1), $result);
            $arguments  = array_slice($this->source->params, 0, -1);
        } else {
            $resultExpr = $result;
        }

        $foreach = new Stmt\Foreach_(
            new Expr\MethodCall(new Expr\Variable('this'), 'getCurriedResults', array_merge(
                [$this->createFunctionCall([])],
                $this->createArguments($arguments)
            )),
            $result
        );

        $foreach->stmts = [$this->createAssert($resultExpr, false)];

        return [$foreach];
    }

    private function createAssert(Expr $call, $isDirectCall)
    {
        return new Expr\MethodCall(new Expr\Variable('this'), 'assertSame', [
            new Expr\Variable('expected'),
            $call,
            new String(sprintf($isDirectCall ? '%s produces correct results.' : '%s is curried correctly.', $this->source->getName())),
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
        $create = function (Param $source) {
            return new Arg(new Expr\Variable($source->name), false, $source->variadic);
        };

        return Phamda::map($create, $sources);
    }
}
