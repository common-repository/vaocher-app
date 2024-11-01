<?php

/**
 * Should maintain 1 instance of this class per request by using the getInstance() method.
 */
class VaocherAppDiagnostics
{
    /** @var array */
    protected $messages = [];

    /** @var $this */
    protected static $instance;

    /**
     * @return $this
     */
    public static function getInstance()
    {
        if (! static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param  string  $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Render the message as HTML.
     *
     * @return void
     */
    public function render()
    {
        global $wp_version;

        if (wp_doing_ajax()) {
            return;
        }

        if (! VaocherAppData::isDiagnosticsModeEnabled()) {
            return;
        }

        echo VaocherAppView::includePartialView('diagnostics_output', [
            'wordpressVersion' => $wp_version,
            'woocommerceVersion' => VaocherAppWoocommerce::getInstalledVersion(),
            'woocommerceIsEnabled' => VaocherAppData::isWoocommerceEnabled(),
            'messages' => $this->messages,
        ]);
    }
}