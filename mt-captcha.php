<?php
/*
* Plugin Name: MTCaptcha
* Description: MTCaptcha is a efficient security solution to protect your wordpress website against spam comments and brute-force attacks.  It can be integrated with the comments, login, registration, forgot password and woocommerce checkout
* Version: 1.0
* Author: MTCaptcha
* Author URI: https://www.mtcaptcha.com
* License: Apache License, Version 2.0
* Text Domain: mtcaptcha
*/

if (!defined('ABSPATH')) {
	die( 'Direct access not allowed!' );
}

function mt_add_plugin_action_links($links) {
	return array_merge(array("settings" => "<a href=\"options-general.php?page=mt-options\">".__("Settings", "mtcaptcha")."</a>"), $links);
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), "mt_add_plugin_action_links");

function mt_activation($plugin) {
	if ($plugin == plugin_basename(__FILE__) && (!get_option("mt_site_key"))) {
		exit(wp_redirect(admin_url("options-general.php?page=mt-options")));
	}
}
add_action("activated_plugin", "mt_activation");

function mt_options_page() {
	echo "<div class=\"wrap\">
	<h1>".__("MTCaptcha Options", "mtcaptcha")."</h1>
	<form method=\"post\" action=\"options.php\">";
	settings_fields("mt_header_section");
	do_settings_sections("mt-options");
	submit_button();
	echo "</form>
	</div>";
}

function mt_menu() {
	add_submenu_page("options-general.php", "MTCaptcha", "MTCaptcha", "manage_options", "mt-options", "mt_options_page");
}
add_action("admin_menu", "mt_menu");

function mt_display_content() {
	echo "<p>".__("You have to <a href=\"https://www.mtcaptcha.com/pricing/\" target=\"blank\" rel=\"external\">register your domain</a> first, get required keys from MTCaptcha and save them bellow.", "mtcaptcha")."</p>";
}

function mt_display_site_key_element() {
	$mt_site_key = filter_var(get_option("mt_site_key"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	echo "<input type=\"text\" name=\"mt_site_key\" class=\"regular-text\" id=\"mt_site_key\" value=\"{$mt_site_key}\" />";
}

function mt_display_site_private_key_element(){
	$mt_site_private_key = filter_var(get_option("mt_site_private_key"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	echo "<input type=\"text\" name=\"mt_site_private_key\" class=\"regular-text\" id=\"mt_site_private_key\" value=\"{$mt_site_private_key}\" />";
}

function mt_display_theme_element(){
	function getThemes(){
		$mt_theme = filter_var(get_option("mt_theme"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$themeOptions = "";
		$themes = ["standard", "overcast", "neowhite", "goldbezel", "blackmoon", "darkruby", "touchoforange", "caribbean", "woodyallen", "chrome", "highcontrast"];
		foreach( $themes as $theme ) {
			if($theme === $mt_theme){
				$themeOptions = $themeOptions."<option selected value=\"{$theme}\">{$theme}</option>";
			}else{
				$themeOptions = $themeOptions."<option value=\"{$theme}\">{$theme}</option>";
			}
		}
		return $themeOptions;
	}
	echo "<select name=\"mt_theme\" id=\"mt_theme\"> ".getThemes()."</select>";
}

function mt_display_language_element(){
	function getLanguage(){
		$mt_lang = filter_var(get_option("mt_lang"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$langOptions = "";
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
			if($langCode === $mt_lang){
				$langOptions = $langOptions."<option selected value=\"{$langCode}\">{$lang}</option>";
			}else{
				$langOptions = $langOptions."<option value=\"{$langCode}\">{$lang}</option>";
			}
		}
		return $langOptions;
	}
	echo "<select name=\"mt_lang\" id=\"mt_lang\"> ".getLanguage()."</select>";
}

function mt_display_login_check_disable() {
	$checkboxOptions = (!empty(get_option("disable_mtcaptcha"))) ? get_option("disable_mtcaptcha") : [];
	$wp_forms = array(
		'login'          => __( 'Login Form', 'mtcaptcha' ),
		'registration'   => __( 'Registration Form', 'mtcaptcha' ),
		'lost_password'  => __( 'Lost Password Form', 'mtcaptcha' ),
		'reset_password' => __( 'Reset Password Form', 'mtcaptcha' ),
		'comment'        => __( 'Comment Form', 'mtcaptcha' ),
		'wc_checkout'    => __( 'WooCommerce Checkout', 'mtcaptcha' )
	);
	foreach( $wp_forms as $formId => $formName ) {
		$html = "<input type=\"checkbox\" name=\"disable_mtcaptcha[$formId]\" id=\"mt_{$formId}_check_disable\" value=\"{$formId}\" ".checked($formId, $checkboxOptions[$formId], false)."/>";
		$html .= "<label for=\"mt_{$formId}_check_disable\">".$formName."</label></br>";
		echo $html;
	}
}

function mt_display_options() {
	add_settings_section("mt_header_section", __("Settings", "mtcaptcha"), "mt_display_content", "mt-options");

	add_settings_field("mt_site_key", __("Site Key", "mtcaptcha"), "mt_display_site_key_element", "mt-options", "mt_header_section");
	add_settings_field("mt_site_private_key", __("Private Key", "mtcaptcha"), "mt_display_site_private_key_element", "mt-options", "mt_header_section");
	add_settings_field("mt_theme", __("Theme", "mtcaptcha"), "mt_display_theme_element", "mt-options", "mt_header_section");
	add_settings_field("mt_lang", __("Language", "mtcaptcha"), "mt_display_language_element", "mt-options", "mt_header_section");
	add_settings_field("disable_mtcaptcha", __("Disable MTCaptcha", "mtcaptcha"), "mt_display_login_check_disable", "mt-options", "mt_header_section");

	register_setting("mt_header_section", "mt_site_key");
	register_setting("mt_header_section", "mt_site_private_key");
	register_setting("mt_header_section", "mt_theme");
	register_setting("mt_header_section", "mt_lang");
	register_setting("mt_header_section", "disable_mtcaptcha");
}
add_action("admin_init", "mt_display_options");

function load_language_mt() {
	load_plugin_textdomain("mtcaptcha", false, dirname(plugin_basename(__FILE__))."/languages/");
}
add_action("plugins_loaded", "load_language_mt");

function frontend_mt_script() {
	$mt_site_key = filter_var(get_option("mt_site_key"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$mt_theme = filter_var(get_option("mt_theme"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$mt_lang = filter_var(get_option("mt_lang"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	$mt_display_list = array();
	$checkboxOptions = (!empty(get_option("disable_mtcaptcha"))) ? get_option("disable_mtcaptcha") : [];
	
	if (!$checkboxOptions['login']) {
		array_push($mt_display_list, "login_form", "woocommerce_login_form");
	}

	if (!$checkboxOptions['registration']) {
		array_push($mt_display_list, "register_form", "woocommerce_register_form");
	}

	if (!$checkboxOptions['comment']) {
		array_push($mt_display_list, "comment_form_after_fields");
	}

	if (!$checkboxOptions['lost_password']) {
		array_push($mt_display_list, "lost_password", "lostpassword_form", "woocommerce_lostpassword_form");
	}

	if (!$checkboxOptions['reset_password']) {
		array_push($mt_display_list, "retrieve_password", "resetpass_form", "woocommerce_resetpassword_form");
	}

	if (!$checkboxOptions['wc_checkout']) {
		array_push($mt_display_list, "woocommerce_after_order_notes");
	}
	
	foreach($mt_display_list as $mt_display) {
		add_action($mt_display, "mt_display");
	}
	
	wp_register_script("mt_captcha_main", "");
	wp_enqueue_script("mt_captcha_main");

	wp_add_inline_script('mt_captcha_main','var mtcaptchaConfig ='
		.json_encode(array("sitekey" 			=> $mt_site_key, 
							"autoFormValidate"	=> (bool)true,
							"theme"				=> $mt_theme,
							"lang"				=> $mt_lang
						)));


	function mt_load_script(){ ?>
		<script type="text/javascript">
		  (function(){var mt_service = document.createElement('script');mt_service.async = true;mt_service.src = 'https://service.mtcaptcha.com/mtcv1/client/mtcaptcha.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_service);
		var mt_service2 = document.createElement('script');mt_service2.async = true;mt_service2.src = 'https://service2.mtcaptcha.com/mtcv1/client/mtcaptcha2.min.js';(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mt_service2);}) ();
		</script><?php
	}
	add_action ( 'wp_head', 'mt_load_script' );
	add_action( 'login_head', 'mt_load_script' );
		
	wp_enqueue_style("style", plugin_dir_url(__FILE__)."style.css?v=2.9");
}

function mt_display() {
	echo "<div class=\"mtcaptcha\"></div>";
}

function mt_verify() {
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mtcaptcha-verifiedtoken"])) {
		$mt_site_private_key = filter_var(get_option("mt_site_private_key"), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$mtcaptchaVerifiedtoken = filter_input(INPUT_POST, "mtcaptcha-verifiedtoken", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		$response = wp_remote_get("https://service.mtcaptcha.com/mtcv1/api/checktoken?privatekey={$mt_site_private_key}&token={$mtcaptchaVerifiedtoken}");
		$response = json_decode($response["body"], 1);
		if ($response["success"]) {
			return true;
		} else {
			return false;
		}
	} else {
		wp_die($message, "MTCaptcha", array("response" => 403, "back_link" => 1));
	}
}

function mt_common_verify($input){
	if (mt_verify()) {
		return $input;
	} else {
		$message = "<p><strong>".__("ERROR:", "mtcaptcha")."</strong> ".__("MTCaptcha verification failed.", "mtcaptcha")."</p>";
		return new WP_Error("mtcaptcha", $message);
	}
}

function mt_wc_checkout_verify($data, $errors) {
	if (!mt_verify()) {
		$message = "<p><strong>".__("ERROR:", "mtcaptcha")."</strong> ".__("MTCaptcha verification failed.", "mtcaptcha")."</p>";
		$errors->add( 'validation', $message );
	}
}

function mt_check() {
	if (get_option("mt_site_key") && get_option("mt_site_private_key") && !is_user_logged_in() ) {
		add_action("login_enqueue_scripts", "frontend_mt_script");
		add_action("wp_enqueue_scripts", "frontend_mt_script");
		add_action('admin_enqueue_scripts',	'frontend_mt_script' );
		
		$mt_verify_list = array();
		
		$checkboxOptions = (!empty(get_option("disable_mtcaptcha"))) ? get_option("disable_mtcaptcha") : [];

		if (!$checkboxOptions['login']) {
			array_push($mt_verify_list, "wp_authenticate_user");
		}

		if (!$checkboxOptions['registration']) {
			array_push($mt_verify_list, "registration_errors", "woocommerce_registration_errors");
		}

		if (!$checkboxOptions['comment']) {
			array_push($mt_verify_list, "comment", "preprocess_comment");
		}

		if (!$checkboxOptions['lost_password']) {
			array_push($mt_verify_list, "lostpassword_post");
		}

		if (!$checkboxOptions['reset_password']) {
			array_push($mt_verify_list, "resetpass_post");
		}

		if (!$checkboxOptions['wc_checkout']) {
			add_action("woocommerce_after_checkout_validation", "mt_wc_checkout_verify", 10, 2 );
		}
		
		foreach($mt_verify_list as $mt_verify) {
			add_action($mt_verify, "mt_common_verify");
		}
	}
}

add_action("init", "mt_check");
