<?php

namespace Linx\Lib\RestHandler\Contracts;

use Illuminate\Support\Collection;
use Linx\Lib\RestHandler\Exception\RestHandlerException;
use Linx\Lib\RestHandler\RestHandler;

/**
 * Interface RestHandlerInterface
 * @package Linx\Lib\RestHandler\Contracts
 */
interface RestHandlerInterface
{
    /**
     * Add an item to the response
     *
     * @param $data
     * @param string $namespace eg:- data | page_data
     *
     * @return RestHandler
     * @throws RestHandlerException
     */
    public function toItem($data, string $namespace = 'data');

    /**
     * Add a collection to the response
     *
     * @param array|Collection $data
     * @param string $namespace eg:- data | page_data
     * @return RestHandler
     */
    public function toCollection($data, string $namespace = 'data');

    /**
     * Paginate a collection
     *
     * @param array $data
     * @param string $namespace eg:- {data: [], pagination: {count: 10}}
     * @return RestHandler
     * @throws RestHandlerException
     */
    public function paginate($data, $namespace = 'pagination');

    /**
     * @param $status
     * @param array $headers
     * @return string Json response
     * @throws RestHandlerException
     */
    public function toJson(int $status, array $headers = []);

    /**
     * Append list of errors to the request
     *
     * @param array $errors Array of errors
     * @param string $namespace eg:- {data: [], errors: [{error1}, {error2}]}
     * @return mixed
     * @throws RestHandlerException
     */
    public function withErrors(array $errors = [], string $namespace = 'errors');

    /**
     * Append meta data to the response
     *
     * @param array $meta
     * @param string $namespace eg:- {data: [], meta: {url: http://somedomain.com}}
     * @return mixed
     * @throws RestHandlerException
     */
    public function withMeta(array $meta = [], string $namespace = 'meta'): RestHandler;

    public function __call($name, $arguments);
}