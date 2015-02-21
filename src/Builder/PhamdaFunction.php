<?php

namespace Phamda\Builder;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

/**
 * @method getDocComment
 *
 * @property Node[]            $stmts  Statements
 * @property Node\Param[]      $params Parameters
 * @property Expr\ClosureUse[] $uses   use()s
 * @property bool              $byRef  Whether to return by reference
 * @property bool              $static Whether the closure is static
 */
class PhamdaFunction
{
    private $innerFunction;
    private $name;
    private $source;
    private $wrapType;

    public function __construct($name, $wrapType, Expr\Closure $source, callable $getFunction)
    {
        $this->name          = $name;
        $this->wrapType      = $wrapType;
        $this->source        = $source;
        $this->innerFunction = $this->createInnerFunction($getFunction);
    }

    public function getClosure()
    {
        return $this->source;
    }

    public function getInnerFunctionParams()
    {
        if ($this->getReturnExpression() instanceof Expr\Closure) {
            return $this->getReturnExpression()->params;
        }

        return $this->innerFunction ? $this->innerFunction->getInnerFunctionParams() : [];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getWrapType()
    {
        return $this->wrapType;
    }

    public function returnsCallable()
    {
        return $this->getReturnExpression() instanceof Expr\Closure
               || ($this->innerFunction && $this->innerFunction->returnsCallable());
    }

    public function __call($name, $args)
    {
        return $this->source->$name(...$args);
    }

    public function __get($name)
    {
        return $this->source->$name;
    }

    private function getReturnExpression()
    {
        foreach ($this->source->stmts as $statement) {
            if ($statement instanceof Stmt\Return_) {
                return $statement->expr;
            }
        }

        throw new \LogicException(sprintf('Function "%s" does not have a return statement. Every function should return something.', $this->name));
    }

    /**
     * @param callable $getFunction
     *
     * @return PhamdaFunction
     */
    private function createInnerFunction(callable $getFunction)
    {
        $return = $this->getReturnExpression();

        if ($return instanceof Expr\StaticCall && $return->class->parts === ['Phamda']) {
            return $getFunction($return->name);
        }

        return null;
    }
}
