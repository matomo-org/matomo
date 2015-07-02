<?php

return array(

    'RequestSanitizer.requestParameterSanitizeBlacklist' => DI\add(array(
        array('ScheduledReports.addReport', array('description')),
        array('ScheduledReports.updateReport', array('description'))
    )),

);