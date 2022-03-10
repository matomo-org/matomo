<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dao;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

class Configuration
{
    private $tableName = 'custom_dimensions';
    private $tableNamePrefixed;

    public function __construct()
    {
        $this->tableNamePrefixed = Common::prefixTable($this->tableName);
    }

    private function getDb()
    {
        return Db::get();
    }

    public function configureNewDimension($idSite, $name, $scope, $index, $active, $extractions, $caseSensitive)
    {
        $extractions = $this->encodeExtractions($extractions);
        $active = $active ? '1' : '0';
        $caseSensitive = $caseSensitive ? '1' : '0';
        $id = $this->getNextCustomDimensionIdForSite($idSite);

        $config = array(
            'idcustomdimension' => $id,
            'idsite'      => $idSite,
            'index'       => $index,
            'scope'       => $scope,
            'name'        => $name,
            'active'      => $active,
            'extractions' => $extractions,
            'case_sensitive' => $caseSensitive,
        );

        $this->getDb()->insert($this->tableNamePrefixed, $config);

        return $id;
    }

    public function configureExistingDimension($idCustomDimension, $idSite, $name, $active, $extractions, $caseSensitive)
    {
        $extractions = $this->encodeExtractions($extractions);
        $active = $active ? '1' : '0';
        $caseSensitive = $caseSensitive ? '1' : '0';

        $this->getDb()->update($this->tableNamePrefixed,
            array(
                'name'        => $name,
                'active'      => $active,
                'extractions' => $extractions,
                'case_sensitive' => $caseSensitive
            ),
            "idcustomdimension = " . (int) $idCustomDimension . " and idsite = " . (int) $idSite
        );
    }

    public function getCustomDimensionsForSite($idSite)
    {
        $query = "SELECT * FROM " . $this->tableNamePrefixed . " WHERE idsite = ?";
        return $this->fetchAllDimensionsEnriched($query, array($idSite));
    }

    public function getCustomDimension($idDimension, $idSite)
    {
        $query = "SELECT * FROM " . $this->tableNamePrefixed . " WHERE idcustomdimension = ? and idsite = ?";
        $dimension = $this->getDb()->fetchRow($query, array($idDimension, $idSite));
        $dimension = $this->enrichDimension($dimension);

        return $dimension;
    }

    public function getCustomDimensionsHavingIndex($scope, $index)
    {
        $query= "SELECT * FROM " . $this->tableNamePrefixed . " WHERE `index` = ? and scope = ?";
        return $this->fetchAllDimensionsEnriched($query, array($index, $scope));
    }

    public function deleteConfigurationsForSite($idSite)
    {
        $this->getDb()->query("DELETE FROM " . $this->tableNamePrefixed . " WHERE idsite = ?", $idSite);
    }

    public function deleteConfigurationsForIndex($index, $scope)
    {
        $this->getDb()->query("DELETE FROM " . $this->tableNamePrefixed . " WHERE `index` = ? and `scope` = ?", array($index, $scope));
    }

    private function fetchAllDimensionsEnriched($sql, $bind)
    {
        $dimensions = $this->getDb()->fetchAll($sql, $bind);
        $dimensions = $this->enrichDimensions($dimensions);

        return $dimensions;
    }

    private function enrichDimensions($dimensions)
    {
        if (empty($dimensions)) {
            return array();
        }

        foreach ($dimensions as $index => $dimension) {
            $dimensions[$index] = $this->enrichDimension($dimension);
        }

        return $dimensions;
    }

    private function enrichDimension($dimension)
    {
        if (empty($dimension)) {
            return $dimension;
        }

        // cast to string done
        $dimension['idcustomdimension'] = (string) $dimension['idcustomdimension'];
        $dimension['idsite'] = (string) $dimension['idsite'];
        $dimension['index'] = (string) $dimension['index'];

        $dimension['extractions'] = $this->decodeExtractions($dimension['extractions']);
        $dimension['active'] = (bool) $dimension['active'];
        $dimension['case_sensitive'] = (bool) $dimension['case_sensitive'];

        return $dimension;
    }

    private function getNextCustomDimensionIdForSite($idSite)
    {
        $nextId = $this->getDb()->fetchOne("SELECT max(idcustomdimension) FROM " . $this->tableNamePrefixed . " WHERE idsite = ?", $idSite);

        if (empty($nextId)) {
            $nextId = 1;
        } else {
            $nextId = (int) $nextId + 1;
        }

        return $nextId;
    }

    public function install()
    {
        $table = "`idcustomdimension` BIGINT UNSIGNED NOT NULL,
                  `idsite` BIGINT UNSIGNED NOT NULL ,
                  `name` VARCHAR(100) NOT NULL ,
                  `index` SMALLINT UNSIGNED NOT NULL ,
                  `scope` VARCHAR(10) NOT NULL ,
                  `active` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                  `extractions` TEXT NOT NULL DEFAULT '',
                  `case_sensitive` TINYINT UNSIGNED NOT NULL DEFAULT 1,
                  PRIMARY KEY (`idcustomdimension`, `idsite`),
                  UNIQUE KEY uniq_hash(idsite, `scope`, `index`)";

        DbHelper::createTable($this->tableName, $table);
    }

    public function uninstall()
    {
        Db::dropTables(array($this->tableNamePrefixed));
    }

    private function encodeExtractions($extractions)
    {
        if (empty($extractions) || !is_array($extractions)) {
            $extractions = array();
        }

        return json_encode($extractions);
    }

    private function decodeExtractions($extractions)
    {
        if (!empty($extractions)) {
            $extractions = json_decode($extractions, true);
        }

        if (empty($extractions) || !is_array($extractions)) {
            $extractions = array();
        }

        return $extractions;
    }

}
