<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
class Com_HallenschliesszeitenInstallerScript
{
    public function preflight($type, $parent)
    {
        if ($type !== 'uninstall' && version_compare(JVERSION, '6.0', '<')) {
            Factory::getApplication()->enqueueMessage('Diese Komponente benötigt Joomla 6 oder neuer.', 'error');
            return false;
        }
        return true;
    }
    public function postflight($type, $parent)
    {
        if ($type === 'uninstall') { return; }
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()->select('extension_id')->from($db->quoteName('#__extensions'))
            ->where("element = " . $db->quote('com_hallenschliesszeiten'))->where("type = " . $db->quote('component'));
        $componentId = (int) $db->setQuery($query)->loadResult();
        $query = $db->createQuery()->select(['id', 'link'])->from($db->quoteName('#__menu'))
            ->where('client_id = 0')->where('type = ' . $db->quote('component'));
        foreach ($db->setQuery($query)->loadObjectList() as $menu) {
            if (!preg_match('/(?:[?&])option=com_halleninfos(?:&|$)/', $menu->link)) { continue; }
            $menu->link = preg_replace('/([?&]option=)com_halleninfos(?=&|$)/', '$1com_hallenschliesszeiten', $menu->link);
            $menu->component_id = $componentId;
            $db->updateObject('#__menu', $menu, 'id');
        }
        Factory::getApplication()->enqueueMessage('Hallenschließzeiten und Halleninfos sind zusammengeführt. Bitte die Berechtigungen der gemeinsamen Komponente prüfen. Die bisherigen Fachtabellen bleiben erhalten.', 'message');
    }
}
