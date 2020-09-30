<?php

namespace Linx\Lib\RestHandler\Exception;


use Linx\Lib\Exceptions\DomainException;

class RestHandlerException extends DomainException
{
    public $code = 2000;

    public static function InputMustBeArray()
    {
        throw new RestHandlerException('Input must be an array');
    }

    public static function paginatorNotSupported()
    {
        throw new RestHandlerException('Unsupported paginator');
    }
}