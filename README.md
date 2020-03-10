# CakePHP Email cron plugin

Plugin lets you create email cron notifications. 
Lets imagine you send email to the topic's owner on new comment to the topic
In case when user have 5 comments within one topic user will get 5 emails which is may be annoying.

This plugin allows to create an queue for such situation and sends one email with 5 new comments
if between creating date of these comments no more than 30 seconds (configurable) 


## Installation

```sh
composer require thebeaverhead/cakephp-email-cron
```

## Setup

In your app's console:

```sh
bin/cake plugin load EmailCron
```

Apply migration:

```sh
bin/cake migrations migrate -p EmailCron
```

## Usage:

To create an email to be sended (NOT increment):

```php
$emailCron = new EmailCron();
$emailCron
    ->setTo('example@mail.com')
    ->setTemplate('email/template/path')
    ->setSubject('Email subject')
    ->setSendDate(1580108540)
    ->setData([
        'topic' => [
            'title' => 'foo'
        ], 
        'comments' => [
            ['text' => 'foo'], 
            ['text' => 'bar']
        ]
    ])
    ->add();
```
To create increment email to be sended:

```php
$emailCron = new EmailCron();
$emailCron
    ->setTo('example@mail.com')
    ->setTemplate('email/template/path')
    ->setSubject('Email subject')
    ->setSendDate(1580108540)
    ->setData([
        'topic' => [
            'title' => 'foo'
        ],
    ])
    ->setIncrementData([
        'comments' => [
            ['text' => 'foo'], 
            ['text' => 'bar']
        ]
    ])
    ->addIncrement();

// Increment notification has been created
// in 10 sec new comment has been added to the same topic
sleep(10);

$emailCron
    ->setIncrementData([
        'comments' => [
            ['text' => 'baz'], 
        ]
    ])
    ->addIncrement();
);
```

create email template.ctp

```php
<?php
/**
 * @var \App\View\AppView $this 
 * @var array $comments
 * @var \App\Model\Entity\Comment $comment
 * @var \App\Model\Entity\Topic $topic
 */
?>

Hi, your topic <?= $topic['title'] ?> has new comment(s):
<ul>
  <?php foreach ($comments as $key => $comment): ?>
    <li>
      Comment #<?= $key ?>
      <?= $comment['text'] ?>
    </li>
  <?php endforeach; ?>
</ul>
```

#### To send emails run

```sh
bin/cake EmailCron run
```
Actually you need add this command to the crontab.

## Configure:

Shell EmailCron supports config `EmailCron.send_delay` in seconds (30sec by default)
It won't sent an incremental email if previous email has been created or updated less than 30 sec ago.

In config/app.php you can add:
```php
'EmailCron' => [
  'send_delay' => 60
],
```