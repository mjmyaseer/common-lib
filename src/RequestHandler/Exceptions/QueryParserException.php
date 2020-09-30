<?php
/**
 * Created by PhpStorm.
 * User: yaseer
 * Date: 9/29/2020
 * Time: 1:43 PM
 */

namespace Linx\Lib\RequestHandler\Exceptions;


use Linx\Lib\Exceptions\SystemException;

class QueryParserException extends SystemException
{

    public static function builderNotSupported()
    {
        throw new RequestMapperException("Builder must be a instance of [\\Illuminate\\Database\\Query\\Builder] or 
        \\Illuminate\\Database\\Eloquent\\Builder");
    }

    public static function unsupportedFilter()
    {
        throw new QueryParserException('Filter support not available for selected filter');
    }

    public static function keyDoseNotExist($key)
    {
        throw new RequestMapperException("The key [$key] dose not exist in the provided mappings");
    }

    public static function invalidBetweenValue()
    {
        throw new QueryParserException('Value must be an array for between');
    }

}