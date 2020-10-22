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
     * Application timezone
     *
     * @string
     */
    private $timezone;

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
        $this->timezone = config('app.timezone');
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
                        'text' => $this->formatText($record),
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
    private function formatText(array $record): string
    {
        try {
            $dateTime = $record['datetime'];
            $dateTime->setTimezone(new \DateTimeZone( $this->timezone ));

            $exLevel = strtolower($record['level_name']);
            $textError = $record['message'];

            $message = '';
            $message .= "{$dateTime->format('Y-m-d H:i:s')} " . PHP_EOL;
            $message .= "<strong>{$this->appName}</strong> (<code>{$exLevel}</code>)" . PHP_EOL;
            $message .= "Environment: {$this->appEnv}" . PHP_EOL . PHP_EOL;
            $message .= "{$textError}" . PHP_EOL;

            if (!empty($record['context'])) {
                $exception = $record['context']['exception'];
                $fileName = $exception->getFile();
                $fileLine = $exception->getLine();
                $message .= "File: {$fileName}:{$fileLine}" . PHP_EOL;
                $message .= "Message: {$exception->getMessage()}";
            }
        } catch (Exception $exception) {
            $message = "Unable to get formatted error due to error: {$exception->getMessage()}" . PHP_EOL . PHP_EOL;
            $message .= json_encode($record);
        }

        return $message;
    }
}
