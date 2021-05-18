<?php

use Migrations\AbstractMigration;

class UseLongtext extends AbstractMigration
{
  /**
   * Change Method.
   *
   * More information on this method is available here:
   * http://docs.phinx.org/en/latest/migrations.html#the-change-method
   * @return void
   */
  public function change()
  {
    $table = $this->table('email_cron');

    $table->changeColumn('data', 'longtext', [
      'default' => null,
      'null' => true
    ]);
    $table->changeColumn('increment_data', 'longtext', [
      'default' => null,
      'null' => true
    ]);


    $table->save();
  }
}
