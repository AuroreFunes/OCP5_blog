<?php

namespace AF\OCP5\Entity;

trait EntityFeature
{
    public function hydrate(array $datas)
    {
        foreach ($datas as $key => $value) {
            $method = $this->getSetterName($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    protected function getSetterName($attributeName)
    {
        $method = "set" . ucfirst($attributeName);

        while(false != ($pos = stripos($method, "_"))) {
            $method = substr($method, 0, $pos) . ucfirst(substr($method, $pos+1));
        }

        return $method;
    }
}