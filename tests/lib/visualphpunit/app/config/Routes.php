<?php

namespace app\config;

class Routes {

    public static function get_routes() {
        return array(
            array(array('get', 'post'), '/', function($request) {
                $controller = new \app\controller\Home();
                return $controller->call('index', $request);
            }),

            array('get', '/archives', function($request) {
                $controller = new \app\controller\Archives();
                return $controller->call('index', $request);
            }),

            array(array('get', 'post'), '/graphs', function($request) {
                $controller = new \app\controller\Graph();
                return $controller->call('index', $request);
            }),

            array('get', '/file-list', function($request) {
                $controller = new \app\controller\FileList();
                return $controller->call('index', $request);
            }),

            array('get', '/help', function($request) {
                $controller = new \app\controller\Home();
                return $controller->call('help', $request);
            }),

            // 404
            array('get', '*', function($request) {
                return array(
                    'status' => 404,
                    'body'   => '<h1>Not Found</h1>'
                );
            })
        );
    }
}

?>
