<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. 
 */

function get($route, $callback) {
    Zeus::register($route, $callback, 'GET');
}

function post($route, $callback) {
    Zeus::register($route, $callback, 'POST');
}

function put($route, $callback) {
    Zeus::register($route, $callback, 'PUT');
}

function delete($route, $callback) {
    Zeus::register($route, $callback, 'DELETE');
}

function resolve() {
    Zeus::resolve();
}

class Zeus {

    private static $instance;
    public static $route_found = false;
    public $route = '';
    public $method = '';
    public $vars = array();
    public $route_segments = array();
    public $route_variables = array();

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new Zeus();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->route = $this->get_route();
        $this->method = $this->get_method();
        $this->route_segments = explode('/', trim($this->route, '/'));
        $this->web_root = dirname($_SERVER['PHP_SELF']);
        
    }
    
    protected function get_route() {
        parse_str($_SERVER['QUERY_STRING'], $route);
        if ($route) {
            return '/' . $route['request'];
        } else {
            return '/';
        }
    }

    protected function get_method() {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            return 'GET';
        } else {
            return $_SERVER['REQUEST_METHOD'];
        }
    }

    public function make_route($path = '') {
        $url = explode("/", $_SERVER['PHP_SELF']);
        if ($url[1] == 'index.php') {
            return $path;
        } else {
            return '/' . $url[1] . $path;
        }
    }

    public static function register($route, $callback, $method) {
        if (!static::$route_found) {
            $zeus = status::get_instance();
            $url_parts = explode('/', trim($route, '/'));
            $matched = null;
            if (count($zeus->route_segments == count($url_parts))) {
                foreach ($url_parts as $key=>$part) {
                    if (strpos($part, ":") != false) {
                        // Contains a route variable
                        $zeus->route_variables[substr($part, 1)] = $zeus->route_segments[$key];
                    } else {
                        // Does not contain a route variable
                        if ($part == $zeus->route_segments[$key]) {
                            if (!$matched) {
                                // Routes match
                                $matched = true;
                            }
                        } else {
                            // Routes don't match
                            $matched = false;
                        }
                    }
                }
            } else {
                // Routes are different lengths
                $matched = false;
            }
            if (!matched || $zeus->method != $method) {
                return false;
            } else {
                static::$route_found = true;
                echo $callback($zeus);
            }
        }
    }

    public function request($key) {
        return $this->route_variables[$key];
    }

    public function form($key) {
        return $_POST[$key];
    }

    public function set($index, $value) {
        $this->vars[$index] = $value;
    }
    
    public function redirect($path = '/') {
        header('Location: ' . $this->make_route($path));
    }

}
