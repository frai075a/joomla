<?php
namespace Ttc\Component\Hallenschliesszeiten\Administrator\Table;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
class SchliesszeitTable extends Table
{
    protected $_supportNullValue = true;
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__ttc_hallenschliesszeiten', 'id', $db);
        $this->setColumnAlias('published', 'state');
        $this->state = 1;
    }
    public function check()
    {

        $value = (string) $this->beginn;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->setError('Bitte ein gültiges Datum eingeben.');
            return false;
        }

        $value = (string) $this->ende;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            $this->setError('Bitte ein gültiges Datum eingeben.');
            return false;
        }
        if ($this->ende < $this->beginn) {
            $this->setError('Das Ende darf nicht vor dem Beginn liegen.');
            return false;
        }

        if (\Joomla\String\StringHelper::strlen((string) $this->schliessinfo) > 75) {
            $this->setError('Der Text darf höchstens 75 Zeichen enthalten.');
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
