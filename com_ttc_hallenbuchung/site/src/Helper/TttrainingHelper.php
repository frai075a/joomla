<?php
namespace Ttchallenbuchung\Component\Ttc_hallenbuchung\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\User\User;

class TttrainingHelper
{
    public static function canEdit(object $item, User $user): bool
    {
        return $user->id > 0 && ($user->authorise('core.edit', 'com_ttc_hallenbuchung')
            || ($user->authorise('core.edit.own', 'com_ttc_hallenbuchung') && (int) $item->created_by === (int) $user->id));
    }
}
