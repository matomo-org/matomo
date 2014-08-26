<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

class Dimensions
{

    private static $defaultMappingApiToSecondaryDimension = array(
        'getContentNames'  => 'getContentPieces',
        'getContentPieces' => 'getContentNames'
    );

    private static $mappingApiToRecord = array(
        'getContentNames' => array(
            'getContentPieces' => Archiver::CONTENTS_NAME_PIECE_RECORD_NAME
        ),
        'getContentPieces' => array(
            'getContentNames' => Archiver::CONTENTS_PIECE_NAME_RECORD_NAME,
        )
    );
    
    public static function getDefaultSecondaryDimension($apiMethod)
    {
        if (isset(self::$defaultMappingApiToSecondaryDimension[$apiMethod])) {
            return self::$defaultMappingApiToSecondaryDimension[$apiMethod];
        }

        return false;
    }

    public static function getRecordNameForAction($apiMethod, $secondaryDimension = false)
    {
        if (empty($secondaryDimension)) {
            $secondaryDimension = self::getDefaultSecondaryDimension($apiMethod);
        }

        $record = self::$mappingApiToRecord[$apiMethod];
        if (!is_array($record)) {
            return $record;
        }

        // when secondaryDimension is incorrectly set
        if (empty($record[$secondaryDimension])) {
            return key($record);
        }

        return $record[$secondaryDimension];
    }

    /**
     * @ignore
     * @param $apiMethod
     * @return array
     */
    public static function getSecondaryDimensions($apiMethod)
    {
        $records = self::$mappingApiToRecord[$apiMethod];

        if(!is_array($records)) {
            return false;
        }

        return array_keys($records);
    }

    public static function checkSecondaryDimension($apiMethod, $secondaryDimension)
    {
        if (empty($secondaryDimension)) {
            return;
        }

        $isSecondaryDimensionValid =
            isset(self::$mappingApiToRecord[$apiMethod])
            && isset(self::$mappingApiToRecord[$apiMethod][$secondaryDimension]);

        if (!$isSecondaryDimensionValid) {
            throw new \Exception(
                "Secondary dimension '$secondaryDimension' is not valid for the API $apiMethod. ".
                "Use one of: " . implode(", ", self::getSecondaryDimensions($apiMethod))
            );
        }
    }

}
