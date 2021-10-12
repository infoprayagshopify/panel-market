<?php

if( !route(1) ){
    $route[1] = "login";
}

if( route(1) == "login" ){
    $title .= $settings["site_seo"];
}elseif( route(1) == "register" ){
    $title .= $languageArray["signup.title"];
}

if( ( route(1) == "login" || route(1) == "register") && $_SESSION["msmbilisim_userlogin"] ){
     Header("Location:".site_url());
}
if(route(1) == "neworder" || route(1) == "orders" || route(1) == "tickets" || route(1) == "addfunds" || route(1) == "account" || route(1) == "dripfeeds" || route(1) == "reference" || route(1) == "subscriptions" ) {
    Header("Location:".site_url()); exit();
}


if( $route[1] == "login" && $_POST ){

    $username       = $_POST["username"];
    $pass           = $_POST["password"];
    $captcha        = $_POST['g-recaptcha-response'];
    $remember       = $_POST["remember"];
    $googlesecret   = $settings["recaptcha_secret"];
    $captcha_control= file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$googlesecret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
    $captcha_control= json_decode($captcha_control);

    if( $settings["recaptcha"] == 2 && $captcha_control->success == false && $_SESSION["recaptcha"]  ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.recaptcha"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( !userdata_check("username",$username) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.username"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( !userlogin_check($username,$pass) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.notmatch"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( countRow(["table"=>"clients","where"=>["username"=>$username,"client_type"=>1]]) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.deactive"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }else{
        $row    = $conn->prepare("SELECT * FROM clients WHERE username=:username && password=:password ");
        $row  -> execute(array("username"=>$username,"password"=>md5(sha1(md5($pass))) ));
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $access = json_decode($row["access"],true);

        $_SESSION["msmbilisim_userlogin"]      = 1;
        $_SESSION["msmbilisim_userid"]         = $row["client_id"];
        $_SESSION["msmbilisim_userpass"]       = md5(sha1(md5($pass)));
        $_SESSION["recaptcha"]                = false;
        if( $access["admin_access"] ):
            $_SESSION["msmbilisim_adminlogin"] = 1;
        endif;
        if( $remember ){
            if($access["admin_access"]):
                setcookie("a_login", 'ok', strtotime('+7 days'), '/', null, null, true);
            endif;
            setcookie("u_id", $row["client_id"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_password", $row["password"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_login", 'ok', strtotime('+7 days'), '/', null, null, true);
        }else{
            setcookie("u_id", $row["client_id"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_password", $row["password"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_login", 'ok', strtotime('+7 days'), '/', null, null, true );
        }
        
        header('Location:'.site_url(''));
        $insert = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
        $insert->execute(array("c_id"=>$row["client_id"],"action"=>"Üye girişi yapıldı.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
        $update = $conn->prepare("UPDATE clients SET login_date=:date, login_ip=:ip WHERE client_id=:c_id ");
        $update->execute(array("c_id"=>$row["client_id"],"date"=>date("Y.m.d H:i:s"),"ip"=>GetIP() ));
    }


}
