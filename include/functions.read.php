<?php declare(strict_types=1);

/**
 * NewBB,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use XoopsModules\Newbb\{
    Helper,
    ReadHandler,
    ReadtopicHandler
};

/** @var Helper $helper */
/** @var ReadHandler $readHandler */
defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_READ_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_READ')) {
    define('NEWBB_FUNCTIONS_READ', 1);

    /**
     * @param string $type
     * @param int    $item_id
     * @param int    $post_id
     * @param int|null   $uid
     * @return mixed
     */
    function newbbSetRead(string $type, int $item_id, int $post_id, ?int $uid = null)
    {
        $readHandler = Helper::getInstance()->getHandler('Read' . $type);

        return $readHandler->setRead($item_id, $post_id, $uid);
    }

    /**
     * @param string $type
     * @param int $item_id
     * @param int|null   $uid
     * @return bool|int
     */
    function newbbGetRead(string $type, int $item_id, ?int $uid = null)
    {
        /** @var ReadHandler $readHandler */
        $readHandler = Helper::getInstance()->getHandler('Read' . $type);

        return $readHandler->getRead($item_id, $uid);
    }

    /**
     * @param int  $status
     * @param null $uid
     * @return mixed
     */
    function newbbSetReadforum(int $status = 0, $uid = null)
    {
        /** @var ReadHandler $readforumHandler */
        $readforumHandler = Helper::getInstance()->getHandler('Readforum');

        return $readforumHandler->setReadItems($status, $uid);
    }

    /**
     * @param int  $status
     * @param int  $forum_id
     * @param null $uid
     * @return mixed
     */
    function newbbSetReadTopic(int $status = 0, int $forum_id = 0, $uid = null)
    {
        /** @var ReadHandler $readTopicHandler */
        $readTopicHandler = Helper::getInstance()->getHandler('Readtopic');

        return $readTopicHandler->setReadItems($status, $forum_id, $uid);
    }

    /**
     * @param string $type
     * @param array  $items
     * @param int|null   $uid
     * @return array|null
     */
    function newbbIsRead(string $type, array $items, ?int $uid = null): ?array
    {
        /** @var ReadHandler $readHandler */
        $readHandler = Helper::getInstance()->getHandler('Read' . $type);

        return $readHandler->isReadItems($items, $uid);
    }
}
