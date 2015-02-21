<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class WrappedMethodBuilder implements BuilderInterface
{
    private $externalVariables;
    private $name;
    private $source;

    public function __construct($name, Expr\Closure $source, array $externalVariables)
    {
        $this->name              = $name;
        $this->source            = $source;
        $this->externalVariables = $externalVariables;
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
            $params[] = (new BuilderFactory())->param($use->var)
                ->setTypeHint($this->getVariableTypeHint($use->var));
        }

        return $params;
    }

    private function getVariableTypeHint($name)
    {
        if (! isset($this->externalVariables[$name])) {
            throw new \Exception(sprintf('External variable "%s" not found.', $name));
        }

        $variable = $this->externalVariables[$name];

        if ($variable instanceof Expr\Closure) {
            return 'callable';
        }

        return new Name(null);
    }
}
