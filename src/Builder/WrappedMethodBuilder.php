<?php

namespace Phamda\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class WrappedMethodBuilder implements BuilderInterface
{
    private $externalVariables;
    private $source;

    public function __construct(PhamdaFunction $source, array $externalVariables)
    {
        $this->source            = $source;
        $this->externalVariables = $externalVariables;
    }

    public function build()
    {
        return (new BuilderFactory())->method($this->source->getName())
            ->setDocComment($this->source->getDocComment())
            ->makeStatic()
            ->addParams($this->createParams())
            ->addStmt(new Stmt\Return_($this->source->getClosure()));
    }

    private function createParams()
    {
        $params = [];
        foreach ($this->source->uses as $index => $use) {
            $param = (new BuilderFactory())->param($use->var);
            $type  = $this->getVariableTypeHint($use->var);
            if ($type) {
                $param->setTypeHint($type);
            }

            $params[] = $param;
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

        return null;
    }
}
