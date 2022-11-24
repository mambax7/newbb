<?php declare(strict_types=1);

/**
 * NewBB,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use Xmf\Request;

defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_SESSION_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_SESSION')) {
    define('NEWBB_FUNCTIONS_SESSION', 1);

    /*
     * Currently the newbb session/cookie handlers are limited to:
     * -- one dimension
     * -- "," and "|" are preserved
     *
     */
    /**
     * @param string $name    
     * @param string|mixed[] $string
     */
    function newbbSetSession(string $name, $string = ''): void
    {
        if (is_array($string)) {
            $value = [];
            foreach ($string as $key => $val) {
                $value[] = $key . '|' . $val;
            }
            $string = implode(',', $value);
        }
        $_SESSION['newbb_' . $name] = $string;
    }

    /**
     * @param string $name
     * @param bool   $isArray
     * @return mixed[]|bool
     */
    function newbbGetSession(string $name, bool $isArray = false)
    {
        $value = !empty($_SESSION['newbb_' . $name]) ? $_SESSION['newbb_' . $name] : false;
        if ($isArray) {
            $_value = $value ? explode(',', (string) $value) : [];
            $value  = [];
            if (count($_value) > 0) {
                foreach ($_value as $string) {
                    $key         = mb_substr($string, 0, mb_strpos($string, '|'));
                    $val         = mb_substr($string, mb_strpos($string, '|') + 1);
                    $value[$key] = $val;
                }
            }
            unset($_value);
        }

        return $value;
    }

    /**
     * @param string       $name
     * @param string|mixed[] $string
     * @param int|null     $expire
     */
    function newbbSetCookie(string $name, $string = '', ?int $expire = null): void
    {
        $expire ??= 0;
        global $forumCookie;
        if (is_array($string)) {
            $value = [];
            foreach ($string as $key => $val) {
                $value[] = $key . '|' . $val;
            }
            $string = implode(',', $value);
        }
        setcookie($forumCookie['prefix'] . $name, (string)$string, ['expires' => (int)$expire, 'path' => $forumCookie['path'], 'domain' => $forumCookie['domain'], 'secure' => $forumCookie['secure']]);
    }

    /**
     * @param string $name
     * @param bool        $isArray
     * @return mixed[]|string
     */
    function newbbGetCookie(string $name, bool $isArray = false)
    {
        global $forumCookie;
        //        $value = !empty($_COOKIE[$forumCookie['prefix'] . $name]) ? $_COOKIE[$forumCookie['prefix'] . $name] : null;
        $value = Request::getString($forumCookie['prefix'] . $name, '', 'COOKIE');

        if ($isArray) {
            $_value = $value ? explode(',', (string) $value) : [];
            $value  = [];
            if (count($_value) > 0) {
                foreach ($_value as $string) {
                    $sep = mb_strpos($string, '|');
                    if (false !== $sep) {
                        $key         = mb_substr($string, 0, $sep);
                        $val         = mb_substr($string, $sep + 1);
                        $value[$key] = $val;
                    } else {
                        $value[] = $string;
                    }
                }
            }
            unset($_value);
        }

        return $value;
    }
}
