<?php
namespace EmailCron\Model\Table;

use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use EmailCron\Model\Entity\EmailCron;

/**
 * EmailCron Model
 *
 * @method EmailCron get($primaryKey, $options = [])
 * @method EmailCron newEntity($data = null, array $options = [])
 * @method EmailCron[] newEntities(array $data, array $options = [])
 * @method EmailCron|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method EmailCron saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method EmailCron patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method EmailCron[] patchEntities($entities, array $data, array $options = [])
 * @method EmailCron findOrCreate($search, callable $callback = null, $options = [])
 */
class EmailCronTable extends Table
{
  /**
   * Initialize method
   *
   * @param array $config The configuration for the Table.
   * @return void
   */
  public function initialize(array $config)
  {
    parent::initialize($config);

    $this->setTable('email_cron');
    $this->setDisplayField('id');
    $this->setPrimaryKey('id');
  }

  /**
   * Default validation rules.
   *
   * @param Validator $validator Validator instance.
   * @return Validator
   */
  public function validationDefault(Validator $validator)
  {
    $validator
      ->boolean('available')
      ->allowEmptyString('available', null, false);

    $validator
      ->maxLength('email', 320)
      ->requirePresence('email', 'create')
      ->allowEmptyString('email', null, false)
      ->email('email', false);

    $validator
      ->integer('send_date')
      ->allowEmptyString('send_date');

    $validator
      ->integer('sent_date')
      ->allowEmptyString('sent_date');

    $validator
      ->scalar('template_path')
      ->requirePresence('template_path', 'create')
      ->allowEmptyString('template_path', null, false);

    $validator
      ->scalar('subject')
      ->requirePresence('subject', 'create')
      ->allowEmptyString('subject', null, false);

    $validator
      ->allowEmptyString('data');

    $validator
      ->allowEmptyString('increment_data');

    $validator
      ->boolean('is_increment')
      ->allowEmptyString('is_increment', null, false);

    $validator
      ->boolean('is_sent')
      ->allowEmptyString('is_sent', null, false);

    return $validator;
  }

  /**
   * @param Event $event
   * @param EmailCron $emailCron
   * @param $options
   * @return bool
   */
  public function beforeSave(Event $event, EmailCron $emailCron, \ArrayObject $options)
  {
    if ($emailCron->isNew()) {
      $emailCron->created = Time::now()->timestamp;
    } else {
      $emailCron->modified = Time::now()->timestamp;
    }

    return true;
  }

  /**
   * @param EmailCron $emailCron
   */
  public function markSent(EmailCron $emailCron)
  {
    $emailCron->sent_date = Time::now()->timestamp;
    $emailCron->is_sent = true;

    $this->save($emailCron);
  }
}
