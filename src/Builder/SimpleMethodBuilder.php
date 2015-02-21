<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;

class SimpleMethodBuilder implements BuilderInterface
{
    private $source;

    public function __construct(PhamdaFunction $source)
    {
        $this->source = $source;
    }

    public function build()
    {
        return (new BuilderFactory())->method($this->source->getName())
            ->setDocComment($this->source->getDocComment())
            ->makeStatic()
            ->addParams($this->source->params)
            ->addStmts($this->source->stmts);
    }
}
