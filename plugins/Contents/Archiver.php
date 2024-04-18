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
    public const CONTENTS_PIECE_NAME_RECORD_NAME = 'Contents_piece_name';
    public const CONTENTS_NAME_PIECE_RECORD_NAME = 'Contents_name_piece';
    public const CONTENT_TARGET_NOT_SET          = 'Piwik_ContentTargetNotSet';
    public const CONTENT_PIECE_NOT_SET           = 'Piwik_ContentPieceNotSet';
}
