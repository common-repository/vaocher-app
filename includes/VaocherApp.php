<?php

class VaocherApp
{
    const ORDER_META_VOUCHER_CODE = 'vaocherapp_voucher_code';
    const ORDER_META_VOUCHER_APPLIED_BALANCE = 'vaocherapp_applied_balance';
    const ORDER_META_VOUCHER_REDEEMED_BALANCE = 'vaocherapp_redeemed_balance';

    /** @var string */
    protected $pluginName = 'vaocher-app';

    /** @var string */
    protected $settingPageName = 'vaocher-app-settings';

    /** @var string */
    protected $version = '1.0.0';

    /**
     * Maintaining and registering all hooks that power the plugin.
     *
     * @var \VaocherAppLoader
     */
    protected $loader;

    /** @var \VaocherApp */
    protected static $selfInstance;

    public function __construct()
    {
        if (defined('VAOCHER_APP_VERSION')) {
            $this->version = VAOCHER_APP_VERSION;
        }

        $this->loader = new VaocherAppLoader();
    }

    public static function getInstance()
    {
        if (! self::$selfInstance) {
            self::$selfInstance = new self();
        }

        return self::$selfInstance;
    }

    public function boot()
    {
        $this->setLocale();
        $this->registerPluginHooks();
        $this->registerWoocommerceHooks();
        $this->registerAdminResources();
        $this->registerShortcodes();

        $this->loader->boot();
    }

    public function isEnvProduction()
    {
        return VAOCHER_APP_ENV === 'production';
    }

    public function isEnvDevelopment()
    {
        return VAOCHER_APP_ENV === 'development';
    }

    public function getSettingPageName()
    {
        return $this->settingPageName;
    }

    public function getPluginName()
    {
        return $this->pluginName;
    }

    public function getVersion()
    {
        return $this->version;
    }

    private function setLocale()
    {
        $this->loader->addAction('plugins_loaded', function () {
            load_plugin_textdomain(
                'vaocher-app',
                false,
                dirname(dirname(plugin_basename(__FILE__))).'/languages/'
            );
        });
    }

    private function registerPluginHooks()
    {
        register_activation_hook(VAOCHER_APP_PLUGIN_REGISTER_PATH, 'vaocherapp_plugin_activated');
        register_deactivation_hook(VAOCHER_APP_PLUGIN_REGISTER_PATH, 'vaocherapp_plugin_deactivated');
        register_uninstall_hook(VAOCHER_APP_PLUGIN_REGISTER_PATH, 'vaocherapp_plugin_uninstalled');
    }

    private function registerWoocommerceHooks()
    {
        VaocherAppWoocommerce::registerHooks();
    }

    private function registerAdminResources()
    {
        // Add settings page in the menu
        $this->loader->addAction('admin_menu', function () {
            add_options_page(
                'VaocherApp',
                'VaocherApp',
                'manage_options',
                $this->getSettingPageName(),
                VaocherAppView::renderAdminSettingView()
            );
        });

        $this->loader->addAction('admin_enqueue_scripts', function ($hook) {
            if ($hook !== 'settings_page_'.$this->getSettingPageName()) {
                return;
            }

            wp_enqueue_script('vaocherapp-admin-custom-script', VaocherAppAssets::load('assets/js/main.js'), ['jquery'], '1.0.0');
            wp_enqueue_style('vaocherapp-admin-custom-style',  VaocherAppAssets::load('assets/css/main.css'), [], '1.0.0');
        });

        // Add settings page in the plugin list
        $this->loader->addFilter('plugin_action_links_'.VAOCHER_APP_PLUGIN_BASE_NAME, VaocherAppView::renderSettingMenuItem());

        // Add global notification
        $this->loader->addAction('admin_notices', VaocherAppView::renderGlobalNotification());
    }

    private function registerShortcodes()
    {
        VaocherAppShortcode::register();
    }
}
