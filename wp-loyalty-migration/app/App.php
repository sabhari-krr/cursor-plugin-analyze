<?php

namespace WPLoyalty\Migration\App;

defined('ABSPATH') or exit;

class App extends Container
{
    public static $app;

    public static function make()
    {
        if (!isset(self::$app)) {
            self::$app = new static();
        }

        return self::$app;
    }

    /**
     * Bootstrap plugin
     */
    public function bootstrap()
    {
        Setup::init();
        add_action('plugins_loaded', function () {
            do_action('wlmr_before_init');
            Route::register();

            static::registerShortCodes();
            do_action('wlmr_after_init');
        }, 1);
    }

    public static function registerShortCodes()
    {
        // Register the shortcode classes
        $classes = apply_filters('wlmr_get_shortcodes_classes', []);

        foreach ($classes as $class) {
            $class::register();
        }
    }
}