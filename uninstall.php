<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die('Direct access not allowed');
}


/**
 * Class MTCaptchaUninstall
 */
class MTCaptchaUninstall
{
    /**
     * MTCaptchaUninstall constructor.
     */
    public function __construct()
    {
        $this->mt_captcha_delete([
                    "mt_site_private_key",
                    "mt_captcha_enable",
                    "mt_captcha_disable_mtcaptcha",
                    "mt_captcha_show_captcha_label_form",
                    "mt_site_key",
                    "mt_captcha_theme",
                    "mt_captcha_lang",
                    "mt_captcha_widget_size",
                    "mt_captcha_enable_json_value",
                    "mt_captcha_json_value"
                ]);
    }

    private function mt_captcha_delete($array)
    {
        foreach ($array as $item) {
            delete_option(sprintf('mtcaptcha_%s', $item));
        }
    }
}

new MTCaptchaUninstall();
