<?php

namespace Phamda\Builder\Docs;

use Phamda\Builder\CommentExampleBuilder;
use Phamda\Builder\PhamdaFunction;

class ListDocFunctionBuilder
{
    private $function;

    public function __construct(PhamdaFunction $function)
    {
        $this->function = $function;
    }

    public function getLink()
    {
        return sprintf('* [%1$s](#%1$s)', $this->function->getName());
    }

    public function getSection()
    {
        return implode("\n", [
            '',
            '',
            sprintf('<a name="%s"></a>', $this->function->getName()),
            '### ' . $this->function->getName(),
            sprintf('`%s`', ((new MethodSignatureBuilder($this->function))->getSignature())),
            '',
            $this->getSummary(),
            $this->getExamples(),
        ]);
    }

    private function getExamples()
    {
        $rows = (new CommentExampleBuilder($this->function))->getRows();

        return $rows ? implode("\n", array_merge(['##### Examples'], $rows)) : '';
    }

    private function getSummary()
    {
        $row = explode("\n", $this->function->getDocComment())[1];

        return substr($row, strpos($row, '*') + 2);
    }
}
