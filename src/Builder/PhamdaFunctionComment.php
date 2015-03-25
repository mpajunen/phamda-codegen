<?php

namespace Phamda\Builder;

use Phamda\Phamda;

class PhamdaFunctionComment
{
    public $parameters;
    public $return;
    public $summary;

    public function __construct($innerDoc)
    {
        $getRows = Phamda::pipe(
            Phamda::explode("\n"),
            Phamda::slice(1, -1),
            Phamda::map(Phamda::pipe(
                Phamda::explode('*'),
                Phamda::last(),
                Phamda::curry('trim')
            ))
        );

        $rows = $getRows($innerDoc);

        $hasSubstring = function ($subString, $string) { return strpos($string, $subString) !== false; };

        $firstParameter = Phamda::findIndex(Phamda::curry($hasSubstring, '@param'), $rows);
        $return         = Phamda::findIndex(Phamda::curry($hasSubstring, '@return'), $rows);

        $this->summary    = Phamda::slice(0, ($firstParameter ? $firstParameter : $return) - 1, $rows);
        $this->parameters = Phamda::slice($firstParameter, $return - 1, $rows);
        $this->return     = $rows[$return];
    }
}
