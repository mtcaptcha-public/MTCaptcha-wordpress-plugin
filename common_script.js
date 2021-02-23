function check_validation() {
    var enable_json = document.getElementById("mt_captcha_enable_json_value_check_disable");
    var site_Key = document.getElementById("mt_site_key");
    var prv_key = document.getElementById("mt_captcha_site_private_key");
    var custom_json_value = document.getElementById("mt_captcha_json_value");
    var get_header = document.getElementById("mtcaptcha_head");
    var get_basic_header = document.getElementById("mtcaptcha_basic_head");
    var get_advanced_header = document.getElementById("mtcaptcha_adv_head");

    function prv_site_keys(common_key, common_header) {            
        common_key.style.borderColor = "#dc3232";
        common_key.style.boxShadow = "0 0 2px rgba(204,0,0,0.8)";            
        common_key.setAttribute("title", "Please fill in this field");            
        common_key.focus();
        common_header.scrollIntoView();
    }

    if(prv_key.value === "") {
        prv_site_keys(prv_key, get_header);
        return false;
    }

    if(!(enable_json.checked) && site_Key.value.trim() === "" ) {
        prv_site_keys(site_Key, get_basic_header);           
        return false;
    } 

    if(enable_json.checked && custom_json_value.value.trim() === "" ) {
        prv_site_keys(custom_json_value, get_advanced_header);           
        return false;
    }        
    return true;
}

function custom_captcha_enable_or_disable() {
    var site_astrik = document.getElementById('site_astrik');
    site_astrik.style.color = "red";
    if (document.getElementById("mt_captcha_enable_json_value_check_disable").checked == true) {            
        document.getElementById("mt_site_key").disabled = true;
        document.getElementById("mt_captcha_theme").disabled = true;
        document.getElementById("mt_captcha_lang").disabled = true;
        document.getElementById("mt_captcha_widget_size").disabled = true;
        document.getElementById("mt_captcha_json_value").disabled = false;
        site_astrik.innerHTML = "";
    } else {            
        document.getElementById("mt_site_key").disabled = false;
        document.getElementById("mt_captcha_theme").disabled = false;
        document.getElementById("mt_captcha_lang").disabled = false;
        document.getElementById("mt_captcha_widget_size").disabled = false;
        document.getElementById("mt_captcha_json_value").disabled = true;
        site_astrik.innerHTML = "*";             
    }
}
