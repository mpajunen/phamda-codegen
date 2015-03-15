<?php

namespace Phamda\Builder;

use PhpParser\Node\Expr;

class SimpleMethodBuilder extends AbstractMethodBuilder
{
    const COMMENT_ROW_PREFIX = '     *';

    public function build()
    {
        return parent::build()->makeStatic();
    }

    protected function createComment()
    {
        $rows = explode("\n", $this->source->getDocComment());

        return implode("\n", array_merge(
            array_slice($rows, 0, 1),
            array_map(function ($row) {
                return self::COMMENT_ROW_PREFIX . ' ' . $row;
            }, (new CommentExampleBuilder($this->source))->getRows()),
            [self::COMMENT_ROW_PREFIX],
            array_slice($rows, 1)
        ));
    }
}
