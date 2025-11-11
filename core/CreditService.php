<?php

class CreditService
{
    /**
     * Checks if a given message contains Unicode characters.
     *
     * @param string $message The message to check.
     * @return bool True if the message contains Unicode characters, false otherwise.
     */
    public static function isUnicode($message)
    {
        return strlen($message) !== mb_strlen($message, 'UTF-8');
    }

    /**
     * Calculates the number of SMS credits required for a given message.
     *
     * @param string $message The message content.
     * @return int The number of SMS credits.
     */
    public static function calculateSmsCredits($message)
    {
        if (self::isUnicode($message)) {
            // Unicode messages have a shorter character limit per SMS (70 characters)
            return (int) ceil(mb_strlen($message, 'UTF-8') / 70);
        } else {
            // Standard GSM messages have a longer character limit per SMS (160 characters)
            return (int) ceil(strlen($message) / 160);
        }
    }
}
