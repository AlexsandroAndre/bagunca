<?php
function conversion_le_key($key){
    for($i=0; $i<6; $i++){
        $key = base64_decode($key);

    }
    $key = strrev($key);
    
    return $key;
}

function  conversion_verifica_dominio($key) {
    $dominio = get_bloginfo('url');
    $resp = conversion_le_key($key);
    if (strpos($resp, '|') > 0){
        $resp = explode('|', $resp);
    }
    else {
        $resp[0] = '------------';
    }

    if (strpos($dominio, $resp[0]) > 0){
        return 1;
    }
    else{
        return 0;
    }

}