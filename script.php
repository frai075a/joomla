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

        // Repair the nullable status definition even after an interrupted upgrade.
        $statusColumns = $db->getTableColumns('#__ttc_spielplanung', false);
        $status = $statusColumns['status'];
        if (strtoupper((string) $status->Null) !== 'YES' || $status->Default !== null) {
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__ttc_spielplanung')
                . ' MODIFY COLUMN ' . $db->quoteName('status') . ' TINYINT(3) NULL DEFAULT NULL');
            $db->execute();
        }

        return true;
    }
}
