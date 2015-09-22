<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;

/**
 * A controller let's you for example create a page that can be added to a menu. For more information read our guide
 * http://developer.piwik.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Controller and
 * http://developer.piwik.org/api-reference/Piwik/View
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function getDefaultAction()
    {
        return 'status';
    }

    public function status()
    {
        Piwik::checkUserHasSuperUserAccess();

        $limit = Common::getRequestVar('limit', 200, 'int');

        $importingFiles = Request::processRequest('AutoLogImporter.getAllImportingFiles');
        $importingSoon  = Request::processRequest('AutoLogImporter.getFilesThatCanBeImported');
        $filesWithInvalidHashes = Request::processRequest('AutoLogImporter.getFilesHavingInvalidHash');
        $importedFiles = Request::processRequest('AutoLogImporter.getAllImportedFiles', array(
            'filter_limit' => $limit
        ));

        if (count($importedFiles) < $limit) {
            $limit = count($importedFiles);
        }

        $formatter = new Formatter();

        return $this->renderTemplate('status', array(
            'importingFiles' => $formatter->addTimeAgoAsSentence($formatter->formatImportedFiles($importingFiles)),
            'importedFiles' => $formatter->formatImportedFiles($importedFiles),
            'scheduledFiles' => $importingSoon,
            'filesWithInvalidHashes' => $filesWithInvalidHashes,
            'limit' => $limit
        ));
    }
}
