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

use Xmf\Module\Helper\Cache;

/** @var Cache $cacheHelper */

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
\define('NEWBB_HANDLER_PERMISSION', 1);

// Initializing XoopsGroupPermHandler if not loaded yet
if (!\class_exists('XoopsGroupPermHandler')) {
    require_once $GLOBALS['xoops']->path('kernel/groupperm.php');
}

/**
 * Class PermissionHandler
 */
class PermissionHandler extends \XoopsGroupPermHandler
{
    protected Cache $cacheHelper;
    /** @var array|null */
    private array $_handler;
    
    /** @var Helper|null $helper
     * @readonly */
    private ?Helper $helper;

    /**
     * @param \XoopsDatabase|null $db
     * @param Helper|null         $helper
     */
    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        $this->cacheHelper = new Cache('newbb');
        if (null === $helper) {
            $helper = Helper::getInstance();
        }
        $this->helper = $helper;

        $this->db = $db;
        parent::__construct($db);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function loadHandler(string $name)
    {
        if (!isset($this->_handler[$name])) {
            //            $className             = '\\XoopsModules\\Newbb\\Permission' . \ucfirst($name) . 'Handler';
            //            $this->_handler[$name] = new $className($this->db);
            $this->_handler[$name] = $this->helper->getHandler('Permission' . \ucfirst($name));
        }

        return $this->_handler[$name];
    }

    /**
     * @param bool $fullname
     * @return array
     */
    public function getValidForumPerms(bool $fullname = false): array
    {
        /** @var PermissionForumHandler $handler */
        $handler = $this->loadHandler('Forum');

        return $handler->getValidPerms($fullname);
    }

    /**
     * @param int|Forum  $forum
     * @param bool $topic_locked
     * @param bool $isAdmin
     * @return array
     */
    public function getPermissionTable($forum = 0, bool $topic_locked = false, bool $isAdmin = false): array
    {
        /** @var PermissionForumHandler $handler */
        $handler = $this->loadHandler('Forum');
        $perm    = $handler->getPermissionTable($forum, $topic_locked, $isAdmin);

        return $perm;
    }

    /**
     * @param int $forum_id
     * @return bool
     */
    public function deleteByForum(int $forum_id): bool
    {
        $this->cacheHelper->delete('permission_forum');
        /** @var PermissionForumHandler $handler */
        $handler = $this->loadHandler('Forum');

        return $handler->deleteByForum($forum_id);
    }

    /**
     * @param int $cat_id
     * @return bool
     */
    public function deleteByCategory(int $cat_id):bool
    {
        $this->cacheHelper->delete('permission_category');
        /** @var PermissionCategoryHandler $handler */
        $handler = $this->loadHandler('Category');

        return $handler->deleteByCategory($cat_id);
    }

    /**
     * @param int $category
     * @param array  $groups
     * @return bool
     */
    public function setCategoryPermission(int $category, array $groups = []): bool
    {
        $this->cacheHelper->delete('permission_category');
        /** @var PermissionCategoryHandler $handler */
        $handler = $this->loadHandler('Category');

        return $handler->setCategoryPermission($category, $groups);
    }

    /**
     * @param string $type
     * @param string $gperm_name
     * @param int    $id
     * @return bool
     */
    public function getPermission(string $type, string $gperm_name = 'access', int $id = 0): bool
    {
        global $xoopsModule;
        $ret = false;
        if ($GLOBALS['xoopsUserIsAdmin'] && 'newbb' === $xoopsModule->getVar('dirname')) {
            $ret = true;
        }

        $groups = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : [XOOPS_GROUP_ANONYMOUS];
        if (!$groups) {
            $ret = false;
        }
        if (!$allowed_groups = $this->getGroups("{$type}_{$gperm_name}", $id)) {
            $ret = false;
        }

        if (\count(\array_intersect($allowed_groups, $groups)) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @param string $permName
     * @return array
     */
    public function &getCategories(string $permName = 'access'): array
    {
        $ret = $this->getAllowedItems('category', "category_{$permName}");

        return $ret;
    }

    /**
     * @param string $permName
     * @return array
     */
    public function getForums(string $permName = 'access'): array
    {
        $ret = $this->getAllowedItems('forum', "forum_{$permName}");

        return $ret;
    }

    /**
     * @param string $type
     * @param string $permName
     * @return array
     */
    public function getAllowedItems(string $type, string $permName): array
    {
        $ret = [];

        $groups = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : [XOOPS_GROUP_ANONYMOUS];
        if ((is_countable($groups) ? \count($groups) : 0) < 1) {
            return $ret;
        }

        if (!$_cachedPerms = $this->loadPermData($permName)) {
            return $ret;
        }

        $allowed_items = [];
        foreach ($_cachedPerms as $id => $allowed_groups) {
            if (0 == $id || empty($allowed_groups)) {
                continue;
            }

            if (\array_intersect($groups, $allowed_groups)) {
                $allowed_items[$id] = 1;
            }
        }
        unset($_cachedPerms);
        $ret = \array_keys($allowed_items);

        return $ret;
    }

    /**
     * @param string $gperm_name
     * @param int    $id
     * @return array
     */
    public function getGroups(string $gperm_name, int $id = 0): array
    {
        $_cachedPerms = $this->loadPermData($gperm_name);
        $groups       = empty($_cachedPerms[$id]) ? [] : \array_unique($_cachedPerms[$id]);
        unset($_cachedPerms);

        return $groups;
    }

    /**
     * @param string $permName
     * @return array
     */
    public function createPermData(string $permName = 'forum_all'): array
    {
        global $xoopsModule;
        /** @var \XoopsModuleHandler $moduleHandler */
        $perms = [];

        if (\is_object($xoopsModule) && 'newbb' === $xoopsModule->getVar('dirname')) {
            $modid = $xoopsModule->getVar('mid');
        } else {
            /** @var \XoopsModuleHandler $moduleHandler */
            $moduleHandler = \xoops_getHandler('module');
            $module        = $moduleHandler->getByDirname('newbb');
            $modid         = $module->getVar('mid');
            unset($module);
        }

        if (\in_array($permName, ['forum_all', 'category_all'], true)) {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = \xoops_getHandler('member');
            $groups        = \array_keys($memberHandler->getGroupList());

            $type = ('category_all' === $permName) ? 'Category' : 'Forum';
            /** @var \XoopsPersistableObjectHandler $objectHandler */
            $objectHandler = Helper::getInstance()->getHandler($type);
            $object_ids    = $objectHandler->getIds();
            foreach ($object_ids as $item_id) {
                $perms[$permName][$item_id] = $groups;
            }
        } else {
            $grouppermHandler = \xoops_getHandler('groupperm');
            $criteria         = new \CriteriaCompo(new \Criteria('gperm_modid', $modid));
            if (!empty($permName) && 'forum_all' !== $permName && 'category_all' !== $permName) {
                $criteria->add(new \Criteria('gperm_name', $permName));
            }
            $permissions = $this->getObjects($criteria);

            foreach ($permissions as $gperm) {
                $item_id                                         = $gperm->getVar('gperm_itemid');
                $group_id                                        = (int)$gperm->getVar('gperm_groupid');
                $perms[$gperm->getVar('gperm_name')][$item_id][] = $group_id;
            }
        }
        if (\count($perms) > 0) {
            foreach (\array_keys($perms) as $perm) {
                $this->cacheHelper->write("permission_{$perm}", $perms[$perm]);
            }
        }
        $ret = (!empty($permName) && !empty($perms)) ? @$perms[$permName] : $perms;

        return $ret;
    }

    /**
     * @param string $permName
     * @return array
     */
    public function &loadPermData(string $permName = 'forum_access'): array
    {
        if (!$perms = $this->cacheHelper->read("permission_{$permName}")) {
            $perms = $this->createPermData($permName);
        }

        return $perms;
    }

    /**
     * @param string   $perm
     * @param int      $itemid
     * @param int      $groupid
     * @param int|null $mid
     * @return bool
     */
    public function validateRight(string $perm, int $itemid, int $groupid, ?int $mid = null): bool
    {
        if (empty($mid)) {
            if (\is_object($GLOBALS['xoopsModule']) && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname')) {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                /** @var \XoopsModuleHandler $moduleHandler */
                $moduleHandler = \xoops_getHandler('module');
                $mod           = $moduleHandler->getByDirname('newbb');
                $mid           = $mod->getVar('mid');
                unset($mod);
            }
        }
        if ($this->myCheckRight($perm, $itemid, $groupid, $mid)) {
            return true;
        }
        $this->cacheHelper->delete('permission');
        $this->addRight($perm, $itemid, $groupid, $mid);

        return true;
    }

    /**
     * Check permission (directly)
     *
     * @param string    $gperm_name    Name of permission
     * @param int       $gperm_itemid  ID of an item
     * @param int|array $gperm_groupid A group ID or an array of group IDs
     * @param int       $gperm_modid   ID of a module
     *
     * @return bool TRUE if permission is enabled
     */
    public function myCheckRight(string $gperm_name, int $gperm_itemid, $gperm_groupid, int $gperm_modid = 1): bool
    {
        $ret      = false;
        $criteria = new \CriteriaCompo(new \Criteria('gperm_modid', $gperm_modid));
        $criteria->add(new \Criteria('gperm_name', $gperm_name));
        $gperm_itemid = (int)$gperm_itemid;
        if ($gperm_itemid > 0) {
            $criteria->add(new \Criteria('gperm_itemid', $gperm_itemid));
        }
        if (\is_array($gperm_groupid)) {
            $criteria2 = new \CriteriaCompo();
            foreach ($gperm_groupid as $gid) {
                $criteria2->add(new \Criteria('gperm_groupid', $gid), 'OR');
            }
            $criteria->add($criteria2);
        } else {
            $criteria->add(new \Criteria('gperm_groupid', $gperm_groupid));
        }
        if ($this->getCount($criteria) > 0) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * @param string   $perm
     * @param int      $itemid
     * @param int      $groupid
     * @param int|null $mid
     * @return bool
     */
    public function deleteRight(string $perm, int $itemid, int $groupid, ?int $mid = null): bool
    {
        $this->cacheHelper->delete('permission');
        if (null === $mid) {
            if (\is_object($GLOBALS['xoopsModule']) && 'newbb' === $GLOBALS['xoopsModule']->getVar('dirname')) {
                $mid = $GLOBALS['xoopsModule']->getVar('mid');
            } else {
                /** @var \XoopsModuleHandler $moduleHandler */
                $moduleHandler = \xoops_getHandler('module');
                $mod           = $moduleHandler->getByDirname('newbb');
                $mid           = $mod->getVar('mid');
                unset($mod);
            }
        }
        if (\is_callable('parent::deleteRight')) {
            return self::deleteRight($perm, $itemid, $groupid, $mid);
        }
        $criteria = new \CriteriaCompo(new \Criteria('gperm_name', $perm));
        $criteria->add(new \Criteria('gperm_groupid', $groupid));
        $criteria->add(new \Criteria('gperm_itemid', $itemid));
        $criteria->add(new \Criteria('gperm_modid', $mid));
        $permsObject = $this->getObjects($criteria);
        if (!empty($permsObject)) {
            foreach ($permsObject as $permObject) {
                $this->delete($permObject);
            }
        }
        unset($criteria, $permsObject);

        return true;
    }

    /**
     * @param int $forum
     * @param int $mid
     * @return bool
     */
    public function applyTemplate(int $forum, int $mid = 0): bool
    {
        $this->cacheHelper->delete('permission_forum');
        /** @var PermissionForumHandler $handler */
        $handler = $this->loadHandler('Forum');

        return $handler->applyTemplate($forum, $mid);
    }

    /**
     * @return array
     */
    public function getTemplate(): array
    {
        /** @var PermissionForumHandler $handler */
        $handler  = $this->loadHandler('Forum');
        $template = $handler->getTemplate();

        return $template;
    }

    /**
     * @param array $perms
     * @param int   $groupid
     * @return bool|int
     */
    public function setTemplate(array $perms, int $groupid = 0)
    {
        /** @var PermissionForumHandler $handler */
        $handler = $this->loadHandler('Forum');

        return $handler->setTemplate($perms, $groupid);
    }
}
