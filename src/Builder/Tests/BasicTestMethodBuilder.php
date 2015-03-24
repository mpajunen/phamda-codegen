<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\AbstractMethodBuilder;
use Phamda\Builder\PhamdaFunction;
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
        $params = [$this->factory->param('expected')];

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

    protected function createStatements()
    {
        $statements = [];
        foreach (range(0, count($this->source->params)) as $offset) {
            $function       = null;
            $argumentSource = $this->source->params;

            if ($offset !== 0) {
                if (! $this->source->isCurried()) {
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

            $statements[] = $this->createAssert($this->createFunctionCall($argumentSource, $function), $offset === 0);
        }

        return $statements;
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
        $args = [];
        foreach ($sources as $source) {
            /** @var Param $source */
            $args[] = new Arg(new Expr\Variable($source->name), false, $source->variadic);
        }

        return $args;
    }
}
