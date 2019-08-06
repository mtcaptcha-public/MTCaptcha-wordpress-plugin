<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( 'Direct access not allowed!' );
}

function mt_delete($array) {
	foreach ($array as $one) {
		delete_option("mt_{$one}");
	}	
}

mt_delete(array("mt_site_key", "mt_site_private_key", "mt_theme", "mt_lang", "disable_mtcaptcha"));
