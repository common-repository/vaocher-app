<?php

class VaocherAppModelAbstract
{
    /**
     * @var array
     */
    protected $attributes = [];

    public function __construct($attributes = [])
    {
        if ($attributes) {
            $this->fill($attributes);
        }
    }

    public function __get($key)
    {
        return array_key_exists($key, $this->attributes)
            ? $this->attributes[$key]
            : null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param  array  $attributes
     * @return $this
     */
    public function fill($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }
}