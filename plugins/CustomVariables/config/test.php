<?php

return array(

    'Piwik\Plugins\CustomVariables\tests\Mock\CustomVariablesMetadataProvider' => DI\object()
        ->constructor(DI\get('Piwik\Plugins\CustomVariables\Model.all')),

);
