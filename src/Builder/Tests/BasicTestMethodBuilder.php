<?php

namespace Phamda\Builder\Tests;

use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class BasicTestMethodBuilder
{
    private $source;

    public function __construct(Stmt\Function_ $source)
    {
        $this->source = $source;
    }

    public function build()
    {
        return (new BuilderFactory())->method('test' . ucfirst($this->source->name))
            ->setDocComment($this->createComment())
            ->addParams($this->createParams())
            ->addStmts($this->createStatements())
        ;
    }

    private function createComment()
    {
        $providerMethod = sprintf('get%sData', ucfirst($this->source->name));

        return <<<EOT
/**
 * @dataProvider $providerMethod
 */
EOT;
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->params as $param) {
            $newParam = clone $param;

            $params[] = $newParam;
        }

        $params[] = (new BuilderFactory())->param('expected');
//        $params[] = (new BuilderFactory())->param('message')->setDefault(null);

        return $params;
    }

    private function createStatements()
    {
        $statements = [];
        foreach (range(0, count($this->source->params) - 1) as $offset) {
            if ($offset !== 0) {
                $args = $this->createArguments(array_slice($this->source->params, 0, $offset));
                $statements[] = new Expr\Assign(
                    new Expr\Variable('curried' . $offset),
                    new Expr\StaticCall(new Name('Phamda'), $this->source->name, $args)
                );
            }

            $statements[] = $this->createMethodAssert($offset);
        }

        return $statements;
    }

    private function createMethodAssert($offset)
    {
        $args = $this->createArguments(array_slice($this->source->params, $offset));
        $call = ($offset === 0)
            ? new Expr\StaticCall(new Name('Phamda'), $this->source->name, $args)
            : new Expr\FuncCall(new Expr\Variable('curried' . $offset), $args);

        return new Expr\MethodCall(new Expr\Variable('this'), 'assertEquals', [
            new Expr\Variable('expected'),
            $call,
        ]);
    }

    private function createArguments(array $params)
    {
        $args = [];

        foreach ($params as $param) {
            $args[] = new Arg(new Expr\Variable($param->name));
        }

        return $args;
    }
}
