<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\DataTable;

/**
 * Triggers when visitors are regularly leaving the website for its videos.
 *
 * An outlink to YouTube says the video exists and is being clicked, but nothing about
 * whether it is then watched, which is the gap the promotion speaks to.
 */
class MediaOutlinksTrigger extends ReportBackedTrigger
{
    public const NAME = 'media_outlinks';

    public const MINIMUM_CLICKS = 500;

    /**
     * Both hosts YouTube links use, so a share link counts alongside a watch page.
     */
    public const MEDIA_HOSTS = ['youtube.com', 'youtu.be'];

    private const ROWS_TO_INSPECT = 200;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRequiredArchives(): array
    {
        return ['Actions'];
    }

    protected function getApiMethod(): string
    {
        return 'Actions.getOutlinks';
    }

    protected function getApiParameters(): array
    {
        return [
            'flat' => 1,
            'filter_sort_column' => 'nb_hits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        $clicks = $this->countMediaClicks($report);

        return $clicks < self::MINIMUM_CLICKS ? null : ['count' => $clicks];
    }

    /**
     * Adds up the clicks on every outlink pointing at one of the media hosts.
     *
     * Flattened, an outlink row is a full URL, so one video per row: they are summed
     * rather than ranked, because the copy speaks about the service and not the video.
     */
    public function countMediaClicks(DataTable $outlinks): int
    {
        $clicks = 0;

        foreach ($outlinks->getRows() as $row) {
            $label = (string) $row->getColumn('label');
            $url = (string) $row->getMetadata('url');

            foreach (self::MEDIA_HOSTS as $host) {
                if (false !== stripos($label, $host) || false !== stripos($url, $host)) {
                    $clicks += (int) $row->getColumn('nb_hits');
                    break;
                }
            }
        }

        return $clicks;
    }
}
