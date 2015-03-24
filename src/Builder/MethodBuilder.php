<?php

namespace Phamda\Builder;

use Phamda\Phamda;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class MethodBuilder extends AbstractMethodBuilder
{
    const COMMENT_ROW_PREFIX = '     *';

    public function build()
    {
        return parent::build()->makeStatic();
    }

    protected function createComment()
    {
        $comment = $this->createBaseComment();

        return $this->source->isCurried()
            ? str_replace('callable|callable', 'callable', str_replace(
                '* @return ', '* @return callable|', $comment
            ))
            : $comment;
    }

    protected function createParams()
    {
        $params = [];
        foreach ($this->source->params as $param) {
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

    private function createBaseComment()
    {
        $rows         = explode("\n", $this->source->getDocComment());
        $exampleStart = Phamda::findIndex(function ($row) { return strpos($row, '@') !== false; }, $rows);

        return implode("\n", array_merge(
            array_slice($rows, 0, $exampleStart),
            array_map(function ($row) {
                return self::COMMENT_ROW_PREFIX . ' ' . $row;
            }, (new CommentExampleBuilder($this->source))->getRows()),
            [self::COMMENT_ROW_PREFIX],
            array_slice($rows, $exampleStart)
        ));
    }
}
