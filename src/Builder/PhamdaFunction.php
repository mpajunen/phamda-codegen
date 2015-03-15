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
    private $exampleStatements;

    public function __construct($name, $wrapType, Expr\Closure $source, callable $getFunction, array $exampleStatements)
    {
        $this->name              = $name;
        $this->wrapType          = $wrapType;
        $this->source            = $source;
        $this->innerFunction     = $this->createInnerFunction($getFunction);
        $this->exampleStatements = $exampleStatements;
    }

    public function getArity()
    {
        /** @var Node\Param $lastParam */
        $lastParam = end($this->source->params);
        $base      = count($this->source->params);

        return $lastParam->variadic ? $base - 1 : $base;
    }

    public function getClosure()
    {
        return $this->source;
    }

    public function getExampleStatements()
    {
        return $this->exampleStatements;
    }

    public function getHelperMethodName($format)
    {
        return sprintf($format, ucfirst(trim($this->getName(), '_')));
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

    public function getCollectionArgumentName()
    {
        return $this->getLastParam()->name;
    }

    public function returnsCallable()
    {
        return $this->getReturnExpression() instanceof Expr\Closure
            || ($this->innerFunction && $this->innerFunction->returnsCallable());
    }

    public function returnsCollection()
    {
        return strpos($this->getDocComment(), '@return array|Collection') !== false;
    }

    public function returnsCollections()
    {
        return strpos($this->getDocComment(), '@return array[]|Collection[]') !== false;
    }

    public function isCollectionFunction()
    {
        return $this->getLastParam() !== false
            && in_array($this->getLastParam()->name, ['collection', 'values'])
            && $this->getLastParam()->type === null;
    }

    public function __call($name, $args)
    {
        return $this->source->$name(...$args);
    }

    public function __get($name)
    {
        return $this->source->$name;
    }

    /**
     * @return Node\Param|false
     */
    private function getLastParam()
    {
        return end($this->source->params);
    }

    private function getReturnExpression()
    {
        foreach ($this->source->stmts as $statement) {
            if ($statement instanceof Stmt\Return_) {
                return $statement->expr;
            }
        }

        return null;
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
