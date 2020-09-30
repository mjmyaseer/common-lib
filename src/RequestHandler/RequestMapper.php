<?php

/*
 * This file is part of the Request Handler.
 *
 * Gayan Yapa <gayan@hq.pickme.lk>
 *
 */

namespace Linx\Lib\RequestHandler;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class RequestMapper
 * @package Linx\Lib\RequestHandler
 */
class RequestMapper
{

    protected $filters = [];
    /**
     * Array of mappings
     *
     * @var array
     */
    private $map = [];
    /**
     * @var Request
     */
    private $request;

    /**
     * @var QueryParser
     */
    private $parser;

    /**
     * RequestMapper constructor.
     * @param Request $request
     * @param QueryParser $parser
     */
    public function __construct(Request $request, QueryParser $parser)
    {
        $this->request = $request;
        $this->parser = $parser;
    }

    /**
     * Set Array of mappings
     *
     * @param array $mappings Array of mappings
     */
    public function setMappings($mappings)
    {
        $this->map = $mappings;
    }

    /**
     * Get mappings for specific field
     *
     * @param string $field Request field
     * @return mixed
     */
    public function getMappingsFor($field)
    {
        return $this->map[$field];
    }

    /**
     * Apply query builder to mapping
     *
     * @param \Illuminate\Database\Query\Builder | \Illuminate\Database\Query\Builder $builder
     * @return mixed
     */
    public function applyFilters($builder)
    {
        $filters = $this->fetchFilters();

        $this->parser->setBuilder($builder);
        $this->parser->setMappings($this->map);
        $this->parser->applyFilters($filters);

        return $this->parser->getBuilder();
    }

    /**
     * Extract filters from request
     *
     * @return array
     */
    public function fetchFilters()
    {
        $filters = !empty($this->request->get('filters')) ? json_decode($this->request->get('filters'), true) : [];

        return $filters;
    }

    /**
     * Apply SELECT fields for a supported query builder
     *
     * @param \Illuminate\Database\Query\Builder | \Illuminate\Database\Query\Builder $builder
     * @return \Illuminate\Database\Query\Builder | \Illuminate\Database\Query\Builder
     */
    public function applyFields($builder)
    {
        $this->parser->setBuilder($builder);
        $this->parser->applyFields($this->fetchFields());

        return $this->parser->getBuilder();
    }

    /**
     * Extract fields from request
     *
     * @return array
     */
    private function fetchFields()
    {
        $fields = !empty($this->request->get('fields')) ? json_decode($this->request->get('fields'), true) : [];

        return $fields;
    }

    /**
     * Apply sortings for a supported query builder
     *
     * @param \Illuminate\Database\Query\Builder | \Illuminate\Database\Query\Builder $builder
     * @return \Illuminate\Database\Query\Builder | \Illuminate\Database\Query\Builder
     */
    public function applySorts($builder)
    {
        $this->parser->setBuilder($builder);
        $this->parser->applySorts($this->fetchSortings());

        return $this->parser->getBuilder();
    }

    /**
     * Extract sortings from request
     *
     * @return array
     */
    private function fetchSortings()
    {
        $fields = !empty($this->request->header('sorts')) ? json_decode($this->request->header('sorts'), true) : [];

        return $fields;
    }

    /**
     * Get raw requested filters
     *
     * @return Collection
     */
    public function getRawFilters()
    {
        return new Collection($this->fetchFilters());
    }

    /**
     * Get filter by name
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getRawFilter($field)
    {
        return array_first($this->fetchFilters(), function ($element, $index) use ($field) {
            return $element['field'] == $field;
        });
    }

    /**
     * Get raw requested fields
     *
     * @return array
     */
    public function getRawFields()
    {
        return $this->fetchFields();
    }

    public function getPaging()
    {
        $paging = !empty($this->request->get('paging')) ? json_decode($this->request->get('paging'), true) : [
            'perPage' => 25
        ];

        return $paging;
    }


}