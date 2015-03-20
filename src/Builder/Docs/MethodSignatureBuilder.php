<?php

namespace Phamda\Builder\Docs;

use Phamda\Builder\AbstractMethodBuilder;
use Phamda\Phamda;
use Phamda\Printer\PhamdaPrinter;
use PhpParser\Node\Param;

class MethodSignatureBuilder extends AbstractMethodBuilder
{
    public function getSignature()
    {
        $method = (new PhamdaPrinter())->prettyPrint([$this->build()->getNode()]);

        $process = Phamda::pipe(
            Phamda::curry('explode', "\n"),
            Phamda::first(),
            Phamda::curry('trim'),
            Phamda::curry('str_replace', 'public function ', 'Phamda::')
        );

        return $this->getReturnType() . ' ' . $process($method);
    }

    protected function createComment()
    {
        return '';
    }

    private function getReturnType()
    {
        $process = Phamda::pipe(
            $this->getTagPicker('@return'),
            Phamda::first(),
            Phamda::first()
        );

        return $process($this->source->getDocComment());
    }

    protected function createParams()
    {
        $setTypeHint = function ($type, Param $param) {
            $param->type = $type;
            return $param;
        };

        return Phamda::zipWith($setTypeHint, $this->getParamTypes(), $this->source->params);
    }

    private function getParamTypes()
    {
        $process = Phamda::pipe(
            $this->getTagPicker('@param'),
            Phamda::map(Phamda::first()),
            Phamda::curry('array_values')
        );

        return $process($this->source->getDocComment());
    }

    private function getTagPicker($tag)
    {
        return Phamda::pipe(
            Phamda::curry(Phamda::binary('explode'), "\n"),
            Phamda::filter(function($row) use ($tag) { return strpos($row, $tag) !== false; }),
            Phamda::map(
                Phamda::pipe(
                    Phamda::curry(Phamda::binary('explode'), $tag),
                    Phamda::last(),
                    Phamda::curry('trim'),
                    Phamda::curry('explode', ' ')
                )
            )
        );
    }
}
