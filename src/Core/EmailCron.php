<?php
namespace EmailCron\Core;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use EmailCron\Model\Table\EmailCronTable;

class EmailCron
{
  /** @var EmailCronTable */
  protected $EmailCron;
  /** @var array */
  protected $emails;
  /** @var string */
  protected $template;
  /** @var integer */
  protected $sendDate;
  /** @var string */
  protected $subject;
  /** @var array */
  protected $data = [];
  /** @var array */
  protected $incrementData = [];

  public function __construct()
  {
    $this->EmailCron = TableRegistry::getTableLocator()->get('EmailCron.EmailCron');
  }

  /**
   * @param string|array $email
   * @return EmailCron
   */
  public function setTo($email)
  {
    $this->emails = is_array($email) ? array_unique($email) : [$email];

    return $this;
  }

  /**
   * @param string $template
   * @return EmailCron
   */
  public function setTemplate(string $template)
  {
    $this->template = $template;

    return $this;
  }

  /**
   * @param string $subject
   * @return EmailCron
   */
  public function setSubject(string $subject)
  {
    $this->subject = $subject;

    return $this;
  }

  /**
   * @param int $sendDate
   * @return EmailCron
   */
  public function setSendDate(int $sendDate)
  {
    $this->sendDate = $sendDate;

    return $this;
  }

  /**
   * @param array $data
   * @return EmailCron
   */
  public function setData(array $data)
  {
    $this->data = $data;

    return $this;
  }

  /**
   * @param array $data
   * @return EmailCron
   */
  public function setIncrementData(array $data)
  {
    $this->incrementData = $data;

    return $this;
  }

  /**
   * Add notification
   *
   * @return mixed
   */
  public function add()
  {
    foreach ($this->emails as $email) {
      $emailCron = $this->EmailCron->newEntity([
        'email' => $email,
        'subject' => $this->subject,
        'template_path' => $this->template,
        'data' => json_encode($this->data),
        'increment_data' => null,
        'is_increment' => false,
        'send_date' => $this->sendDate,
      ]);

      $this->EmailCron->save($emailCron);
    }

    return true;
  }

  /**
   * Add increment notification
   *
   * @return mixed
   */
  public function addIncrement()
  {
    foreach ($this->emails as $email) {
      /** @var \EmailCron\Model\Entity\EmailCron $emailCron */
      $emailCron = $this->EmailCron->find()
        ->where([
          'email' => $email,
          'subject' => $this->subject,
          'template_path' => $this->template,
          'is_increment IS' => true,
          'is_sent IS' => false
        ])
        ->first();

      if (!$emailCron) {
        $emailCron = $this->EmailCron->newEntity([
          'email' => $email,
          'subject' => $this->subject,
          'template_path' => $this->template,
          'data' => json_encode($this->data),
          'increment_data' => json_encode($this->incrementData),
          'is_increment' => true,
          'send_date' => $this->sendDate,
        ]);

        $this->EmailCron->save($emailCron);

        continue;
      }

      $oldIncrementData = json_decode($emailCron->increment_data, true);
      $keys = array_unique(array_merge(array_keys($oldIncrementData), array_keys($this->incrementData)));

      $incrementDataToSave = [];

      foreach ($keys as $key) {
        $incrementDataToSave[$key] = array_merge($oldIncrementData[$key] ?? [], $this->incrementData[$key] ?? []);
      }

      $emailCron = $this->EmailCron->patchEntity($emailCron, [
        'data' => json_encode($this->data),
        'increment_data' => json_encode($incrementDataToSave),
        'sent_date' => $this->sendDate,
      ]);

      $this->EmailCron->save($emailCron);
    }

    return true;
  }
}
