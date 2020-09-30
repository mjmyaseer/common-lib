<?php

/*
 * This file is part of the Request Handler.
 *
 * Yaseer <gmjmyaseer@gmail.com>
 *
 */

namespace Linx\Lib\RequestHandler;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Linx\Lib\RequestHandler\Exceptions\QueryParserException;

/**
 * Class QueryParser
 * @package Linx\Lib\RequestHandler
 */
class QueryParser
{
    const EQUAL = 1;

    const LIKE = 2;

    const BEGIN_WITH = 3;

    const END_WITH = 4;

    const BETWEEN = 5;

    const IN = 6;

    const CUSTOM = 0;

    const NOT_EQUAL = 7;

    /**
     * @var Builder|\Illuminate\Database\Query\Builder
     */
    private $builder;

    private $supportedFilters = [
        self::EQUAL => 'equal',
        self::NOT_EQUAL => 'notEqual',
        self::LIKE => 'like',
        self::BEGIN_WITH => 'beginWith',
        self::END_WITH => 'endWith',
        self::BETWEEN => 'between',
        self::IN => 'in'
    ];

    private $mappings;


    /**
     * QueryParser constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * Set supported query builder
     *
     * @param Builder|\Illuminate\Database\Query\Builder $builder Supported Query Builder
     */
    public function setBuilder($builder)
    {
        $this->checkBuilderSupport($builder);

        $this->builder = $builder;
    }

    private function checkBuilderSupport($builder)
    {
        if (!$builder instanceof \Illuminate\Database\Query\Builder && !$builder instanceof \Illuminate\Database\Eloquent\Builder) {
            QueryParserException::builderNotSupported();
        };
    }

    public function setMappings($mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Apply filters
     *
     * @param Collection $filters Supported filters
     */
    public function applyFilters(Collection $filters)
    {
        foreach ($filters as $filter) {

            if(!$this->isMappingsAvailableFor($filter['field']))
            {
                continue;
            }

            $mapping = $this->getMappingsFor($filter['field']);

            if (!$mapping['autoAppend'] || !isset($filter['value']) || $filter['value'] === "") {
                continue;
            }

            $field = $mapping['field'];
            $value = $filter['value'];

            if (isset($mapping['callBack'])) {

                $value = $mapping['callBack']($filter['value']);
            }

            $operator = $filter['operator'];
            $boolean = !empty($filter['boolean']) ? $filter['boolean'] : 'and';

            $this->filterSupported($operator);

            $this->{$this->supportedFilters[$operator]}($field, $value, $boolean);

        }
    }

    /**
     * @param string $field Request field
     * @return mixed
     */
    public function getMappingsFor($field)
    {
        $mapping = $this->mappings[$field];

        if (!isset($mapping['autoAppend'])) {
            $mapping['autoAppend'] = true;
        }

        return $mapping;
    }

    public function isMappingsAvailableFor($field)
    {
        return isset($this->mappings[$field]);
    }

    private function filterSupported($filter)
    {
        if (!isset($this->supportedFilters[$filter])) {
            QueryParserException::unsupportedFilter();
        }
    }

    public function applyFields($requestFields)
    {

        $fields = [];
        foreach ($requestFields as $field) {
            $fields[] = $this->getField($field);
        }

        $fields = count($fields) > 0 ? $fields : ['*'];

        return $this->builder->addSelect($fields);
    }

    private function getField($field)
    {
        if (!isset($this->mappings[$field])) {
            QueryParserException::keyDoseNotExist($field);
        }

        return $this->mappings[$field]['field'];
    }

    public function applySorts($requestFields)
    {

        foreach ($requestFields as $requestField) {

            $field = $this->getField($requestField['field']);

            $this->builder->orderBy($field, $requestField['order']);
        }
    }

    private function equal($field, $value, $boolean = 'and', array $options = [])
    {
        $this->builder->where($field, '=', $value, $boolean);
    }

    private function notEqual($field, $value, $boolean = 'and', array $options = [])
    {
        $this->builder->where($field, '!=', $value, $boolean);
    }

    private function beginWith($field, $value, $boolean = 'and', array $options = [])
    {
        $this->builder->where($field, 'LIKE', "$value%", $boolean);
    }

    private function endWith($field, $value, $boolean = 'and', array $options = [])
    {
        $this->builder->where($field, 'LIKE', "%$value", $boolean);
    }

    private function like($field, $value, $boolean = 'and', array $options = [])
    {
        $this->builder->where($field, 'LIKE', "%$value%", $boolean);
    }

    private function between($field, $value, $boolean = 'and', array $options = [])
    {
        if (!is_array($value)) {
            QueryParserException::invalidBetweenValue();
        }

        $this->builder->whereBetween($field, $value, $boolean);
    }

    private function in($field, $value, $boolean = 'and', array $options = [])
    {
        if (!is_array($value)) {
            QueryParserException::invalidBetweenValue();
        }

        $this->builder->whereIn($field, $value, $boolean);
    }

    private function custom($field, $value, $boolean)
    {

    }

    private function hasField($field)
    {
        return isset($this->map[$field]);
    }
}