# Telegram Logger

Send logs to Telegram chat via Telegram bot in your Laravel application

## Install

```

composer require revangelista/telegram-logger

```

Set a new channel in the <b>config/logging.php</b> file, with the following variables:

```php
'telegram' => [
    'driver'  => 'custom',
    'via'     => Logger\TelegramLogger::class,
    'level'   => 'debug',
    'token'   => '<YOUR_BOT_TOKEN>',
    'chat_id' => '<CHAT_ID>',
]
```

Where <b>token</b> is the token of your Telegram bot and <b>chat_id</b> is where the bot will send the messages


If your default log channel is a stack, you can add it to the <b>stack</b> channel like this:
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single', 'telegram'],
]
```

Or you can simply change the default log channel in the .env 
```
LOG_CHANNEL=telegram
```

## Logging

For more information on custom log channels, check the [Laravel documentation](https://laravel.com/docs/master/logging) on this.


## Create bot

For using this package you need to create Telegram bot

1. Go to @BotFather in the Telegram
2. Send ``/newbot``
3. Set up name and bot-name for your bot.
4. Get token and add it to your .env file (it is written above)
5. Go to your bot and send ``/start`` message