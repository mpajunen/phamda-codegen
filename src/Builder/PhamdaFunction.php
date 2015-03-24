<?php

namespace Phamda\Builder;

use Phamda\Phamda;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

/**
 * @method getDocComment
 *
 * @property int          $type   Type
 * @property bool         $byRef  Whether to return by reference
 * @property string       $name   Name
 * @property Node\Param[] $params Parameters
 * @property Node[]       $stmts  Statements
 */
class PhamdaFunction
{
    private $innerFunction;
    private $name;
    private $source;
    private $wrapType;
    private $exampleStatements;

    public function __construct($name, $wrapType, Stmt\ClassMethod $source, callable $getFunction, array $exampleStatements)
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
        return new Expr\Closure([
            'params' => $this->source->params,
            'stmts'  => $this->source->stmts,
        ]);
    }

    public function getExampleStatements()
    {
        return $this->exampleStatements;
    }

    public function getHelperMethodName($format)
    {
        return sprintf($format, $this->getName() !== '_' ? ucfirst(trim($this->getName(), '_')) : '_');
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
        return in_array('Collection', $this->getReturnTypes());
    }

    public function returnsCollections()
    {
        return in_array('Collection[]', $this->getReturnTypes());
    }

    public function returnsTraversable()
    {
        return in_array('\Traversable', $this->getReturnTypes());
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

    private function getReturnTypes()
    {
        $process = Phamda::pipe(
            Phamda::explode("\n"),
            Phamda::filter(Phamda::stringIndexOf('@return')),
            Phamda::first(),
            Phamda::explode('@return'),
            Phamda::last(),
            Phamda::curry('trim'),
            Phamda::explode('|')
        );

        return $process($this->getDocComment());
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
