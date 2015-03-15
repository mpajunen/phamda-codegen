<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\PhamdaFunction;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String;
use PhpParser\Node\Stmt;

class CollectionTestMethodBuilder extends BasicTestMethodBuilder
{
    private $simple;

    public function __construct(PhamdaFunction $source, $simple)
    {
        parent::__construct($source);
        $this->simple = $simple;
    }

    protected function getName()
    {
        return $this->source->getHelperMethodName('test%s' . ($this->simple ? 'Simple' : ''));
    }

    protected function createParams()
    {
        $params = [$this->factory->param('expected')];

        foreach ($this->source->params as $param) {
            $params[] = clone $param;
        }

        $params = array_merge($params, $this->source->getInnerFunctionParams());

        return $params;
    }

    protected function createStatements()
    {
        return [
            $this->createCollectionAssignment(),
            $this->createResultAssignment(),
            $this->createResultAssert(),
            $this->createImmutabilityAssert()
        ];
    }

    private function createCollectionAssignment()
    {
        return new Expr\Assign(
            new Expr\Variable('_' . $this->source->getCollectionArgumentName()),
            new Expr\New_(new Name($this->simple ? 'ArrayContainer' : 'ArrayCollection'), [
                new Expr\Variable($this->source->getCollectionArgumentName())
            ])
        );
    }

    private function createResultAssignment()
    {
        return new Expr\Assign(
            new Expr\Variable('result'),
            $this->createFunctionCall()
        );
    }

    private function createResultAssert()
    {
        return new Expr\MethodCall(new Expr\Variable('this'), 'assertSame', [
            new Expr\Variable('expected'),
            $this->createResultComparison(),
            new String(sprintf('%s works for%s collection objects.', $this->source->getName(), $this->simple ? ' simple' : '')),
        ]);
    }

    private function createImmutabilityAssert()
    {
        return new Expr\MethodCall(new Expr\Variable('this'), 'assertSame', [
            new Expr\Variable($this->source->getCollectionArgumentName()),
            new Expr\MethodCall(new Expr\Variable('_' . $this->source->getCollectionArgumentName()), 'toArray'),
            new String(sprintf('%s does not modify original collection values.', $this->source->getName())),
        ]);
    }

    private function createResultComparison()
    {
        $result = new Expr\Variable('result');

        if (! $this->simple && ($this->source->returnsCollection() || $this->source->returnsCollections())) {
            $helperMethod = $this->source->returnsCollections() ? 'getCollectionGroupArray' : 'getCollectionArray';

            $result = new Expr\MethodCall(new Expr\Variable('this'), $helperMethod, [$result]);
        }

        return $result;
    }

    private function createFunctionCall()
    {
        $arguments = $this->createArguments($this->source->params);

        return new Expr\StaticCall(new Name('Phamda'), $this->source->getName(), $arguments);
    }

    private function createArguments(array $sources)
    {
        $args = [];
        foreach ($sources as $source) {
            /** @var Param $source */
            $name   = $source->name === $this->source->getCollectionArgumentName() ? '_' . $source->name : $source->name;
            $args[] = new Arg(new Expr\Variable($name), false, $source->variadic);
        }

        return $args;
    }
}
