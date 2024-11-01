<?php

class VaocherAppResponse
{
    /** @var bool */
    public $success = false;

    /** @var int */
    public $code = 0;

    /** @var array|string */
    public $body;

    /** @var string */
    public $renderableBody;

    public function __construct($response)
    {
        $body = wp_remote_retrieve_body($response);
        $this->code = (int) wp_remote_retrieve_response_code($response);
        $this->success = $this->code >= 200 && $this->code <= 299;
        $this->body = $this->isJson($body) ? json_decode($body, true) : $body;
        $this->renderableBody = $this->isJson($body) ? $body : '<div style="word-break: break-all; overflow-x: none; overflow-y: auto; max-height: 200px;">'.htmlentities($body).'</div>';
    }

    private function isJson($string)
    {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }
}