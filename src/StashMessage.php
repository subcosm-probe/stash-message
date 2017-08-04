<?php
/**
 * This file is part of the subcosm-probe.
 *
 * (c)2017 Matthias Kaschubowski
 *
 * This code is licensed under the MIT license,
 * a copy of the license is stored at the project root.
 */

namespace Singularity\Http\Stash;


use Singularity\Http\Stash\Exceptions\StashMessageException;
use Singularity\Primitive\Traits\Stash\ArrayTrait;

class StashMessage
{
    use ArrayTrait;

    private $status = 'ok';

    /**
     * Stash constructor.
     * @param int|string|object $status
     * @param array $items
     * @throws StashMessageException when the status parameter is not string, or object implementing __toString() or integer.
     */
    public function __construct($status, array $items = [])
    {
        if ( is_object($status) && method_exists($status, '__toString') ) {
            $status = (string) $status;

            if ( is_numeric($status) ) {
                $status = (int) $status;
            }
        }

        if ( ! is_string($status) || ! is_int($status) ) {
            throw new StashMessageException(
                'Status parameter must be a string or integer, '.gettype($status).' given'
            );
        }

        $this->status = $status;

        $this->storeToStashFromArray($items);
    }

    /**
     * Factory method for a common message stash.
     *
     * @param $status
     * @param array $items
     * @return StashMessage
     */
    static public function from($status, array $items = []): StashMessage
    {
        return new static($status, $items);
    }

    /**
     * Factory method for a generalized message stash.
     *
     * @param $status
     * @param string $message
     * @param array $items
     * @return StashMessage
     */
    static public function message($status, string $message, array $items = []): StashMessage
    {
        $items = array_replace($items, ['message' => $message]);

        return new static($status, $items);
    }

    /**
     * Factory method for a exception message stash.
     *
     * @param $status
     * @param \Exception $exception
     * @return StashMessage
     */
    static public function exception($status, \Exception $exception): StashMessage
    {
        $reflection = new \ReflectionClass($exception);

        $items = [
            'message' => $exception->getMessage(),
            'class' => $reflection->getName(),
            'exception' => $reflection->getShortName(),
            'code' => $exception->getCode(),
        ];

        return new static($status, $items);
    }


    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return array_replace($this->fetchStashStorage(), ['status' => $this->status]);
    }
}