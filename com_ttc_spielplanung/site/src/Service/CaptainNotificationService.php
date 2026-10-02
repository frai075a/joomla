<?php
namespace Ttc\Component\Spielplanung\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Sends grouped captain notifications after availability changes are committed.
 * Returns warnings to the caller; never writes database data or UI messages.
 */
class CaptainNotificationService
{
    /**
     * @return string[] Notification failures; other teams are still notified.
     */
    public function notify($model, $user, array $changes)
    {
        $changesByCategory = array();
        foreach ($changes as $change)
        {
            $changesByCategory[(int) $change['category_id']][] = $change;
        }

        $warnings = array();
        foreach ($changesByCategory as $categoryId => $categoryChanges)
        {
            try
            {
                $captain = $model->getCaptain($categoryId);
                if (!$captain || empty($captain->email))
                {
                    continue;
                }

                $this->sendCaptainMail($captain, $user, $categoryChanges);
            }
            catch (\Exception $e)
            {
                $warnings[] = $e->getMessage();
            }
        }

        return $warnings;
    }

    private function sendCaptainMail($captain, $user, array $categoryChanges)
    {
        $subject = Text::sprintf('COM_TTC_SPIELPLANUNG_MAIL_AVAILABILITY_CHANGED_SUBJECT', $user->name);

        $lines = array();
        $lines[] = Text::sprintf('COM_TTC_SPIELPLANUNG_MAIL_AVAILABILITY_CHANGED_INTRO', $user->name);
        $lines[] = '';

        foreach ($categoryChanges as $change) {
            $gameLabel = Text::sprintf(
                'COM_TTC_SPIELPLANUNG_MAIL_GAME_LABEL',
                HTMLHelper::_('date', $change['spieldatum'] . ' ' . $change['uhrzeit'], 'd.m.Y H:i'),
                $change['gegner'],
                $change['sporthalle']
            );
            $oldLabel = Text::_($change['old_status'] == 1 ? 'COM_TTC_SPIELPLANUNG_STATUS_YES' : 'COM_TTC_SPIELPLANUNG_STATUS_NO');
            $newLabel = Text::_($change['new_status'] == 1 ? 'COM_TTC_SPIELPLANUNG_STATUS_YES' : 'COM_TTC_SPIELPLANUNG_STATUS_NO');

            $lines[] = '- ' . $gameLabel . ': ' . $oldLabel . ' -> ' . $newLabel;
        }

        $body = implode("\n", $lines);

        $mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();
        $mailer->setSubject($subject);
        $mailer->setBody($body);
        $mailer->addRecipient($captain->email);

        if ($mailer->Send() === false)
        {
            throw new \RuntimeException(Text::_('COM_TTC_SPIELPLANUNG_MAIL_SEND_ERROR'));
        }
    }
}
