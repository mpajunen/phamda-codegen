<?php

namespace Phamda\Functions;

$functions = [
    'curried' => [

        'all'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return bool
         */
            function (callable $function, array $list) {
                foreach ($list as $value) {
                    if (! $function($value)) {
                        return false;
                    }
                }

                return true;
            },

        'any'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return bool
         */
            function (callable $function, array $list) {
                foreach ($list as $value) {
                    if ($function($value)) {
                        return true;
                    }
                }

                return false;
            },

        'eq'      =>
        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return bool
         */
            function ($a, $b) {
                return $a === $b;
            },

        'filter'  =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return array_filter($list, $function);
            },

        'map'     =>
        /**
         * @param callable $function
         * @param array    $list
         *
         * @return array
         */
            function (callable $function, array $list) {
                return array_map($function, $list);
            },

        'pick'    =>
        /**
         * @param array $names
         * @param array $item
         *
         * @return array
         */
            function (array $names, array $item) {
                $new = [];
                foreach ($names as $name) {
                    if (array_key_exists($name, $item)) {
                        $new[$name] = $item[$name];
                    }
                }

                return $new;
            },

        'pickAll' =>
        /**
         * @param array $names
         * @param array $item
         *
         * @return array
         */
            function (array $names, array $item) {
                $new = [];
                foreach ($names as $name) {
                    $new[$name] = isset($item[$name]) ? $item[$name] : null;
                }

                return $new;
            },

        'prop'    =>
        /**
         * @param string       $name
         * @param array|object $object
         *
         * @return mixed
         */
            function ($name, $object) {
                return is_object($object) ? $object->$name : $object[$name];
            },

        'propEq'  =>
        /**
         * @param string       $name
         * @param mixed        $value
         * @param array|object $object
         *
         * @return bool
         */
            function ($name, $value, $object) {
                return is_object($object)
                    ? $object->$name === $value
                    : $object[$name] === $value;
            },

        'reduce'  =>
        /**
         * @param callable $function
         * @param mixed    $initial
         * @param array    $list
         *
         * @return mixed
         */
            function (callable $function, $initial, array $list) {
                return array_reduce($list, $function, $initial);
            },

        'sort'    =>
        /**
         * @param callable $comparator
         * @param array    $list
         *
         * @return array
         */
            function (callable $comparator, array $list) {
                $newList = $list;

                usort($list, $comparator);

                return $newList;
            },

        'zip'     =>
        /**
         * @param array $a
         * @param array $b
         *
         * @return array
         */
            function (array $a, array $b) {
                $zipped = [];
                foreach (array_intersect_key($a, $b) as $key => $value) {
                    $zipped[$key] = [$value, $b[$key]];
                }

                return $zipped;
            },

        'zipWith' =>
        /**
         * @param callable $function
         * @param array    $a
         * @param array    $b
         *
         * @return array
         */
            function (callable $function, array $a, array $b) {
                $zipped = [];
                foreach (array_intersect_key($a, $b) as $key => $value) {
                    $zipped[$key] = $function($value, $b[$key]);
                }

                return $zipped;
            },
    ],
];
