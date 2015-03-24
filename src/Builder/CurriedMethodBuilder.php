<?php

namespace Phamda\Builder;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class CurriedMethodBuilder extends SimpleMethodBuilder
{
    protected function createComment()
    {
        return $this->source->isCurried()
            ? str_replace('callable|callable', 'callable', str_replace(
                '* @return ', '* @return callable|', parent::createComment()
            ))
            : parent::createComment();
    }

    protected function createParams()
    {
        $params = [];
        foreach ($this->source->params as $index => $param) {
            $newParam       = clone $param;
            $newParam->type = null;
            if (! $newParam->variadic) {
                $newParam->default = new Expr\ConstFetch(new Name('null'));
            }

            $params[] = $newParam;
        }

        return $params;
    }

    protected function createStatements()
    {
        return $this->source->isCurried()
            ? [new Stmt\Return_($this->getCurryWrap())]
            : $this->source->stmts;
    }

    private function getCurryWrap()
    {
        return new Expr\StaticCall(new Name('static'), 'curry' . $this->source->getArity(), [
            $this->source->getClosure(),
            new Arg(new Expr\FuncCall(new Name('func_get_args'))),
        ]);
    }
}
