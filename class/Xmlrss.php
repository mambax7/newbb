<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
//load_functions('locale');

/**
 * Description
 *
 * @param type $var description
 * @return type description
 * @link
 */
class Xmlrss
{
    public string $xml_version;
    public string $rss_version;
    public string $xml_encoding;
    public string $channel_title;
    public string $channel_link;
    public string $channel_desc;
    public string $channel_lastbuild;
    public string $channel_webmaster;
    public string $channel_editor;
    public string $channel_category;
    public string $channel_generator;
    public string $channel_language;
    public string $image_title;
    public string $image_url;
    public string $image_link;
    public string $image_description;
    public int    $image_height;
    public int    $image_width;
    public int    $max_items;
    public int    $max_item_description;
    public array  $items = [];

    public function __construct()
    {
        $this->xml_version          = '1.0';
        $this->xml_encoding         = empty($GLOBALS['xoopsModuleConfig']['rss_utf8']) ? _CHARSET : 'UTF-8';
        $this->rss_version          = '2.0';
        $this->image_height         = 31;
        $this->image_width          = 88;
        $this->max_items            = 10;
        $this->max_item_description = 0;
        $this->items                = [];
    }

    /**
     * @param $var
     * @param $val
     */
    public function setVarRss($var, $val): void
    {
        $this->$var = $this->cleanup($val);
    }

    /**
     * @param             $title
     * @param             $link
     * @param string      $description
     * @param string      $label
     * @param int|string  $pubdate
     * @return bool
     */
    public function addItem($title, $link, string $description = '', string $label = '', $pubdate = 0): bool
    {
        if (\count($this->items) < $this->max_items) {
            if (!empty($label)) {
                $label = '[' . $this->cleanup($label) . ']';
            }
            if (!empty($description)) {
                $description = $this->cleanup($description, $this->max_item_description);
                //$description .= ' ' . $label;
            }
            //$description = $label;

            $title         = $this->cleanup($title) . ' ' . $label;
            $pubdate       = $this->cleanup($pubdate);
            $this->items[] = [
                'title'       => $title,
                'link'        => $link,
                'guid'        => $link,
                'description' => $description,
                'pubdate'     => $pubdate,
            ];
        }

        return true;
    }

    /**
     * @param               $text
     * @param int           $trim
     * @return string
     */
    public function cleanup($text, int $trim = 0): string
    {
        if ('utf-8' === \mb_strtolower($this->xml_encoding) && \strncasecmp(_CHARSET, $this->xml_encoding, 5)) {
            $text = \XoopsLocal::convert_encoding($text, 'utf-8');
        }
        if (!empty($trim)) {
            $text = \xoops_substr($text, 0, (int)$trim);
        }
        $text = \htmlspecialchars((string)$text, \ENT_QUOTES);

        return $text;
    }
}
