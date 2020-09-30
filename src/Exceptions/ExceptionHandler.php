<?php
declare(strict_types=1);

namespace Linx\Lib\Exceptions;

/**
 * Class ExceptionHandler
 * @package Linx\Lib\Exceptions
 *
 * Handle exceptions and convert them to structured messages based on their types
 */
class ExceptionHandler
{

    /**
     * @var array List of exceptions
     */
    private $exceptions = [];

    /**
     * @var array General error
     */
    private $unknownException = [
        'message' => 'Unknown error occurred',
        'code' => 1000,
    ];

    /**
     * Render exceptions and return a list of errors
     *
     * @param \Exception $exception
     * @return array
     */
    public function render(\Exception $exception)
    {
        if ($this->isDevEnv() || $this->publicVisible($exception)) {
            $this->exceptions[] = $this->prepareExceptions($exception);
        }

        $previous = $exception->getPrevious();
        while ($previous != null) {
            if ($this->isDevEnv() || $this->publicVisible($previous)) {
                $this->exceptions[] = $this->prepareExceptions($previous);
            }

            $previous = $previous->getPrevious();
        }

        //if no exceptions to show but actually there are exceptions
        //just show a general exception
        return !empty($this->exceptions) ? $this->exceptions : [$this->unknownException];
    }

    /**
     * Check the application environment
     *
     * @return mixed
     */
    private function isDevEnv()
    {
        return env('APP_DEBUG');
    }

    /**
     * Check public visibility of an exception
     * eg:- DomainExceptions are public visible
     *
     * @param $exception
     * @return bool
     */
    private function publicVisible($exception)
    {
        return $exception instanceof DomainException;
    }

    /**
     * Convert exception to an formatted error
     *
     * @param \Throwable $e
     *
     * @return array
     */
    private function prepareExceptions(\Throwable $e)
    {
        $error = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ];

        if ($this->isDevEnv()) {
            $error['type'] = class_basename($e);
            $error['line'] = $e->getLine();
            $error['file'] = $e->getFile();
            $error['trace'] = $e->getTraceAsString();
        }

        return $error;
    }
}