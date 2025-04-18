<?php

namespace xamned\framework\form;

use InvalidArgumentException;

class FormRequest extends AbstractFormRequest
{
    protected array $dynamicAttributes = [];

    public function addAttribute(string $name, mixed $value = null): void
    {
        if (property_exists($this, $name) === true 
            || array_key_exists($name, $this->dynamicAttributes) === true
        ) {
            throw new InvalidArgumentException('Аттрибут уже существует.');
        }

        $this->dynamicAttributes[$name] = $value;
    }

    public function addAttributes(array $names): void
    {
        foreach ($names as $name => $value) {
            if (is_int($name) === true) {
                $this->addAttribute($value);
                continue;
            }

            $this->addAttribute($name, $value);
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->dynamicAttributes) === true) {
            return $this->dynamicAttributes[$name];
        }

        return $this->$name;
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->dynamicAttributes) === true) {
            $this->dynamicAttributes[$name] = $value;
            return;
        }

        $this->$name = $value;
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this->dynamicAttributes) === true) {
            return isset($this->dynamicAttributes[$name]);
        }

        return isset($this->$name);
    }

    public function __unset($name)
    {
        if (array_key_exists($name, $this->dynamicAttributes) === true) {
            unset($this->dynamicAttributes[$name]);
            return;
        } 
        
        unset($this->$name);
    }

    protected function getAttributes(): array
    {
        return array_merge(array_keys($this->dynamicAttributes), parent::getAttributes());
    }
}
