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
class Category extends \XoopsObject
{
    public int    $cat_id;
    public string $cat_title;
    public string $cat_image;
    public string $cat_description;
    public int    $cat_order;
    public string $cat_url;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('cat_id', \XOBJ_DTYPE_INT);
        $this->initVar('cat_title', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('cat_image', \XOBJ_DTYPE_SOURCE, 'blank.gif');
        $this->initVar('cat_description', \XOBJ_DTYPE_TXTAREA);
        $this->initVar('cat_order', \XOBJ_DTYPE_INT, 99);
        $this->initVar('cat_url', \XOBJ_DTYPE_URL);
    }
}
