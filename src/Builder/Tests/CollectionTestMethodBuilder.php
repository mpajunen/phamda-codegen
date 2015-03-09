<?php

namespace Phamda\Builder\Tests;

use Phamda\Builder\BuilderInterface;
use Phamda\Builder\PhamdaFunction;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;

class CollectionTestMethodBuilder implements BuilderInterface
{
    const COLLECTION_ARGUMENT_NAME = 'collection';
    const COLLECTION_VARIABLE_NAME = 'arrayCollection';

    private $factory;
    private $source;

    public function __construct(PhamdaFunction $source)
    {
        $this->source  = $source;
        $this->factory = new BuilderFactory();
    }

    public function build()
    {
        return $this->factory->method($this->getHelperMethodName('test%s'))
            ->setDocComment($this->createComment())
            ->addParams($this->createParams())
            ->addStmts($this->createStatements());
    }

    private function createComment()
    {
        return <<<EOT
/**
 * @dataProvider {$this->getHelperMethodName('get%sData')}
 */
EOT;
    }

    private function getHelperMethodName($format)
    {
        return sprintf($format, ucfirst(trim($this->source->getName(), '_')));
    }

    private function createParams()
    {
        $params = [$this->factory->param('expected')];

        foreach ($this->source->params as $param) {
            $params[] = clone $param;
        }

        $params = array_merge($params, $this->source->getInnerFunctionParams());

        return $params;
    }

    private function createStatements()
    {
        return [
            $this->createCollectionAssignment(),
            $this->createAssert($this->createFunctionCall()),
        ];
    }

    private function createCollectionAssignment()
    {
        return new Expr\Assign(
            new Expr\Variable(self::COLLECTION_VARIABLE_NAME),
            new Expr\New_(new Name('ArrayCollection'), [
                new Expr\Variable(self::COLLECTION_ARGUMENT_NAME)
            ])
        );
    }

    private function createAssert(Expr $call)
    {
        return new Expr\MethodCall(new Expr\Variable('this'), 'assertSame', [
            new Expr\Variable('expected'),
            $call,
        ]);
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
            $name   = $source->name === self::COLLECTION_ARGUMENT_NAME ? self::COLLECTION_VARIABLE_NAME : $source->name;
            $args[] = new Arg(new Expr\Variable($name), false, $source->variadic);
        }

        return $args;
    }
}
