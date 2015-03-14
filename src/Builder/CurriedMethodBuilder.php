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
        return str_replace('callable|callable', 'callable', str_replace(
            '* @return ', '* @return callable|', parent::createComment()
        ));
    }

    protected function createParams()
    {
        $params = [];
        foreach ($this->source->params as $index => $param) {
            $newParam          = clone $param;
            if (! $newParam->variadic) {
                $newParam->default = new Expr\ConstFetch(new Name('null'));
            }

            $params[] = $newParam;
        }

        return $params;
    }

    protected function createStatements()
    {
        $arity = $this->source->getArity();

        if ($arity < 1) {
            throw new \LogicException(sprintf('Invalid curried function "%s", arity "%s".', $this->source->getName(), $arity));
        } elseif ($arity > 3) {
            throw new \LogicException(sprintf('CurryN is not supported, arity "%s" required for function "%s".', $arity, $this->source->getName()));
        }

        return [new Stmt\Return_($this->getCurryWrap($arity))];
    }

    private function getCurryWrap($arity)
    {
        return new Expr\StaticCall(new Name('static'), 'curry' . $arity, [
            $this->source->getClosure(),
            new Arg(new Expr\FuncCall(new Name('func_get_args'))),
        ]);
    }
}
