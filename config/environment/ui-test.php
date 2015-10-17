<?php

return array(

    'observers.global' => array(

        array('Request.dispatch.end', function (&$result) {
            // remove the port from URLs if any so UI tests won't fail if the port isn't 80
            $result = preg_replace('/localhost:[0-9]+/', 'localhost', $result);
        }),

    ),

);
