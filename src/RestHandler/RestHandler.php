<?php
declare(strict_types=1);

namespace Linx\Lib\RestHandler;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\TransformerAbstract;
use Linx\Lib\RestHandler\Contracts\RestHandlerInterface;
use Linx\Lib\RestHandler\Exception\RestHandlerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RestHandler
 * @package Linx\Lib\RestHandler
 */
class RestHandler implements RestHandlerInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var array Request Data
     */
    private $data;
    private $shouldTransform;
    private $transformer;

    /**
     * RestHandler constructor.
     */
    public function __construct(\Illuminate\Http\Response $response)
    {
        $this->response = $response;
    }

    /**
     * Add an item to the response
     *
     * @param array $data
     * @param string $namespace eg:- data | page_data
     * @return RestHandler
     * @throws RestHandlerException
     */
    public function toItem($data, string $namespace = 'data'): RestHandler
    {
        if ($this->shouldTransform) {
            $data = $this->transformItem($data);
        }

        $this->data[$namespace] = $data;

        return $this;
    }

    /**
     * Transform an item using provided transformer
     *
     * @param array $data
     * @return array Transformed data array
     */
    private function transformItem($data): array
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $item = new Item($data, $this->transformer);
        return $manager->createData($item)->toArray();

    }

    /**
     * Add a collection to the response
     *
     * @param array|\Illuminate\Support\Collection $data
     * @param string                               $namespace eg:- data | page_data
     *
     * @return RestHandler
     */
    public function toCollection($data, string $namespace = 'data'): RestHandler
    {
        $data = $data instanceof \Illuminate\Database\Eloquent\Collection ? $data->toArray() : $data;

        if ($this->shouldTransform) {
            $data = $this->transformCollection($data);
        }

        $this->data[$namespace] = $data;

        return $this;
    }

    /**
     * Transform a collection using provided transformer
     *
     * @param array $data
     * @return array Transformed data array
     */
    private function transformCollection($data): array
    {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $collection = new Collection($data, $this->transformer);
        $transformedData = $manager->createData($collection)->toArray();
        return $transformedData['data'];
    }

    /**
     * Paginate a collection
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param string $namespace eg:- {data: [], pagination: {count: 10}}
     * @return RestHandler
     */
    public function paginate($paginator, $namespace = 'data')
    {
        if (!$this->supportedPaginator()) {
            RestHandlerException::paginatorNotSupported();
        }

        $this->data['pagination'] = $this->getPaginateMeta($paginator);

        $data = $this->getPaginatedData($paginator);

        if ($this->shouldTransform) {
            $data = $this->transformCollection($data);
        }

        $this->data[$namespace] = $data;

        return $this;
    }

    private function supportedPaginator()
    {
        return true;
    }

    /**
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginatedData
     * @return array
     */
    private function getPaginateMeta($paginatedData)
    {
        return [
            'count' => $paginatedData->total(),
            'per_page' => $paginatedData->perPage(),
            'current_page' => $paginatedData->currentPage(),
            'next_page' => $paginatedData->nextPageUrl(),
        ];
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @return mixed
     */
    private function getPaginatedData($paginator)
    {
        return $paginator->items();
    }

    /**
     * @param callable|TransformerAbstract $transformer
     * @return $this
     */
    public function transformWith($transformer)
    {
        $this->shouldTransform = true;
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @param       $status
     * @param array $headers
     *
     * @return \Illuminate\Http\Response
     */
    public function toJson(int $status, array $headers = []): \Illuminate\Http\Response
    {
        return $this->response->create($this->data, $status, $headers);
    }

    /**
     * Append list of errors to the request
     *
     * @param array $errors Array of errors
     * @param string $namespace eg:- {data: [], errors: [{error1}, {error2}]}
     * @return RestHandler
     * @throws RestHandlerException
     */
    public function withErrors(array $errors = [], string $namespace = 'errors')
    {
        $this->data[$namespace] = $errors;

        return $this;
    }

    /**
     * Append error to the request
     *
     * @param array $error Errors
     * @param string $namespace eg:- {data: [], errors: [{error1}, {error2}]}
     * @return RestHandler
     * @throws RestHandlerException
     */
    public function withError(array $error = [], string $namespace = 'errors'): RestHandler
    {
        $this->data[$namespace][] = $error;

        return $this;
    }

    /**
     * Append meta data to the response
     *
     * @param array $meta
     * @param string $namespace eg:- {data: [], meta: {url: http://somedomain.com}}
     * @return RestHandler
     * @throws RestHandlerException
     */
    public function withMeta(array $meta = [], string $namespace = 'meta'): RestHandler
    {
        $this->data[$namespace] = $meta;

        return $this;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }

}