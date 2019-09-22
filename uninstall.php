<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'Direct access not allowed!' );
}

function mt_captcha_delete($array) {
	foreach ($array as $one) {
		delete_option("mt_captcha_{$one}");
	}	
}

mt_captcha_delete(array("mt_captcha_site_key", "mt_captcha_site_private_key", "mt_captcha_theme", "mt_captcha_lang", "mt_captcha_disable_mtcaptcha"));
