<?php

namespace Linx\Lib\RequestHandler\Exceptions;


use Linx\Lib\Exceptions\SystemException;

class RequestMapperException extends SystemException
{

    public static function keyDoseNotExist($key)
    {
        throw new RequestMapperException("The key [$key] dose not exist in the provided mappings");
    }

    public static function builderNotSupported()
    {
        throw new RequestMapperException("Builder must be a instance of [\\Illuminate\\Database\\Query\\Builder] or 
        \\Illuminate\\Database\\Eloquent\\Builder");
    }
}