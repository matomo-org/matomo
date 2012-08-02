<?php
    require dirname(__DIR__) . '/config/bootstrap.php';

    $request = new \nx\core\Request();
    $dispatcher = new \nx\core\Dispatcher();
    $dispatcher->handle($request, \app\config\Routes::get_routes());
?>
