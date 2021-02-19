<?php
/*
* Plugin Name: MTCaptcha WordPress Plugin
* Description: MTCaptcha is a efficient security solution to protect your wordpress website against spam comments and brute-force attacks.  It can be integrated with the comments, login, registration, forgot password, contact form 7 and woocommerce checkout
* Version: 2.7.0
* Author: MTCaptcha
* Author URI: https://www.mtcaptcha.com
* License: Apache License, captchaLabel 2.0
* Text Domain: mtcaptcha
*/

if (!defined('ABSPATH')) {
    die('Direct access not allowed!');
}


class mtcaptcha
{
    const UPDATE = 'update';
    const DISABLE = 'disable';

    const MT_ACTION = 'mt_action';
    const MT_PAGE_OPTIONS_QUERY = '?page=mt_options';

    const MT_OPTION_SHOW_CAPTCHA_LABEL = 'mt_captcha_show_captcha_label_form';
    const MT_OPTION_SITE_KEY = 'mt_site_key';
    const MT_OPTION_PRIVATE_KEY = 'mt_site_private_key';
    const MT_OPTION_ENABLE = 'mt_captcha_enable';
    const MT_OPTION_ENABLE_CAPTCHA_FOR_FORMS = 'mt_captcha_disable_mtcaptcha';
    const MT_OPTION_THEME = 'mt_captcha_theme';
    const MT_OPTION_LANGUAGE = 'mt_captcha_lang';
    const MT_OPTION_WIDGET_SIZE = 'mt_captcha_widget_size';
    const MT_OPTION_ENABLE_JSON_VALUE = 'mt_captcha_enable_json_value';
    const MT_OPTION_JSON_VALUE = 'mt_captcha_json_value';

    private $pluginName;
    private $captchaLabel;
    private $loginFormCheckbox;
    private $registrationFormCheckbox;
    private $lostPasswordFormCheckbox;
    private $resetPasswordFormCheckbox;
    private $commentFormCheckbox;
    private $woocommerceFormCheckbox;
    private $privateKey;
    private $siteKey;
    private $enableCaptcha;
    private $theme;
    private $language;
    private $jsonValue;
    private $jsonElement;
    private $widgetSize;

    /**
     * MTCaptcha constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'run']);
        add_action('activated_plugin', [$this, 'activation']);
    }

    public function updateSettings()
    {
        if (current_user_can('manage_options')) {
            $hash = null;
            $options = [self::MT_OPTION_SHOW_CAPTCHA_LABEL, 
            self::MT_OPTION_SITE_KEY, 
            self::MT_OPTION_PRIVATE_KEY, 
            self::MT_OPTION_ENABLE,
            self::MT_OPTION_THEME, 
            self::MT_OPTION_LANGUAGE,  
            self::MT_OPTION_WIDGET_SIZE, 
            self::MT_OPTION_ENABLE_JSON_VALUE, 
            self::MT_OPTION_JSON_VALUE
            ];

            foreach ($options as $option) {
                $postValue = filter_input(INPUT_POST, $option, FILTER_SANITIZE_SPECIAL_CHARS);
                if (!$postValue) {
                    $postValue = '';
                }
                update_option($option, $postValue);

                if (substr_count($option, 'key')) {
                    $hash .= $postValue;
                }
            }

            $enable_captcha_forms   = filter_input(INPUT_POST, self::MT_OPTION_ENABLE_CAPTCHA_FOR_FORMS, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            update_option(self::MT_OPTION_ENABLE_CAPTCHA_FOR_FORMS, $enable_captcha_forms);
        }
    }

    /**
     * @param $links
     * @return array
     */
    public function action_links($links)
    {
        return array_merge(['settings' => sprintf('<a href="options-general.php%s">%s</a>', self::MT_PAGE_OPTIONS_QUERY, __('Setings', 'mtcaptcha'))], $links);
    }

    public function activation($plugin)
    {
        if ($plugin === plugin_basename(__FILE__) && (!get_option(self::MT_OPTION_PRIVATE_KEY) || !get_option(self::MT_OPTION_ENABLE))) {
            exit(wp_redirect(admin_url(sprintf('options-general.php%s', self::MT_PAGE_OPTIONS_QUERY))));
        }
    }

    public function mt_captcha_display_content(){
        echo "<p id='mt_captcha_display'>".__("You have to <a href=\"https://www.mtcaptcha.com/pricing/\" target=\"blank\" rel=\"external\">register your domain</a> first, get private key from MTCaptcha and save it below.", "mtcaptcha")."</p>";
     }

    public function options_page()
    {
        echo sprintf('<div class="wrap"><h1>%s - %s</h1><form method="post" action="%s">', $this->pluginName, __('Settings', 'mtcaptcha'), self::MT_PAGE_OPTIONS_QUERY);

        settings_fields('mt_captcha_header_section');
        settings_fields("mt_captcha_basic_option_header_section");   
        settings_fields("mt_captcha_adv_option_header_section"); 
        do_settings_sections('mt_options');

        submit_button();

        echo sprintf('<input type="hidden" name="%s" value="%s">%s</form>%s</div>', self::MT_ACTION, self::UPDATE, PHP_EOL," ");
    }

    public function menu()
    {
        $this->enqueue_main();
        add_submenu_page('options-general.php', $this->pluginName, 'MTCaptcha', 'manage_options', 'mt_options', [$this, 'options_page']);
        add_action('admin_init', [$this, 'display_options']);
    }

    public function display_mt_site_private_key()
    {
        echo sprintf('<input type="text" name="%1$s" class="regular-text" id="%1$s" value="%2$s" /><br/>', self::MT_OPTION_PRIVATE_KEY, $this->privateKey);
        echo 'The private key given to you when you <a href="https://admin.mtcaptcha.com/signup/profile?plantype=A" target="blank">register for mtcaptcha.</a>';
    }

    public function display_mt_captcha_enable()
    {
        function getEnableCaptcha(){
            $mt_captcha_enable = filter_var(get_option("mt_captcha_enable"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $captcha_enable_options = "";
            $enable_options = array(
                "all" => "All Users",
                "login" => "Logged in Users",
                "logout" => "Logged Out Users"
               );
            foreach( $enable_options as $captcha_enable_eoption => $en_option ) {
                if($captcha_enable_eoption === $mt_captcha_enable){
                    $captcha_enable_options = $captcha_enable_options."<option selected value=\"{$captcha_enable_eoption}\">{$en_option}</option>";
                }else{
                    $captcha_enable_options = $captcha_enable_options."<option value=\"{$captcha_enable_eoption}\">{$en_option}</option>";
                }
            }
            return $captcha_enable_options;
        }
        echo "<select name=\"mt_captcha_enable\" id=\"mt_captcha_enable\" > ".getEnableCaptcha()."</select>";
    }

    public function display_mt_captcha_disable_mtcaptcha()
    {
        $checkbox_options = (!empty(get_option("mt_captcha_disable_mtcaptcha"))) ? get_option("mt_captcha_disable_mtcaptcha") : [];
        $wp_forms = array(
            'login'          => __( 'Login Form', 'mtcaptcha' ),
            'registration'   => __( 'Registration Form', 'mtcaptcha' ),
            'lost_password'  => __( 'Lost Password Form', 'mtcaptcha' ),
            'reset_password' => __( 'Reset Password Form', 'mtcaptcha' ),
            'comment'        => __( 'Comment Form', 'mtcaptcha' ),
            'wc_checkout'    => __( 'WooCommerce Checkout', 'mtcaptcha' )
        );
        foreach( $wp_forms as $formId => $formName ) {
            if(sizeof($checkbox_options) == 0) {
                $html = "<input type=\"checkbox\" name=\"mt_captcha_disable_mtcaptcha[$formId]\" id=\"mt_captcha_{$formId}_check_disable\" value=\"{$formId}\" ".checked(true, false , false)."/>";
            } else {
                if(isset($checkbox_options[$formId])) {
                    $html = "<input type=\"checkbox\" name=\"mt_captcha_disable_mtcaptcha[$formId]\" id=\"mt_captcha_{$formId}_check_disable\" value=\"{$formId}\" ".checked($formId, $checkbox_options[$formId], false)."/>";
                } else {
                    $html = "<input type=\"checkbox\" name=\"mt_captcha_disable_mtcaptcha[$formId]\" id=\"mt_captcha_{$formId}_check_disable\" value=\"{$formId}\" ".checked(true, false , false)."/>";
                }
            }
            $html .= "<label for=\"mt_captcha_{$formId}_check_disable\">".$formName."</label></br>";
            echo $html;
        }
    }
  
    public function display_mt_captcha_show_captcha_label_form()
    {
        echo sprintf('<input type="checkbox" name="%1$s" id="%1$s" value="3" %2$s />', self::MT_OPTION_SHOW_CAPTCHA_LABEL, checked(3, $this->captchaLabel, false));
        echo"Show or Hide Captcha label in the forms";   
    }

    public function display_mt_site_key()
    {
        echo sprintf('<input type="text" name="%1$s" class="regular-text" id="%1$s" value="%2$s" /><br/>', self::MT_OPTION_SITE_KEY, $this->siteKey);
        echo 'The site key given to you when you <a href="https://admin.mtcaptcha.com/signup/profile?plantype=A" target="blank">register for mtcaptcha.</a>';
    }

    public function display_mt_captcha_theme()
    {
        function getThemes(){
            $mt_captcha_theme = filter_var(get_option("mt_captcha_theme"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $theme_options = "";
            $themes = ["standard", "overcast", "neowhite", "goldbezel", "blackmoon", "darkruby", "touchoforange", "caribbean", "woodyallen", "chrome", "highcontrast"];
            foreach( $themes as $theme ) {
                if($theme === $mt_captcha_theme){
                    $theme_options = $theme_options."<option selected value=\"{$theme}\">{$theme}</option>";
                }else{
                    $theme_options = $theme_options."<option value=\"{$theme}\">{$theme}</option>";
                }
            }
            return $theme_options;
        }
        echo "<select name=\"mt_captcha_theme\" id=\"mt_captcha_theme\"> ".getThemes()."</select>";
    }

    public function display_mt_captcha_lang()
    {
        function getLanguage(){
            $mt_captcha_lang = filter_var(get_option("mt_captcha_lang"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $lang_options = "";
            $languages = array(
                "en" => "English(en)",
                "ar" => "Arabic(ar)",
                "af" => "Afrikaans(af)",
                "am" => "Amharic(am)",
                "hy" => "Armenian(hy)",
                "az" => "Azerbaijani(az)",
                "eu" => "Basque(eu)",
                "bn" => "Bengali(bn)",
                "bg" => "Bulgarian(bg)",
                "ca" => "Catalan(ca)",
                "zh-hk" => "Chinese (Hong Kong)(zh-HK)",
                "zh" => "Chinese(zh)",
                "hr" => "Croatian(hr)",
                "cs" => "Czech(cs)",
                "da" => "Danish(da)",
                "nl" => "Dutch(nl)",
                "en" => "English",
                "et" => "Estonian(et)",
                "fil" => "Filipino(fil)",
                "fi" => "Finnish(fi)",
                "fr" => "French(fr)",
                "gl" => "Galician(gl)",
                "ka" => "Georgian(ka)",
                "de" => "German(de)",
                "el" => "Greek(el)",
                "gu" => "Gujarati(gu)",
                "iw" => "Hebrew(iw)",
                "hi" => "Hindi(hi)",
                "hu" => "Hungarain(hu)",
                "is" => "Icelandic(is)",
                "id" => "Indonesian(id)",
                "it" => "Italian(it)",
                "ja" => "Japanese(ja)",
                "kn" => "Kannada(kn)",
                "ko" => "Korean(ko)",
                "ko" => "Korean(ko)",
                "lv" => "Latvian(lv)",
                "lt" => "Lithuanian(lt)",
                "ms" => "Malay(ms)",
                "ml" => "Malayalam(ml)",
                "mr" => "Marathi(mr)",
                "mn" => "Mongolian(mn)",
                "no" => "Norwegian(no)",
                "fa" => "Persian(fa)",
                "pl" => "Polish(pl)",
                "pt" => "Portuguese(pt)",
                "ro" => "Romanian(ro)",
                "ru" => "Russian(ru)",
                "si" => "Sinhalese(si)",
                "sr" => "Serbian(sr)",
                "sk" => "Slovak(sk)",
                "sl" => "Slovenian(sl)",
                "es" => "Spanish(es)",
                "sw" => "Swahili(sw)",
                "sv" => "Swedish(sv)",
                "ta" => "Tamil(ta)",
                "te" => "Telugu(te)",
                "th" => "Thai(th)",
                "tr" => "Turkish(tr)",
                "uk" => "Ukrainian(uk)",
                "ur" => "Urdu(ur)",
                "vi" => "Vietnamese(vi)",
                "zu" => "Zulu(zu)"
            );
            foreach( $languages as $langCode => $lang ) {
                if($langCode === $mt_captcha_lang){
                    $lang_options = $lang_options."<option selected value=\"{$langCode}\">{$lang}</option>";
                }else{
                    $lang_options = $lang_options."<option value=\"{$langCode}\">{$lang}</option>";
                }
            }
            return $lang_options;
        }
        echo "<select name=\"mt_captcha_lang\" id=\"mt_captcha_lang\"> ".getLanguage()."</select>";
    }

    public function display_mt_captcha_widget_size()
    {
        function getWidgetSize(){
            $mt_captcha_widget_size = filter_var(get_option("mt_captcha_widget_size"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $widget_size_options = "";
            $widget_sizes = array(
                "standard" => "standard",
                "mini" => "modern mini" );
            foreach( $widget_sizes as $captcha_widget_size => $widget_size ) {
                if($captcha_widget_size === $mt_captcha_widget_size){
                    $widget_size_options = $widget_size_options."<option selected value=\"{$captcha_widget_size}\">{$widget_size}</option>";
                }else{
                    $widget_size_options = $widget_size_options."<option value=\"{$captcha_widget_size}\">{$widget_size}</option>";
                }
            }
            return $widget_size_options;
        }
        echo "<select name=\"mt_captcha_widget_size\" id=\"mt_captcha_widget_size\"> ".getWidgetSize()."</select>";  
    }

    public function display_mt_captcha_enable_json_value()
    {
        $captcha_enable_json_status = (!empty(get_option("mt_captcha_enable_json_value"))) ? get_option("mt_captcha_enable_json_value") : false;
        $show_captcha_enable_json = array(
            'enable_json_value'    => __('Yes', 'mtcaptcha' ),
        );
        if($captcha_enable_json_status == TRUE) {        
            $html = "<input type=\"checkbox\" name=\"mt_captcha_enable_json_value\" id=\"mt_captcha_enable_json_value_check_disable\" onclick=\"custom_captcha_enable_or_disable()\" value=\"{$show_captcha_enable_json['enable_json_value']}\" ".checked(true, true , false)."/>";
        } else {
            $html = "<input type=\"checkbox\" name=\"mt_captcha_enable_json_value\" id=\"mt_captcha_enable_json_value_check_disable\" onclick=\"custom_captcha_enable_or_disable()\" value=\"{$show_captcha_enable_json['enable_json_value']}\" ".checked(true, false , false)."/>";
        }
        $html .= "<label for=\"mt_captcha_enable_json_value_check_disable\">".$show_captcha_enable_json['enable_json_value']."</label></br>";
        echo $html;
        echo'
        Provides the custom configuration to render 
        the MTCaptcha in your forms.<br/><br/>
    
        1. You have to <a href="https://www.mtcaptcha.com/pricing/" 
        target="blank" rel="external">register your domain</a> 
        and get your required keys.<br/>
        2. Visit <a href="http://service.mtcaptcha.com/mtcv1/demo/" 
        target="blank" rel="external">MTCaptcha demo page</a> 
        to customize the MTCaptcha configuration.<br/> 
        3. Under Basic Options, Provide your site key in the Sitekey field. <br/>
        4. Choose the <b>Render Type as "explicit" </b><br/>
        5. Choose the <b>Auto Form validate as "True" </b><br/>
        6. Customize the <b>Basic Options</b>, 
        <b>Custom Style</b> and <b>Custom Language</b>.<br/>
        7. Click on Apply button to view the changes. <br/>
        8. If the changes are looks good, 
            then copy the snippet located inside the <b>script</b>
            tag under <b>Embed Snippet</b> tab. <br/>
        9. Paste the copied snippet to the below textbox. <br/>';
    }


    public function display_mt_captcha_json_value() {
        $mt_captcha_json_value = filter_var(get_option("mt_captcha_json_value"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        echo "<textarea 
                 name='mt_captcha_json_value' 
                 class='regular-text' 
                 id='mt_captcha_json_value'
                 rows='10'
                 cols='50'            
                 placeholder = 'var mtcaptchaConfig = {
                     \"sitekey\": \"YOUR SITE KEY\",
                     \"widgetSize\": \"mini\",
                     \"lang\": \"en\",
                     \"autoFormValidate\": true,
                     \"loadAnimation\": true,
                     \"render\": \"explicit\",
                     \"renderQueue\":[]
                    };
                 (function(){var mt_service = document.createElement(\"script\");
                 mt_service.async = true;
                 mt_service.src=\"https://service.mtcaptcha.com/mtcv1/client/mtcaptcha.min.js\";
                 (document.getElementsByTagName(\"head\")[0] || 
                   document.getElementsByTagName(\"body\")[0]).appendChild(mt_service);
                  var mt_service2 = document.createElement(\"script\");
                  mt_service2.async = true;
                 mt_service2.src=\"https://service2.mtcaptcha.com/mtcv1/client/mtcaptcha2.min.js\";
                  (document.getElementsByTagName(\"head\")[0] ||
                 document.getElementsByTagName(\"body\")[0]).appendChild(mt_service2);}) ();'
                 >"
                 .$mt_captcha_json_value.
             '</textarea>';   
        ?> <script>custom_captcha_enable_or_disable(); </script> <?php
     
     }

    public function display_options()
    {
        add_settings_section("mt_captcha_header_section", __("<h2 id='mtcaptcha_head' >MTCaptcha Common Settings</h2>", "mtcaptcha"), "mt_captcha_display_content", "mt-options");         

        $fields = [
            ['id' => self::MT_OPTION_SITE_KEY, 'label' => __('Site Key', 'mtcaptcha')],
            ['id' => self::MT_OPTION_PRIVATE_KEY, 'label' => __('Private Key', 'mtcaptcha')],
            ['id' => self::MT_OPTION_ENABLE, 'label' => __('Enable MTCaptcha for', 'mtcaptcha')],
            ['id' => self::MT_OPTION_ENABLE_CAPTCHA_FOR_FORMS, 'label' => __('Enable MTCaptcha', 'mtcaptcha')],
            ['id' => self::MT_OPTION_SHOW_CAPTCHA_LABEL, 'label' => __('Show Captcha label in the form', 'mtcaptcha')],
        ];

        add_settings_section('mt_captcha_header_section', __('MTCaptcha Common Settings<p id="mt_captcha_display">You have to <a href=\"https://www.mtcaptcha.com/pricing/\" target=\"blank\" rel=\"external\">register your domain</a> first, get site & private key from MTCaptcha and save it below.</p>', 'mtcaptcha'), [], 'mt_options');
        
        foreach ($fields as $field) {
            add_settings_field($field['id'], $field['label'], [$this, sprintf('display_%s', $field['id'])], 'mt_options', 'mt_captcha_header_section');
            register_setting('mt_captcha_header_section', $field['id']);
        }

        $basicFields = [
            ['id' => self::MT_OPTION_THEME, 'label' => __('Theme', 'mtcaptcha')],
            ['id' => self::MT_OPTION_LANGUAGE, 'label' => __('Language', 'mtcaptcha')],
            ['id' => self::MT_OPTION_WIDGET_SIZE, 'label' => __('Widget Size', 'mtcaptcha')],
        ];

        add_settings_section('mt_captcha_basic_option_header_section', __('MTCaptcha Basic Options', 'mtcaptcha'), [], 'mt_options');

        foreach ($basicFields as $basicField) {
            add_settings_field($basicField['id'], $basicField['label'], [$this, sprintf('display_%s', $basicField['id'])], 'mt_options', 'mt_captcha_basic_option_header_section');
            register_setting('mt_captcha_basic_option_header_section', $basicField['id']);
        }

        $advFields = [
            ['id' => self::MT_OPTION_ENABLE_JSON_VALUE, 'label' => __('Enable Custom MTCaptcha configuration', 'mtcaptcha')],
            ['id' => self::MT_OPTION_JSON_VALUE, 'label' => __(' ', 'mtcaptcha')],
        ];

        add_settings_section('mt_captcha_adv_option_header_section', __('MTCaptcha Advanced Options', 'mtcaptcha'), [], 'mt_options');

        foreach ($advFields as $advField) {
            add_settings_field($advField['id'], $advField['label'], [$this, sprintf('display_%s', $advField['id'])], 'mt_options', 'mt_captcha_adv_option_header_section');
            register_setting('mt_captcha_adv_option_header_section', $advField['id']);
        }

        $plugin_path_styles = plugin_dir_url( __FILE__ ).'style.css';
        $plugin_path_scripts = plugin_dir_url( __FILE__ ).'common_script.js';
        wp_enqueue_style( 'mtcaptcha-styles', $plugin_path_styles , false, '1.1', 'all');  
        wp_enqueue_script('common-scripts', $plugin_path_scripts);
    }

    function mt_captcha_wc_checkout_verify($data, $errors) {
        global $message_statement;    
        if (!mt_captcha_verify()) {
            $message = $message_statement;
            $errors->add( 'validation', $message . "<br/>");
        }
    }


    function mt_captcha_common_verify($input) {    
        global $message_statement;
        if ($this->mt_captcha_verify()) {        
            return $input;
        } else {
            $errorTitle = 'MTCaptcha';
            $errorParams = ['response' => 403, 'back_link' => 1];
            $failedMsg = '<p><strong>%s:</strong> MTCaptcha %s. %s</p>';
            $error = __('Error', 'mtcaptcha');
            $verificationFailed = __('verification failed', 'mtcaptcha');
            $message = $message_statement;
            wp_die(sprintf($failedMsg, $error, $verificationFailed, $message), $errorTitle, $errorParams);    
       }
    }

    function mt_captcha_verify() {
        global $message_statement;
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mtcaptcha-verifiedtoken"])) {
            $mtcaptchaVerifiedtoken = filter_input(INPUT_POST, "mtcaptcha-verifiedtoken", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $response = wp_remote_get("https://qa-service.sadtron.com/mtcv1/api/checktoken?privatekey={$this->privateKey}&token={$mtcaptchaVerifiedtoken}");
            $response = json_decode($response["body"], 1);
            if ($response["success"]) {
                return true;
            } else {
                $this->validate_error_token($response);
                return false;
            }
        } else {
            $message = $message_statement;
            wp_die($message, "MTCaptcha", array("response" => 403, "back_link" => 1));
        }
    }

    function validate_error_token($response) {
        $error_codes = array(
            'token-expired' => ('The token has expired.'),
            'token-duplicate-cal' => ('The token has been verified already.'),
            'bad-request' => ('The request is invalid or malformed.'),
            'missing-input-privatekey' => ('`privatekey` parameter is missing'),
            'missing-input-token' => (' ‘token’ parameter is missing.'),
            'invalid-privatekey' => ('The private key is invalid or malformed.'),
            'invalid-token' => ('The token parameter is invalid or malformed.'),
            'invalid-token-faildecrypt' => ('The token parameter is invalid or malformed.'),
            'privatekey-mismatch-token' => ('The token and the privatekey does not match.'),
            'expired-sitekey-or-account' => ('The sitekey/privatekey is no longer valid due to expiration or account closure.'),
            'network-error' => ('Something went wrong!'),
            'unknown-error' => ('Something went wrong!')
        );
        foreach ($response['fail_codes'] as $code) {
            if (!isset($error_codes[$code])) {
                $code = 'unknown-error';
            }
            $message = "<p><strong>".__("ERROR:", "mtcaptcha")."</strong> ".__($error_codes[$code], "mtcaptcha")."</p>";
            global $message_statement;
            $message_statement = $message;
        }
    }

    function mt_captcha_display() {
        $randomId = "mtcaptcha-".strval(rand());
        if($this->captchaLabel){
            echo"<label >Captcha <span class='required'>*</span></label>";       
        }
        echo '<div id="'.$randomId.'"></div>';
        $this->pushTorenderQueue($randomId);
     }

    function pushTorenderQueue($randomId) {?>
        <script type="text/javascript">
                if(typeof mtcaptchaConfig != "undefined") {
                    mtcaptchaConfig.renderQueue.push(<?php echo "'$randomId'" ?>);
                }
        </script><?php
     }

    public function enqueue_main()
    {
        wp_register_script("mt_captcha_captcha_main", "");
        wp_enqueue_script("mt_captcha_captcha_main");

        if($this->jsonValue == "Yes"){
            wp_add_inline_script('mt_captcha_captcha_main',wp_specialchars_decode(html_entity_decode($this->jsonElement, ENT_QUOTES)));
        }else{
         wp_add_inline_script('mt_captcha_captcha_main','var mtcaptchaConfig ='
		.json_encode(array("sitekey"            => $this->siteKey,
							"autoFormValidate"  => (bool)true,
							"render"            => "explicit",
							"renderQueue"       => ["mtcaptcha"],
							"theme"             => $this->theme,
							"widgetSize"        => $this->widgetSize,
							"lang"              => $this->language
                            )));
            function mt_captcha_load_script(){ ?>
                <script type="text/javascript">
                    (function(){var mt_captcha_service = document.createElement('script');mt_captcha_service.async = true;mt_captcha_service.src = 'https://qa-service.sadtron.com/mtcv1/client/mtcaptcha.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_captcha_service);
                var mt_captcha_service2 = document.createElement('script');mt_captcha_service2.async = true;mt_captcha_service2.src = 'https://qa-service.sadtron.com/mtcv1/client/mtcaptcha2.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_captcha_service2);
                }) ();
                </script><?php
            }            
            add_action ( 'wp_head', 'mt_captcha_load_script' );
            add_action( 'login_head', 'mt_captcha_load_script' );
        }
        wp_enqueue_style("style", plugin_dir_url(__FILE__)."style.css?v=2.9");      
    }

    public function frontend()
    {
        $this->enqueue_main();
        $mt_display_list = [];
        $mt_captcha_verify_list = [];
        if ((!empty($this->loginFormCheckbox))
            && (($this->enableCaptcha == "logout" && !is_user_logged_in())
            || $this->enableCaptcha == "all" ||  ($this->enableCaptcha == "login" && is_user_logged_in()) ))  {
            array_push($mt_display_list, 'login_form', 'woocommerce_login_form');
            array_push($mt_captcha_verify_list, "wp_authenticate_user");
        }

        if (!empty($this->lostPasswordFormCheckbox) 
        && (($this->enableCaptcha == "logout" && !is_user_logged_in())
        || $this->enableCaptcha == "all" ||  ($this->enableCaptcha == "login" && is_user_logged_in()) ))  {
            array_push($mt_display_list, "lost_password", "lostpassword_form", "woocommerce_lostpassword_form");
            array_push($mt_captcha_verify_list, "lostpassword_post");
        }

        if (!empty($this->registrationFormCheckbox)
        && (($this->enableCaptcha == "logout" && !is_user_logged_in())
        || $this->enableCaptcha == "all" ||  ($this->enableCaptcha == "login" && is_user_logged_in()) ))  {
            array_push($mt_display_list, "register_form", "woocommerce_register_form");
            array_push($mt_captcha_verify_list, "registration_errors", "woocommerce_registration_errors");
        }
        
        if (!empty($this->commentFormCheckbox) 
        && (($this->enableCaptcha == "logout" && !is_user_logged_in())
        || $this->enableCaptcha == "all" ||  ($this->enableCaptcha == "login" && is_user_logged_in()) ))  {
            array_push($mt_display_list, "comment_form_after_fields");
            array_push($mt_captcha_verify_list, 'preprocess_comment');
        }
 
        if (!empty($this->resetPasswordFormCheckbox) 
        && (($this->enableCaptcha == "logout" && !is_user_logged_in())
        || $this->enableCaptcha == "all" ||  ($this->enableCaptcha == "login" && is_user_logged_in()) ))  {
            array_push($mt_display_list, "retrieve_password", "resetpass_form", "woocommerce_resetpassword_form");
            array_push($mt_captcha_verify_list, "resetpass_post");
        }

        if ((!empty($this->woocommerceFormCheckbox)) 
        && (($this->enableCaptcha == "logout" && !is_user_logged_in())
        || $this->enableCaptcha == "all" ||  ($this->enableCaptcha == "login" && is_user_logged_in()) ))  {
            array_push($mt_display_list, "woocommerce_after_order_notes");
            add_action("woocommerce_after_checkout_validation", array($this, "mt_captcha_wc_checkout_verify"), 10, 2 );
        }

        $mtcDisplay = 'mt_captcha_display';
        $mtcVerify = 'mt_captcha_common_verify';

        foreach ($mt_display_list as $mt_display) {
            add_action($mt_display, [$this, $mtcDisplay]);
        }

        foreach($mt_captcha_verify_list as $mt_captcha_verify) {
            add_action($mt_captcha_verify,[$this, $mtcVerify]);
        }

    }
    
    public function run()
    {
        $this->pluginName = get_file_data(__FILE__, ['Name' => 'Plugin Name'])['Name'];

        $postAction = filter_input(INPUT_POST, self::MT_ACTION, FILTER_SANITIZE_SPECIAL_CHARS);
        if ($postAction === self::UPDATE) {
            $this->updateSettings();
        }
        $checkbox_options = (!empty(get_option("mt_captcha_disable_mtcaptcha"))) ? get_option("mt_captcha_disable_mtcaptcha") : [];
        $this->privateKey = filter_var(get_option(self::MT_OPTION_PRIVATE_KEY), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->siteKey = filter_var(get_option(self::MT_OPTION_SITE_KEY), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->enableCaptcha = filter_var(get_option(self::MT_OPTION_ENABLE), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->language = filter_var(get_option(self::MT_OPTION_LANGUAGE), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->widgetSize = filter_var(get_option(self::MT_OPTION_WIDGET_SIZE), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->theme = get_option(self::MT_OPTION_THEME);
        $this->loginFormCheckbox = $checkbox_options['login'];
        $this->registrationFormCheckbox = $checkbox_options['lost_password'];
        $this->lostPasswordFormCheckbox = $checkbox_options['registration'];
        $this->resetPasswordFormCheckbox = $checkbox_options['reset_password'];
        $this->commentFormCheckbox = $checkbox_options['comment'];
        $this->woocommerceFormCheckbox = $checkbox_options['wc_checkout'];
        $this->captchaLabel = get_option(self::MT_OPTION_SHOW_CAPTCHA_LABEL);
        $this->jsonValue = get_option(self::MT_OPTION_ENABLE_JSON_VALUE);
        $this->jsonElement = get_option(self::MT_OPTION_JSON_VALUE);

        $getAction = filter_input(INPUT_GET, self::MT_ACTION, FILTER_SANITIZE_SPECIAL_CHARS);

        add_filter(sprintf('plugin_action_links_%s', plugin_basename(__FILE__)), [$this, 'action_links']);
        add_action('admin_menu', [$this, 'menu']);

        if (!is_user_logged_in() && !wp_doing_ajax()) {
            $this->frontend();
        }
    }
}

new mtcaptcha();
