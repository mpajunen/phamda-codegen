<?php

namespace Phamda\Data;

use Faker\Factory;
use Phamda\Phamda;

class Builder
{
    private $factory;

    public function __construct()
    {
        $this->factory = Factory::create();
    }

    public function create()
    {
        echo $this->printList($this->createProducts());
    }

    private function createProducts()
    {
        $createProduct = function() {
            $product = [
                'number'    => $this->factory->numberBetween(10000, 99999),
                'category'  => strtoupper($this->factory->randomLetter . $this->factory->randomLetter . $this->factory->randomLetter),
                'weight'    => $this->factory->randomFloat(1, 0.1, 100.0),
                'price'     => $this->factory->randomFloat(2, 5.00, 1000.00),
            ];

            $keys = array_keys($product);
            shuffle($keys);

            return array_merge(array_flip($keys), $product);
        };

        return array_map($createProduct, range(0, 7));
    }

    private function printList(array $list)
    {
        $format = function ($value) {
            return is_string($value) ? "'$value'" : $value;
        };
        $combine = function ($value, $key) {
            return "'$key' => $value";
        };
        $concat = function ($separator, array $values) {
            return implode($separator, $values);
        };
        $createRow = Phamda::pipe(
            Phamda::map($format),
            Phamda::map($combine),
            function (array $row) use ($concat) { return $concat(', ', $row); },
            function ($row) { return '[' . $row . '],'; }
        );

        return $concat("\n", Phamda::map($createRow, $list));
    }
}
