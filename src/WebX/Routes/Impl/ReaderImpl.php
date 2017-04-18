<?php
/**
 * User: niclas
 * Date: 4/28/16
 * Time: 10:47 AM
 */

namespace WebX\Impl;


use DateTime;
use Tictac\Util\Arrays\Api\ArrayView;
use WebX\Routes\Api\Reader;

class ReaderImpl implements Reader
{
    /**
     * @var array
     */
    private $array;


    /**
     * @var bool
     */
    private $writable;

    public function __construct(array $array = null)
    {
        $this->array = $array!==null ? $array : [];
    }

    public function keys() {
        return array_keys($this->array);
    }

    public function hasKey($path)
    {
        if(is_string($path)) {
            $path = explode(".", $path);
        } else if ($path===null) {
            return true;
        } else if (!is_array($path) && !is_scalar($path)) {
            throw new \Exception("\$path in Reader must be either a '.'-notated string, a scalar value or an array of path segments.");
        }
        if(count($path)===1) {
            if(array_key_exists($path[0],$this->array)) {
                return true;
            }
        } else {
            $value = $this->array;
            while (($key = array_shift($path))) {
                if(array_key_exists($key,$value)) {
                    if(count($path)===0) {
                        return true;
                    } else {
                        $value = $value[$key];
                    }
                }
            }
        }
        return false;
    }


    public function asAny($key, $default = null)
    {
        if(NULL !== ($value = $this->get($key))) {
            return $value;
        }
        return $default;
    }


    public function asInt($key, $default = null)
    {
        if(NULL !== ($value = $this->get($key))) {
            return is_int($value) ? $value : (is_scalar($value) ? intval($value) : $default);
        }
        return $default;
    }

    public function asFloat($key, $default = null)
    {
        if(NULL !== ($value = $this->get($key))) {
            return is_float($value) ? $value : (is_scalar($value) ? floatval($value) : $default);
        }
        return $default;
    }

    public function asBool($key, $default = null)
    {
        if(null !== ($value = $this->get($key))) {
            return $value ? true : false;
        }
        return $default;
    }

    public function asDate($key, $default = null)
    {
        if(NULL !== ($value = $this->get($key))) {
            if($value instanceof DateTime) {
                return $value;
            } else if(is_string($value)) {
                $date = new DateTime();
                $date->setTimestamp(strtotime($value));
                return $date;
            } else if (is_int($value) || is_long($value)) {
                $date = new DateTime();
                $date->setTimestamp($value);
                return $date;
            }
        }
        return $default;
    }

    public function asArray($key=null, $default = null)
    {
        if (NULL !== ($value = $this->get($key))) {
            return is_array($value) ? $value : $default;
        }
        return $default;
    }

    public function asString($key, $default = "")
    {
        if(NULL !== ($value = $this->get($key))) {
            return is_string($value) ? trim($value) : (is_scalar($value) ? trim(strval($value)) : $default);
        }
        return $default;
    }

    public function asReader($key)
    {
        if(NULL!==($value = $this->get($key))) {
            return is_array($value) ? new ReaderImpl($value) : null;
        }
        return NULL;
    }

    private function get($path=null){
        if(is_string($path)) {
            $path = explode(".", $path);
        } else if ($path===null) {
            return $this->array;
        } else if (!is_array($path) && !is_scalar($path)) {
            throw new \Exception("\$path in ArrayReader must be either a '.'-notated string, a scalar value or an array of path segments.");
        }
        if(count($path)===1) {
            if(array_key_exists($path[0],$this->array)) {
                return $this->array[$path[0]];
            }
            return null;
        } else {
            $value = $this->array;
            while (($key = array_shift($path))) {
                if(is_array($value) && array_key_exists($key,$value)){
                    $value = $value[$key];
                    if ((count($path) === 0) || ($value === NULL)) {
                        return $value;
                    }
                } else {
                    return null;
                }
            }
            return $value;
        }
    }

    private static function mergeRecursive(array &$array1, array $array2) {
        foreach($array2 as $key => $val2) {
            if (is_array($val2) && isset($array1[$key]) && is_array($array1[$key])) {
                $val1 = &$array1[$key];
                self::mergeRecursive($val1,$val2);
            } else {
                $array1[$key] = $val2;
            }
        }
    }


}