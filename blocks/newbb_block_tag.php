<?php declare(strict_types=1);

/**
 * Tag blocks for NewBB 4.0+
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since          4.00
 */

use XoopsModules\Newbb;

/**#@+
 * Function to display tag cloud
 * @param array $options
 * @return array|null|bool
 */
function newbb_tag_block_cloud_show(array $options)
{
    if ((!class_exists('TagFormTag')) || (class_exists('TagFormTag') && !@require $GLOBALS['xoops']->path('modules/tag/blocks/block.php'))) {
        return null;
    }
    require_once XOOPS_ROOT_PATH . '/modules/tag/blocks/block.php';
    $block_content = tag_block_cloud_show($options, 'newbb');

    return $block_content;
}

/**
 * @param array $options
 * @return null|string
 */
function newbb_tag_block_cloud_edit(array $options): ?string
{
    if ((!class_exists('TagFormTag')) || (class_exists('TagFormTag') && !@require $GLOBALS['xoops']->path('modules/tag/blocks/block.php'))) {
        return null;
    }
    require_once XOOPS_ROOT_PATH . '/modules/tag/blocks/block.php';
    $form = tag_block_cloud_edit($options);

    return $form;
}

/**#@+
 * Function to display top tag list
 * @param array $options
 * @return array|null
 */
function newbb_tag_block_top_show(array $options): ?array
{
    if ((!class_exists('TagFormTag')) || (class_exists('TagFormTag') && !@require $GLOBALS['xoops']->path('modules/tag/blocks/block.php'))) {
        return null;
    }
    require_once XOOPS_ROOT_PATH . '/modules/tag/blocks/block.php';
    $block_content = tag_block_top_show($options, 'newbb');

    return $block_content;
}

/**
 * @param array $options
 * @return string|null
 */
function newbb_tag_block_top_edit(array $options): ?string
{
    if (!@require $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    require_once XOOPS_ROOT_PATH . '/modules/tag/blocks/block.php';
    $form = tag_block_top_edit($options);

    return $form;
}
