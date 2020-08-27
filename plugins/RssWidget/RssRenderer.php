<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\RssWidget;

use Piwik\Cache;
use Matomo\Cache\Lazy;
use Piwik\Http;

/**
 *
 */
class RssRenderer
{
    protected $url = null;
    protected $count = 3;
    protected $showDescription = false;
    protected $showContent = false;
    /**
     * @var Lazy
     */
    private $cache;


    public function __construct($url)
    {
        $this->url = $url;
        $this->cache = Cache::getLazyCache();
    }

    public function showDescription($bool)
    {
        $this->showDescription = $bool;
    }

    public function showContent($bool)
    {
        $this->showContent = $bool;
    }

    public function setCountPosts($count)
    {
        $this->count = $count;
    }

    public function get()
    {
        $cacheId = 'RSS_' . md5($this->url);

        $output = $this->cache->fetch($cacheId);

        if (!$output) {
            try {
                $content = Http::fetchRemoteFile($this->url, null, 0, 15);

                $rss = @simplexml_load_string($content);
                if ($rss === false) {
                    throw new \Exception("Failed to parse XML.");
                }
            } catch (\Exception $e) {
                throw new \Exception("Error while importing feed: {$e->getMessage()}\n");
            }

            $output = '<div style="padding:10px 15px;"><ul class="rss">';
            $i = 0;

            $items = array();
            if (!empty($rss->channel->item)) {
                $items = $rss->channel->item;
            }
            foreach ($items as $post) {
                $title = $post->title;
                $date = @strftime("%B %e, %Y", strtotime($post->pubDate));
                $link = $post->link;

                $output .= '<li><a class="rss-title" title="" target="_blank" rel="noreferrer noopener" href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '">' . $title . '</a>' .
                    '<span class="rss-date">' . $date . '</span>';
                if ($this->showDescription) {
                    $output .= '<div class="rss-description">' . $this->addTargetBlankAndNoReferrerToLinks($post->description) . '</div>';
                }

                if ($this->showContent) {
                    $output .= '<div class="rss-content">' . $this->addTargetBlankAndNoReferrerToLinks($post->content) . '</div>';
                }
                $output .= '</li>';

                if (++$i == $this->count) {
                    break;
                }
            }

            $output .= '</ul></div>';
            $this->cache->save($cacheId, $output, 60 * 60 * 24);
        }
        return $output;
    }

    private function addTargetBlankAndNoReferrerToLinks($content)
    {
        return str_replace('<a ', '<a target="_blank" rel="noreferrer noopener"', $content);
    }
}
