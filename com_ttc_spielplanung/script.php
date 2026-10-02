<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Keep fresh installs and upgrades (including previously broken upgrades) aligned.
 */
class com_ttc_spielplanungInstallerScript
{
    public function postflight($type, $parent)
    {
        if (!in_array($type, array('install', 'update', 'discover_install'), true))
        {
            return true;
        }

        $db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $columns = $db->getTableColumns('#__ttc_mmb', false);

        if (!isset($columns['is_captain']))
        {
            // Do not swallow failures here: an unusable schema must fail installation.
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__ttc_mmb')
                . ' ADD COLUMN ' . $db->quoteName('is_captain')
                . ' TINYINT(3) NOT NULL DEFAULT 0 AFTER ' . $db->quoteName('position'));
            $db->execute();
        }

        return true;
    }
}
