<?php

class VaocherAppLogger
{
    public static function write($message, $data = [])
    {
        // Dont do anything in production
        if (VaocherApp::getInstance()->isEnvProduction()) {
            return;
        }

        $message = sprintf(
            '[%s] :: %s',
            date('Y-m-d H:i:s'),
            $message
        );

        if ($data) {
            if (! VaocherAppHelpers::strEndsWith($message, '.')) {
                $message .= '.';
            }
            $message .= ' ';
            $message .= json_encode($data);
        }

        $message .= PHP_EOL;

        file_put_contents(static::getCurrentLogFile(), $message, FILE_APPEND);
    }

    /**
     * @return string
     */
    protected static function getCurrentLogFile()
    {
        return sprintf(
            '%s/storage/logs/%s.log',
            rtrim(VAOCHER_APP_BASE_PATH, '/'),
            date('Y-m-d')
        );
    }
}