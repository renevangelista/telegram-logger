<?php

namespace Logger;

use Exception;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class TelegramHandler
 * @package App\Logging
 */
class TelegramHandler extends AbstractProcessingHandler
{
    /**
     * Bot API token
     *
     * @var string
     */
    private $botToken;

    /**
     * Chat id for bot
     *
     * @var array
     */
    private $chatId;

    /**
     * Application name
     *
     * @string
     */
    private $appName;

    /**
     * Application environment
     *
     * @string
     */
    private $appEnv;

    /**
     * TelegramHandler constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $level = Logger::toMonologLevel($config['level']);

        parent::__construct($level, true);

        // define variables for making Telegram request
        $this->botToken = $config['token'];

        if (is_array($config['chat_id'])) {
            $this->chatId = $config['chat_id'];
        } else {
            $this->chatId[] = $config['chat_id'];
        }

        // define variables for text message
        $this->appName = config('app.name');
        $this->appEnv = config('app.env');
    }

    /**
     * @param array $record
     */
    public function write(array $record): void
    {
        if (!$this->botToken || !$this->chatId) {
            throw new \InvalidArgumentException('Bot token or chat id is not defined for Telegram logger');
        }

        foreach ($this->chatId as $chatId) {
            // trying to make request and send notification
            try {
                file_get_contents(
                    'https://api.telegram.org/bot' . $this->botToken . '/sendMessage?'
                    . http_build_query([
                        'text' => $this->formatText($record['formatted'], $record['level_name']),
                        'chat_id' => $chatId,
                        'parse_mode' => 'html'
                    ])
                );
            } catch (Exception $exception) {
                \Log::channel('single')->error($exception->getMessage());
            }
        }

    }

    /**
     * @param string $text
     * @param string $level
     * @return string
     */
    private function formatText(string $text, string $level): string
    {
        return '<b>' . $this->appName . '</b> (' . $level . ')' . PHP_EOL . 'Env: ' . $this->appEnv . PHP_EOL . $text;
    }
}