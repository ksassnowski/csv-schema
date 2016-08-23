<?php

namespace Sassnowski\CsvSchema\Exceptions;

use Exception;

class UnsupportedTypeException extends Exception
{
    /**
     * UnsupportedTypeException constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct("Unsupported type '$type' in schema");
    }
}
