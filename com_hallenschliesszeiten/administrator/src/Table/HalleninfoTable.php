<?php
namespace Ttc\Component\Hallenschliesszeiten\Administrator\Table;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
class HalleninfoTable extends Table
{
    protected $_supportNullValue = true;
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__ttc_halleninfos', 'id', $db);
        $this->setColumnAlias('published', 'state');
        $this->state = 1;
    }
    public function check()
    {

        $value = (string) $this->datum;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->setError('Bitte ein gültiges Datum eingeben.');
            return false;
        }
        $db = $this->getDatabase();
        $query = $db->createQuery()->select('COUNT(*)')->from($db->quoteName('#__ttc_halleninfos'))
            ->where($db->quoteName('datum') . ' = ' . $db->quote($this->datum))
            ->where($db->quoteName('id') . ' <> ' . (int) $this->id);
        if ((int) $db->setQuery($query)->loadResult() > 0) {
            $this->setError('Für dieses Datum ist bereits eine Halleninfo vorhanden.');
            return false;
        }

        if (\Joomla\String\StringHelper::strlen((string) $this->information) > 400) {
            $this->setError('Der Text darf höchstens 400 Zeichen enthalten.');
            return false;
        }
        if (empty($this->id) && empty($this->ordering)) {
            $this->ordering = $this->getNextOrder();
        }
        return parent::check();
    }
    public function store($updateNulls = true)
    {
        $userId = Factory::getApplication()->getIdentity()->id;
        if (empty($this->id)) {
            $this->created_by = $userId;
        }
        $this->modified_by = $userId;
        return parent::store($updateNulls);
    }
}
