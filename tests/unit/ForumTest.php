<?php declare(strict_types=1);

namespace XoopsModules\Newbb\Tests\Unit;

use PHPUnit\Framework\TestCase;
use XoopsModules\Newbb\Forum;

// Mock the XoopsObject class
if (!class_exists('XoopsObject')) {
    class XoopsObject
    {
        public function __construct() {}
        public function initVar($key, $type, $value = null) {}
        public function getVar($key) {
            if ($key === 'forum_moderator') {
                return [1, 2, 3];
            }
            return null;
        }
    }
}

// Mock the newbbGetUnameFromIds function
if (!function_exists('newbbGetUnameFromIds')) {
    function newbbGetUnameFromIds($uids, $show_realname = false, $linked = false)
    {
        return ['admin', 'user1', 'user2'];
    }
}

// Mock the $GLOBALS['xoops'] object
$GLOBALS['xoops'] = new class {
    public function path($path)
    {
        return __DIR__ . '/../../' . $path;
    }
};

class ForumTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            Forum::class,
            new Forum()
        );
    }

    public function testDispForumModerators(): void
    {
        $forum = new Forum();
        $this->assertEquals(
            'admin, user1, user2',
            $forum->dispForumModerators()
        );
    }
}
