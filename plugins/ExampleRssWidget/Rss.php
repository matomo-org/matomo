<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_ExampleRssWidget
 */

/**
 *
 * @package Piwik_ExampleRssWidget
 */
class Piwik_ExampleRssWidget_Rss
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
            $rss = Zend_Feed::import($this->url);
        } catch (Zend_Feed_Exception $e) {
            echo "Error while importing feed: {$e->getMessage()}\n";
            exit;
        }

        $output = '<div style="padding:10px 15px;"><ul class="rss">';
        $i = 0;

        foreach ($rss as $post) {
            $title = $post->title();
            $date = @strftime("%B %e, %Y", strtotime($post->pubDate()));
            $link = $post->link();

            $output .= '<li><a class="rss-title" title="" target="_blank" href="?module=Proxy&action=redirect&url=' . $link . '">' . $title . '</a>' .
                '<span class="rss-date">' . $date . '</span>';
            if ($this->showDescription) {
                $output .= '<div class="rss-description">' . $post->description() . '</div>';
            }

            if ($this->showContent) {
                $output .= '<div class="rss-content">' . $post->content() . '</div>';
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
