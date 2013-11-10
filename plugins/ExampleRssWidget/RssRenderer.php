<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package ExampleRssWidget
 */

namespace Piwik\Plugins\ExampleRssWidget;
use Piwik\Http;

/**
 *
 * @package ExampleRssWidget
 */
class RssRenderer
{
    protected $url = null;
    protected $count = 3;
    protected $showDescription = false;
    protected $showContent = false;

    public function __construct($url)
    {
        $this->url = $url;
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
        try {
            $content = Http::fetchRemoteFile($this->url);
            $rss = simplexml_load_string($content);
        } catch (\Exception $e) {
            echo "Error while importing feed: {$e->getMessage()}\n";
            exit;
        }

        $output = '<div style="padding:10px 15px;"><ul class="rss">';
        $i = 0;

        foreach ($rss->channel->item as $post) {
            $title = $post->title;
            $date = @strftime("%B %e, %Y", strtotime($post->pubDate));
            $link = $post->link;

            $output .= '<li><a class="rss-title" title="" target="_blank" href="?module=Proxy&action=redirect&url=' . $link . '">' . $title . '</a>' .
                '<span class="rss-date">' . $date . '</span>';
            if ($this->showDescription) {
                $output .= '<div class="rss-description">' . $post->description . '</div>';
            }

            if ($this->showContent) {
                $output .= '<div class="rss-content">' . $post->content . '</div>';
            }
            $output .= '</li>';

            if (++$i == $this->count) {
                break;
            }
        }

        $output .= '</ul></div>';
        return $output;
    }
}
