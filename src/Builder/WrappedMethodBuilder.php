<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class WrappedMethodBuilder implements BuilderInterface
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
            ->addParams($this->createParams())
            ->addStmt(new Stmt\Return_($this->source));
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->uses as $index => $use) {
            $params[] = (new BuilderFactory())->param($use->var);
        }

        return $params;
    }
}
