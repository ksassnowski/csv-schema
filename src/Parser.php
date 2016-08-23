<?php

namespace Sassnowski\CsvSchema;

use League\Csv\Reader;
use Sassnowski\CsvSchema\Exceptions\UnsupportedTypeException;

class Parser
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $defaultDelimiter = ',';

    /**
     * @var string
     */
    private $defaultEnclosure = '"';

    /**
     * @var string
     */
    private $defaultEscape = '\\';

    /**
     * Parser constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param $input
     *
     * @return array
     */
    public function fromString($input)
    {
        return $this->parse(Reader::createFromString($input));
    }

    /**
     * @param string $filename
     *
     * @return array
     */
    public function fromFile($filename)
    {
        return $this->parse(Reader::createFromPath($filename));
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function parseRow(array $columns)
    {
        return collect($columns)->zip($this->config['schema'])->flatMap(function ($pair, $index) {
            list($value, $type) = $pair;

            $parsed = $this->getValue($type, $value);

            $key = array_keys($this->config['schema'])[$index];

            return [$key => $parsed];
        })->all();
    }

    /**
     * @param Reader $reader
     *
     * @return array
     */
    protected function parse(Reader $reader)
    {
        $reader->setDelimiter($this->getConfigValue('delimiter', $this->defaultDelimiter));
        $reader->setEnclosure($this->getConfigValue('enclosure', $this->defaultEnclosure));
        $reader->setEscape($this->getConfigValue('escape', $this->defaultEscape));

        return collect($reader)->map(function ($row) {
            return (object) $this->parseRow($row);
        })->all();
    }

    /**
     * @param string $type
     * @param string $value
     *
     * @return array|mixed
     *
     * @throws UnsupportedTypeException
     */
    protected function getValue($type, $value)
    {
        $method = $this->getMethodName($type);

        if (!method_exists($this, $method)) {
            throw new UnsupportedTypeException($type);
        }

        $args = $this->getArguments($type);

        return call_user_func_array([$this, $method], [$value, $args]);
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    protected function getArguments($type)
    {
        if ($this->isArrayType($type)) {
            return explode(':', $type)[1];
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getMethodName($type)
    {
        if ($this->isArrayType($type)) {
            $type = explode(':', $type)[0];
        }

        return 'parse'.ucfirst($type);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isArrayType($type)
    {
        return substr($type, 0, 5) === 'array';
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function parseString($value)
    {
        return (string) $value;
    }

    /**
     * @param string $value
     *
     * @return int
     */
    protected function parseInt($value)
    {
        return (int) $value;
    }

    /**
     * @param string $value
     *
     * @return float
     */
    public function parseFloat($value)
    {
        return (float) $value;
    }

    /**
     * @param string $string
     * @param string $delimiter
     *
     * @return array
     */
    protected function parseArray($string, $delimiter)
    {
        return explode($delimiter, trim($string));
    }

    /**
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    protected function getConfigValue($key, $default)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
}
