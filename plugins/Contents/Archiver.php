<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

/**
 * Processing reports for Contents
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const CONTENTS_PIECE_NAME_RECORD_NAME = 'Contents_piece_name';
    const CONTENTS_NAME_PIECE_RECORD_NAME = 'Contents_name_piece';
    const CONTENT_TARGET_NOT_SET          = 'Piwik_ContentTargetNotSet';
    const CONTENT_PIECE_NOT_SET           = 'Piwik_ContentPieceNotSet';
}
