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

/**
 * Class Report
 */
class Report extends \XoopsObject
{
    public int    $report_id;
    public int    $post_id;
    public int    $reporter_uid;
    public string $reporter_ip;
    public int    $report_time;
    public string $report_text;
    public int    $report_result;
    public string $report_memo;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('report_id', \XOBJ_DTYPE_INT);
        $this->initVar('post_id', \XOBJ_DTYPE_INT);
        $this->initVar('reporter_uid', \XOBJ_DTYPE_INT);
        $this->initVar('reporter_ip', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('report_time', \XOBJ_DTYPE_INT);
        $this->initVar('report_text', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('report_result', \XOBJ_DTYPE_INT);
        $this->initVar('report_memo', \XOBJ_DTYPE_TXTBOX);
    }
}
