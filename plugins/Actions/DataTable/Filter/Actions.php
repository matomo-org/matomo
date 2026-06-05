<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\DataTable\Filter;

use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Plugins\Actions\ArchivingHelper;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Tracker\Action;
use Piwik\Tracker\PageUrl;

class Actions extends BaseFilter
{
    private $actionType;

    /**
     * Per-instance cache of the host list resolved for an idSite. `null` marks a site
     * we already tried to resolve and could not (so we don't retry on every row).
     *
     * @var array<int, array<int, array{scheme: string, host: string}>|null>
     */
    private $siteHostsCache = [];

    /**
     * @param DataTable $table The table to eventually filter.
     * @param int $actionType The action type being processed.
     */
    public function __construct($table, $actionType)
    {
        parent::__construct($table);
        $this->actionType = $actionType;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $isFlattening = Common::getRequestVar('flat', 0);
        $table->filter(function (DataTable $dataTable) use ($isFlattening) {
            $site = $dataTable->getMetadata('site');
            $urlPrefix = $site ? $site->getMainUrl() : null;

            $defaultActionName = GeneralConfig::getConfigValue('action_default_name');

            $isPageTitleType = $this->actionType == Action::TYPE_PAGE_TITLE;

            // for BC, we read the old style delimiter first (see #1067)
            $actionDelimiter = GeneralConfig::getConfigValue('action_category_delimiter');
            if (empty($actionDelimiter)) {
                if ($isPageTitleType) {
                    $actionDelimiter = GeneralConfig::getConfigValue('action_title_category_delimiter');
                } else {
                    $actionDelimiter = GeneralConfig::getConfigValue('action_url_category_delimiter');
                }
            }

            $notDefinedUrl = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_URL);
            $notDefinedTitle = ArchivingHelper::getUnknownActionName(Action::TYPE_PAGE_TITLE);

            foreach ($dataTable->getRows() as $row) {
                if (!$row->isSummaryRow()) {
                    $url = $row->getMetadata('url');
                    $pageTitlePath = $row->getMetadata('page_title_path');
                    $folderUrlStart = $row->getMetadata('folder_url_start');
                    $label = $row->getColumn('label');
                    if ($url) {
                        $row->setMetadata('segmentValue', urlencode($url));

                        if ($site && strpos($url, 'http://') === 0) {
                            $host = parse_url($url, PHP_URL_HOST);

                            if ($host && PageUrl::shouldUseHttpsHost($site->getId(), $host)) {
                                $row->setMetadata('url', 'https://' . mb_substr($url, 7 /* = strlen('http://') */));
                            }
                        }
                    } elseif ($folderUrlStart) {
                        $row->setMetadata('segment', $this->buildFolderUrlSegment($folderUrlStart, $site));
                    } elseif ($pageTitlePath) {
                        if ($row->getIdSubDataTable()) {
                            $row->setMetadata('segment', 'pageTitle=^' . urlencode(urlencode(trim($pageTitlePath))));
                        } else {
                            $row->setMetadata('segmentValue', urlencode(trim($pageTitlePath)));
                        }
                    } elseif ($isPageTitleType && !in_array($label, [DataTable::LABEL_SUMMARY_ROW])) {
                        // for older data w/o page_title_path metadata
                        if ($row->getIdSubDataTable()) {
                            $row->setMetadata('segment', 'pageTitle=^' . urlencode(urlencode(trim($label))));
                        } else {
                            if (trim($label) == $notDefinedTitle) {
                                // segmenting by an "empty" value is currently broken for actions, so we do not set a segment value to hide row actions like segmented visit log
                                $row->setMetadata('segment', null);
                            } else {
                                $row->setMetadata('segmentValue', urlencode(trim($label)));
                            }
                        }
                    } elseif ($this->actionType == Action::TYPE_PAGE_URL) { // folder for older data w/ no folder URL metadata
                        if ($label === $notDefinedUrl) {
                            // segmenting by an "empty" value is currently broken for actions, so we do not set a segment value to hide row actions like segmented visit log
                            $row->setMetadata('segment', null);
                        } elseif ($urlPrefix) {
                            $row->setMetadata(
                                'segment',
                                $this->buildFolderUrlSegment($urlPrefix . '/' . $label, $site)
                            );
                        }
                    }
                }

                // remove the default action name 'index' in the end of flattened urls and prepend $actionDelimiter
                if ($isFlattening) {
                    $label = $row->getColumn('label');
                    $stringToSearch = $actionDelimiter . $defaultActionName;
                    if (substr($label, -strlen($stringToSearch)) == $stringToSearch) {
                        $label = substr($label, 0, -strlen($defaultActionName));
                        $label = rtrim($label, $actionDelimiter) . $actionDelimiter;
                        $row->setColumn('label', $label);
                    }
                    $dataTable->setLabelsHaveChanged();
                }

                $row->deleteMetadata('folder_url_start');
                $row->deleteMetadata('page_title_path');
            }
        });

        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->filter($subtable);
            }
        }
    }

    /**
     * Builds the `pageUrl=^` segment for a folder row. If the site has additional URL
     * aliases registered, the segment is an OR-joined list with one `pageUrl=^` clause
     * per host so the visitor log query matches rows tracked under any of the site's
     * known hosts, not only `main_url`.
     */
    private function buildFolderUrlSegment(string $folderUrlStart, $site): string
    {
        $original = 'pageUrl=^' . urlencode(urlencode($folderUrlStart));

        if (!$site) {
            return $original;
        }

        $mainUrl = $site->getMainUrl();
        if (empty($mainUrl)) {
            return $original;
        }

        // `folder_url_start` (and the legacy fallback) is always built as
        // `<main_url>/<path-to-folder>`. Strip the main URL to get the path-only portion
        // that we can re-attach to each known host.
        $mainUrlNormalized = rtrim($mainUrl, '/') . '/';
        if (strpos($folderUrlStart, $mainUrlNormalized) !== 0) {
            return $original;
        }
        $folderPath = substr($folderUrlStart, strlen($mainUrlNormalized));

        $hosts = $this->getResolvedHosts((int) $site->getId());
        if ($hosts === null) {
            return $original;
        }

        $clauses = [];
        foreach ($hosts as $entry) {
            $clauses[] = 'pageUrl=^'
                . urlencode(urlencode($entry['scheme'] . '://' . $entry['host'] . '/' . $folderPath));
        }

        if (empty($clauses)) {
            return $original;
        }

        return implode(',', $clauses);
    }

    /**
     * @return array<int, array{scheme: string, host: string}>|null Null on API failure.
     */
    private function getResolvedHosts(int $idSite): ?array
    {
        if (array_key_exists($idSite, $this->siteHostsCache)) {
            return $this->siteHostsCache[$idSite];
        }

        try {
            $allUrls = SitesManagerAPI::getInstance()->getSiteUrlsFromId($idSite);
        } catch (\Exception $e) {
            return $this->siteHostsCache[$idSite] = null;
        }

        $seenHosts = [];
        $hosts = [];
        foreach ($allUrls as $url) {
            $host = parse_url($url, PHP_URL_HOST);
            if (!$host || isset($seenHosts[$host])) {
                continue;
            }
            $seenHosts[$host] = true;
            $hosts[] = [
                'scheme' => parse_url($url, PHP_URL_SCHEME) ?: 'https',
                'host' => $host,
            ];
        }

        return $this->siteHostsCache[$idSite] = $hosts;
    }
}
