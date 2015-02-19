<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class BasicTestMethodBuilder implements BuilderInterface
{
    private $name;
    private $source;

    public function __construct($name, Expr\Closure $source)
    {
        $this->name   = $name;
        $this->source = $source;
    }

    public function build()
    {
        return (new BuilderFactory())->method('test' . ucfirst($this->name))
            ->setDocComment($this->createComment())
            ->addParams($this->createParams())
            ->addStmts($this->createStatements())
        ;
    }

    private function createComment()
    {
        $providerMethod = sprintf('get%sData', ucfirst($this->name));

        return <<<EOT
/**
 * @dataProvider $providerMethod
 */
EOT;
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->uses as $index => $use) {
            $params[] = (new BuilderFactory())->param($use->var);
        }

        foreach ($this->source->params as $param) {
            $newParam = clone $param;

            $params[] = $newParam;
        }

        $params[] = (new BuilderFactory())->param('expected');

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
        $statements = [];

        $statements[] = new Expr\Assign(
            new Expr\Variable('wrapped'),
            $this->createMethodCall($this->source->uses)
        );

        $statements[] = $this->createAssert($this->createClosureCall('wrapped', $this->source->params));

        return $statements;
    }

    private function createParameterStatements()
    {
        $statements = [];
        foreach (range(0, count($this->source->params) - 1) as $offset) {
            if ($offset !== 0) {
                $statements[] = new Expr\Assign(
                    new Expr\Variable('curried' . $offset),
                    $this->createMethodCall(array_slice($this->source->params, 0, $offset))
                );
            }

            $statements[] = $this->createMethodAssert($offset);
        }

        return $statements;
    }

    private function createMethodAssert($offset)
    {
        $call = ($offset === 0)
            ? $this->createMethodCall($this->source->params)
            : $this->createClosureCall('curried' . $offset, array_slice($this->source->params, $offset));

        return $this->createAssert($call);
    }

    private function createAssert(Expr $call)
    {
        return new Expr\MethodCall(new Expr\Variable('this'), 'assertSame', [
            new Expr\Variable('expected'),
            $call,
        ]);
    }

    private function createClosureCall($name, $argumentSource)
    {
        return new Expr\FuncCall(new Expr\Variable($name), $this->createArguments($argumentSource));
    }

    private function createMethodCall($argumentSource)
    {
        return new Expr\StaticCall(new Name('Phamda'), $this->name, $this->createArguments($argumentSource));
    }

    private function createArguments(array $params)
    {
        $args = [];

        foreach ($params as $param) {
            $args[] = new Arg(new Expr\Variable($param->name ?: $param->var));
        }

        return $args;
    }
}
