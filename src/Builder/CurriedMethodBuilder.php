<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class CurriedMethodBuilder implements BuilderInterface
{
    private $source;

    public function __construct(PhamdaFunction $source)
    {
        $this->source = $source;
    }

    public function build()
    {
        return (new BuilderFactory())->method($this->source->getName())
            ->setDocComment($this->createComment())
            ->makeStatic()
            ->addParams($this->createParams())
            ->addStmts($this->createStatements());
    }

    private function createComment()
    {
        return str_replace('callable|callable', 'callable', str_replace(
            '* @return ', '* @return callable|', $this->source->getDocComment()
        ));
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->params as $index => $param) {
            $newParam          = clone $param;
            $newParam->default = new Expr\ConstFetch(new Name('null'));

            $params[] = $newParam;
        }

        return $params;
    }

    private function createStatements()
    {
        $arity = count($this->source->params);

        if ($arity < 1) {
            throw new \LogicException(sprintf('Invalid curried function "%s", arity "%s".', $this->source->getName(), $arity));
        } elseif ($arity > 3) {
            throw new \LogicException(sprintf('CurryN is not supported, arity "%s" required for function "%s".', $arity, $this->source->getName()));
        }

        return $this->getCurriedStatements($arity);
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
        return new Expr\StaticCall(new Name('static'), 'curry' . $arity, [$this->source->getClosure()]);
    }
}
