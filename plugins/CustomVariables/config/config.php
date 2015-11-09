<?php

use Piwik\Plugins\CustomVariables\Model;

return array(

    'tracker.request.processors' => DI\add(array(
        DI\get('Piwik\Plugins\CustomVariables\Tracker\CustomVariablesRequestProcessor'),
    )),

    'Piwik\Plugins\CustomVariables\Model.page' => DI\object('Piwik\Plugins\CustomVariables\Model')
        ->constructor(Model::SCOPE_PAGE),
    'Piwik\Plugins\CustomVariables\Model.visit' => DI\object('Piwik\Plugins\CustomVariables\Model')
        ->constructor(Model::SCOPE_VISIT),
    'Piwik\Plugins\CustomVariables\Model.conversion' => DI\object('Piwik\Plugins\CustomVariables\Model')
        ->constructor(Model::SCOPE_CONVERSION),

    'Piwik\Plugins\CustomVariables\Model.all' => array(
        DI\get('Piwik\Plugins\CustomVariables\Model.page'),
        DI\get('Piwik\Plugins\CustomVariables\Model.visit'),
        DI\get('Piwik\Plugins\CustomVariables\Model.conversion'),
    ),

    'Piwik\Plugins\CustomVariables\CustomVariablesMetadataProvider' => DI\object()
        ->constructor(DI\get('Piwik\Plugins\CustomVariables\Model.all')),

);
