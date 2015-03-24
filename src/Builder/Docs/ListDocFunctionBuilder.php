<?php

namespace Phamda\Builder\Docs;

use Phamda\Builder\CommentExampleBuilder;
use Phamda\Builder\PhamdaFunction;
use Phamda\Phamda;

class ListDocFunctionBuilder
{
    public static function getLink(PhamdaFunction $function)
    {
        return sprintf('* [%1$s](#%1$s)', $function->getName());
    }

    public static function getSection(PhamdaFunction $function)
    {
        return implode("\n", [
            '',
            '',
            sprintf('<a name="%s"></a>', $function->getName()),
            '### ' . $function->getName(),
            sprintf('`%s`', ((new MethodSignatureBuilder($function))->getSignature())),
            '',
            self::getSummary($function),
            self::getExamples($function),
        ]);
    }

    private static function getExamples(PhamdaFunction $function)
    {
        $process = Phamda::pipe(
            Phamda::construct(CommentExampleBuilder::class),
            Phamda::invoker(0, 'getRows'),
            Phamda::ifElse(Phamda::isEmpty(), Phamda::identity(), Phamda::prepend('##### Examples')),
            Phamda::implode("\n")
        );

        return $process($function);
    }

    private static function getSummary(PhamdaFunction $function)
    {
        $row = explode("\n", $function->getDocComment())[1];

        return substr($row, strpos($row, '*') + 2);
    }
}
