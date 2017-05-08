<?php

namespace WebX\Routes\Impl;

use WebX\Routes\Api\Path;
use WebX\Routes\Api\Routes;
use WebX\Routes\Api\RoutesException;

class PathImpl implements Path {

    private $pos;
    private $segments;
    private $path;

    public function __construct()  {
        $this->pos = -1;
        $full = trim($this->full()," /");
        $this->segments = $full ? explode("/",$full) : [];
    }

    public function remaining() {
        return array_slice($this->segments,$this->pos + 1);
    }

    public function move($dpos) {
        $this->pos += $dpos;
    }

    public function pop($condition) {
        if(is_string($condition)) {
            if ($condition === $this->next()) {
                $this->move(1);
                return 1;
            }
            return null;
        } else {
            return $condition ? true : null;
        }
    }

    public function next() {
        return $this->get($this->pos + 1);
    }

    public function reset($steps) {
        $this->pos-=$steps;
    }

    public function current() {
        return  $this->get($this->pos);
    }

    private function get($pos) {
        if($pos<0) {
            return null;
        } else {
            return $pos < count($this->segments) ? $this->segments[$pos] : null;
        }
    }

    public function full() {
        if(!$this->path) {
            $this->path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        }
        return $this->path;
    }

}
