<?php

namespace Blog;

use Slim\Http\Request;

class Utilities
{
    static function getPDOParams(array $keys, Request $request):array
    {
        $vals = [];
        foreach ($keys as $k) {
            $vals[$k] = $request->getParam($k);
        }
        return $vals;
    }
}