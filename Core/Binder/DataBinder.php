<?php

namespace Core\Binder;

class DataBinder
{
    public function bind($formData, $className): mixed
    {
        $object = new $className();

        foreach ($formData as $key => $value) {
            $methodName = "set" . implode("",
                    array_map(function ($el) {
                        return ucfirst($el);
                    }, explode("_", $key)));

            if (method_exists($object, $methodName)) {
                $object->$methodName($value);
            }
        }
        return $object;
    }
}