<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class PhamdaMethodBuilder
{
    private $source;

    public function __construct(Stmt\Function_ $source)
    {
        $this->source = $source;
    }

    public function build()
    {
        return (new BuilderFactory())->method($this->source->name)
            ->setDocComment($this->createComment())
            ->makeStatic()
            ->addParams($this->createParams())
            ->addStmts($this->createStatements());
    }

    private function createComment()
    {
        return str_replace('* @return ', '* @return callable|', $this->source->getDocComment());
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->params as $index => $param) {
            $newParam = clone $param;

            // First parameter is always required.
            if ($index !== 0) {
                $newParam->default = new Expr\ConstFetch(new Name('null'));
            }

            $params[] = $newParam;
        }

        return $params;
    }

    private function createStatements()
    {
        $arity = count($this->source->params);

        if ($arity < 2) {
            $statements = $this->getSimpleWrapStatement();
        } elseif ($arity > 3) {
            throw new \LogicException(sprintf('CurryN is not supported, arity "%s" required.', $arity));
        } else {
            $statements = $this->getCurriedStatements($arity);
        }

        return $statements;
    }

    private function getSimpleWrapStatement()
    {
        return [
            new Stmt\Return_($this->createInnerFunction())
        ];
    }

    private function getCurriedStatements($arity)
    {
        $returnCall = new Expr\FuncCall(new Expr\Variable('func'), [
            new Arg(new Expr\FuncCall(new Name('func_get_args')), false, true),
        ]);

        return [
            new Expr\Assign(new Expr\Variable('func'), $this->getCurryWrap($arity)),
            new Stmt\Return_($returnCall)
        ];
    }

    private function getCurryWrap($arity)
    {
        return new Expr\StaticCall(new Name('static'), 'curry' . $arity, [
            $this->createInnerFunction(),
        ]);
    }

    private function createInnerFunction()
    {
        return new Expr\Closure([
            'params' => $this->source->params,
            'stmts'  => $this->source->stmts,
        ]);
    }
}
