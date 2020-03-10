<?php
namespace EmailCron\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use EmailCron\Model\Entity\EmailCron;
use EmailCron\Model\Table\EmailCronTable;

/**
 * EmailCron shell command.
 *
 * @property EmailCronTable EmailCron
 */
class EmailCronShell extends Shell
{
  const DEFAULT_SEND_DELAY = 30;

  public $modelClass = 'EmailCron.EmailCron';

  public function run()
  {
    $delay = Configure::read('EmailCron.send_delay', self::DEFAULT_SEND_DELAY);

    $emailCrons = $this->EmailCron->find()
      ->where(['is_sent IS' => false])
      ->where([
        'OR' => [
          ['send_date IS' => null],
          ['send_date <=' => Time::now()->timestamp],
        ]
      ])
      ->where([
        'OR' => [
          ['is_increment IS' => false],
          [
            'is_increment IS' => true,
            'created <' => Time::now()->timestamp - $delay,
            'OR' => [
              ['modified IS' => null],
              ['modified <' => Time::now()->timestamp - $delay]
            ]
          ]
        ]
      ]);

    $email = new Email();
    $email->setEmailFormat('html');

    /** @var EmailCron $emailCron */
    foreach ($emailCrons as $emailCron) {
      $data = (array) json_decode($emailCron->data, true);
      $incrementData = (array) json_decode($emailCron->increment_data, true);

      $email->viewBuilder()->setTemplate($emailCron->template_path);
      $email
        ->setTo($emailCron->email)
        ->setSubject($emailCron->subject)
        ->setViewVars($data + $incrementData)
        ->send();

      $this->EmailCron->markSent($emailCron);
    }
  }
}
