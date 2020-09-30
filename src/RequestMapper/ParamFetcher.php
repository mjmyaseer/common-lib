<?php


namespace Linx\Lib\RequestMapper;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use function json_decode;

class ParamFetcher
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getFilters(): Collection
    {
        $filters = json_decode($this->request->header('filters'));
        $filters = $filters ? $filters : [];

        return new Collection($filters);
    }

    public function getSortings()
    {

    }

    public function getPagination(): array
    {
        $pagination = json_decode($this->request->header('pagin'));
        $pagination = $pagination ? (array)$pagination : [];

        return $pagination;
    }
}