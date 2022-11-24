<?php declare(strict_types=1);
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

//  ------------------------------------------------------------------------ //

use Xmf\Request;
use XoopsModules\Newbb\{
    Topic
};
use XoopsModules\Xoopspoll\{
    Constants,
    Helper,
    Log,
    LogHandler,
    Poll,
    PollHandler,
    Utility
};

require_once __DIR__ . '/header.php';
$poll_id  = Request::getInt('poll_id', Request::getInt('poll_id', 0, 'POST'), 'GET');
$topic_id = Request::getInt('topic_id', Request::getInt('topic_id', 0, 'POST'), 'GET');
$forum    = Request::getInt('forum', Request::getInt('forum', 0, 'POST'), 'GET');

//$topicHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Topic');
/** @var Topic $topicObject */
$topicObject = $topicHandler->get($topic_id);
if (!$topicHandler->getPermission($topicObject->getVar('forum_id'), $topicObject->getVar('topic_status'), 'vote')) {
    redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _NOPERM);
}

if (!Request::getInt('option_id', 0, 'POST')) {
    // irmtfan - add error message - simple url
    redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?topic_id={$topic_id}", 1, _MD_NEWBB_POLL_NOOPTION);
}
// poll module
$pollModuleHandler = $moduleHandler->getByDirname($GLOBALS['xoopsModuleConfig']['poll_module']);
$classPoll = null;
$pollObject = null;
$xpLogHandler = null;
$xpPollHandler = null;
if (is_object($pollModuleHandler) && $pollModuleHandler->getVar('isactive')) {
    // new xoopspoll module
    if ($pollModuleHandler->getVar('version') >= 140) {
        xoops_load('constants', $GLOBALS['xoopsModuleConfig']['poll_module']);
        xoops_loadLanguage('main', $GLOBALS['xoopsModuleConfig']['poll_module']);

        /** @var PollHandler $xpPollHandler */
        $xpPollHandler = Helper::getInstance()->getHandler('Poll');
        /** @var LogHandler $xpLogHandler */
        $xpLogHandler = Helper::getInstance()->getHandler('Log');
        /** @var Poll $pollObject */
        $pollObject = $xpPollHandler->get($poll_id); // will create poll if poll_id = 0 exist
        // old xoopspoll or umfrage or any clone from them
    } else {
        require_once $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModuleConfig']['poll_module'] . '/include/constants.php');
        $classPoll  = $topicObject->loadOldPoll();
        $pollObject = new $classPoll($poll_id); // will create poll if poll_id = 0 exist
    }
} else {
    redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_NEWBB_POLLMODULE_ERROR);
}

$mail_author = false;
// new xoopspoll module
if ($pollModuleHandler->getVar('version') >= 201) {
    //    $classConstants = \XoopsModules\Xoopspoll\Constants;
    if (is_object($pollObject)) {
        $optionId = Request::getInt('option_id', 0, 'POST');
        if ($pollObject->getVar('multiple')) {
            $optionId = (array)$optionId; // type cast to make sure it's an array
            $optionId = array_map('\intval', $optionId); // make sure values are integers
        }
        if ($pollObject->hasExpired()) {
            /* poll has expired so just show the results */
            $msg = constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . 'SORRYEXPIRED');
        } else {
            $msg = constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . '_MUSTLOGIN');
            //@todo:: add $url to all redirects
            //            $url = $GLOBALS['xoops']->buildUrl("index.php", array('poll_id' => $poll_id));
            if ($pollObject->isAllowedToVote()) {
                $thisVoter     = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : null;
                $votedThisPoll = $xpLogHandler->hasVoted($poll_id, xoops_getenv('REMOTE_ADDR'), $thisVoter);
                if ($votedThisPoll) {
                    $msg = constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . '_ALREADYVOTED');
                } else {
                    /* user that hasn't voted before in this poll or module preferences allow it */
                    $voteTime = time();
                    if ($pollObject->vote($optionId, xoops_getenv('REMOTE_ADDR'), $voteTime)) {
                        if (!$xpPollHandler->updateCount($pollObject)) { // update the count and save in db
                            echo $pollObject->getHtmlErrors();
                            exit();
                        }
                        $msg = constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . '_THANKSFORVOTE');
                    } else {
                        /* there was a problem registering the vote */
                        redirect_header($GLOBALS['xoops']->buildUrl('index.php', ['poll_id' => $poll_id]), Constants::REDIRECT_DELAY_MEDIUM, constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . '_VOTE_ERROR'));
                    }
                }
                /* set anon user vote (and the time they voted) */
                if (!is_object($GLOBALS['xoopsUser'])) {
                    xoops_load('pollUtility', $GLOBALS['xoopsModuleConfig']['poll_module']);
                    $classPollUtility = new Utility();
                    $classPollUtility::setVoteCookie($poll_id, $voteTime, 0);
                }
            } else {
                $msg = constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . '_CANNOTVOTE');
            }
        }
    } else {
        $msg = constant('_MD_' . \mb_strtoupper((string) $GLOBALS['xoopsModuleConfig']['poll_module']) . '_ERROR_INVALID_POLLID');
    }
    if (null !== $url) {
        redirect_header($url, Constants::REDIRECT_DELAY_MEDIUM, $msg);
    } else {
        redirect_header($GLOBALS['xoops']->buildUrl('viewtopic.php', ['topic_id' => $topic_id]), Constants::REDIRECT_DELAY_MEDIUM, $msg);
    }
    // old xoopspoll or umfrage or any clone from them
} else {
    $classLog = $classPoll . 'Log';
    if (is_object($GLOBALS['xoopsUser'])) {
        if ($classLog::hasVoted($poll_id, Request::getString('REMOTE_ADDR', '', 'SERVER'), $GLOBALS['xoopsUser']->getVar('uid'))) {
            $msg = _PL_ALREADYVOTED;
        } else {
            // irmtfan save ip to db
            $pollObject->vote(Request::getInt('option_id', 0, 'POST'), Request::getString('REMOTE_ADDR', '', 'SERVER'), $GLOBALS['xoopsUser']->getVar('uid'));
            $pollObject->updateCount();
            $msg = _PL_THANKSFORVOTE;
        }
        setcookie("newbb_polls[{$poll_id}]", 1);
    } elseif ($classLog::hasVoted($poll_id, Request::getString('REMOTE_ADDR', '', 'SERVER'))) {
        $msg = _PL_ALREADYVOTED;
        setcookie("newbb_polls[{$poll_id}]", 1);
    } else {
        $voteTime = time();
        $pollObject->vote(Request::getInt('option_id', 0, 'POST'), Request::getString('REMOTE_ADDR', '', 'SERVER'), $voteTime);
        $pollObject->updateCount();
        $msg = _PL_THANKSFORVOTE;
        setcookie("newbb_polls[{$poll_id}]", 1);
    }
}
// irmtfan - simple url
redirect_header("viewtopic.php?topic_id={$topic_id}", 1, $msg);
