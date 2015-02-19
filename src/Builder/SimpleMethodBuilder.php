<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;

class SimpleMethodBuilder implements BuilderInterface
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
        return (new BuilderFactory())->method($this->name)
            ->setDocComment($this->source->getDocComment())
            ->makeStatic()
            ->addParams($this->source->params)
            ->addStmts($this->source->stmts);
    }
}
