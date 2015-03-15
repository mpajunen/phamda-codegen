<?php

namespace Phamda\Builder;

use Phamda\Phamda;
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
