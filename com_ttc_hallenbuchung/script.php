<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

/** Preserve legacy bookings and migrate links when installing the renamed component. */
class com_ttc_hallenbuchungInstallerScript
{
    public function postflight($type, $parent)
    {
        if (!in_array($type, ['install', 'update', 'discover_install'], true)) {
            return true;
        }

        $file = JPATH_ROOT . '/components/com_ttc_hallenbuchung/tmpl/hallenfussballteilnahme/default.xml';
        if (is_file($file) && !File::delete($file)) {
            return false;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $old = 'com_ttc_hallenfussball';
        $new = 'com_ttc_hallenbuchung';
        $query = $db->getQuery(true)->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote($new));
        $extensionId = (int) $db->setQuery($query)->loadResult();
        if (!$extensionId) {
            throw new \RuntimeException('The Hallenbuchung extension record is missing.');
        }

        // Only migrate exact component options in frontend component menu links.
        $query = $db->getQuery(true)->select($db->quoteName(['id', 'link']))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        foreach ($db->setQuery($query)->loadObjectList() as $menu) {
            $link = preg_replace('/([?&])option=' . preg_quote($old, '/') . '(?=&|$)/', '$1option=' . $new, $menu->link);
            if ($link !== $menu->link) {
                $query = $db->getQuery(true)->update($db->quoteName('#__menu'))
                    ->set($db->quoteName('link') . ' = ' . $db->quote($link))
                    ->set($db->quoteName('component_id') . ' = ' . $extensionId)
                    ->where($db->quoteName('id') . ' = ' . (int) $menu->id);
                $db->setQuery($query)->execute();
            }
        }

        // A fresh installation receives the old component's ACL. Later updates
        // preserve permissions already configured for Hallenbuchung.
        if (in_array($type, ['install', 'discover_install'], true)) {
            $query = $db->getQuery(true)->select($db->quoteName('params'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
                ->where($db->quoteName('element') . ' = ' . $db->quote($old));
            $params = $db->setQuery($query)->loadResult();
            if ($params !== null && $params !== false) {
                $query = $db->getQuery(true)->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('params') . ' = ' . $db->quote($params))
                    ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
                $db->setQuery($query)->execute();
            }

            $query = $db->getQuery(true)->select($db->quoteName('rules'))
                ->from($db->quoteName('#__assets'))
                ->where($db->quoteName('name') . ' = ' . $db->quote($old));
            $rules = $db->setQuery($query)->loadResult();
            if ($rules !== null && $rules !== false) {
                $query = $db->getQuery(true)->update($db->quoteName('#__assets'))
                    ->set($db->quoteName('rules') . ' = ' . $db->quote($rules))
                    ->where($db->quoteName('name') . ' = ' . $db->quote($new));
                $db->setQuery($query)->execute();
            }
        }

        return true;
    }
}
