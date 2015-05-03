<?php

namespace Phamda\CodeGen\Builder\Docs;

use Phamda\CodeGen\Builder\CommentExampleBuilder;
use Phamda\CodeGen\Functions\FunctionWrap;
use Phamda\Phamda;

class ListDocFunctionBuilder
{
    public static function getSection(FunctionWrap $function)
    {
        return implode("\n", [
            '',
            '',
            sprintf('.. _%s:', $function->getName()),
            '',
            $function->getName(),
            str_repeat('-', strlen($function->getName())),
            sprintf('``%s``', ((new MethodSignatureBuilder($function))->getSignature())),
            '',
            self::getSummary($function),
            '',
            self::getExamples($function),
        ]);
    }

    private static function getSummary(FunctionWrap $function)
    {
        $process = Phamda::pipe(
            Phamda::implode("\n"),
            Phamda::explode('`'),
            Phamda::implode('``')
        );

        return $process($function->getComment()->summary);
    }

    private static function getExamples(FunctionWrap $function)
    {
        $process = Phamda::pipe(
            Phamda::construct(CommentExampleBuilder::class),
            Phamda::invoker(0, 'getRows'),
            Phamda::map(Phamda::concat('    ')),
            Phamda::ifElse(Phamda::isEmpty(), Phamda::identity(), Phamda::merge(['.. code-block:: php', ''])),
            Phamda::implode("\n")
        );

        return $process($function);
    }
}
