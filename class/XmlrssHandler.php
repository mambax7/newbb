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

use XoopsModules\Newbb;

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
//load_functions('locale');

/**
 * Class XmlrssHandler
 */
class XmlrssHandler
{
    /**
     * @return Xmlrss
     */
    public function create(): Xmlrss
    {
        $xmlrss = new Xmlrss();

        return $xmlrss;
    }

    /**
     * @param \XoopsModules\Newbb\Xmlrss $rss
     *
     * @return (array|int|string)[]
     *
     * @psalm-return array{xml_version: string, xml_encoding: string, rss_version: string, channel_title: string, channel_link: string, channel_desc: string, channel_lastbuild: string, channel_webmaster: string, channel_editor: string, channel_category: string, channel_generator: string, channel_language: string, image_title: string, image_url: string, image_link: string, image_width: int, image_height: int, items: array}
     */
    public function get(Xmlrss $rss): array
    {
        $rss_array                      = [];
        $rss_array['xml_version']       = $rss->xml_version;
        $rss_array['xml_encoding']      = $rss->xml_encoding;
        $rss_array['rss_version']       = $rss->rss_version;
        $rss_array['channel_title']     = $rss->channel_title;
        $rss_array['channel_link']      = $rss->channel_link;
        $rss_array['channel_desc']      = $rss->channel_desc;
        $rss_array['channel_lastbuild'] = $rss->channel_lastbuild;
        $rss_array['channel_webmaster'] = $rss->channel_webmaster;
        $rss_array['channel_editor']    = $rss->channel_editor;
        $rss_array['channel_category']  = $rss->channel_category;
        $rss_array['channel_generator'] = $rss->channel_generator;
        $rss_array['channel_language']  = $rss->channel_language;
        $rss_array['image_title']       = $rss->channel_title;
        $rss_array['image_url']         = $rss->image_url;
        $rss_array['image_link']        = $rss->channel_link;
        $rss_array['image_width']       = $rss->image_width;
        $rss_array['image_height']      = $rss->image_height;
        $rss_array['items']             = $rss->items;

        return $rss_array;
    }
}
