<?php

if( !route(1) ){
    $route[1] = "signup";
}


$title .= "Sign Up";


if( ( route(1) == "login" || route(1) == "register") && $_SESSION["msmbilisim_userlogin"] ){
     Header("Location:".site_url());
}
if(route(1) == "neworder" || route(1) == "orders" || route(1) == "tickets" || route(1) == "addfunds" || route(1) == "account" || route(1) == "dripfeeds" || route(1) == "reference" || route(1) == "subscriptions" ) {
    Header("Location:".site_url()); exit();
}
if( $_SESSION["msmbilisim_userlogin"] == 1  || $user["client_type"] == 1 || $settings["register_page"] == 1  ){
  Header("Location:".site_url());
}

elseif( $route[1] == "signup" && $_POST ){
  foreach ($_POST as $key => $value) {
    $_SESSION["data"][$key]  = $value;
  }

  $name           = $_POST["name"];
  $email          = $_POST["email"];
  $username       = $_POST["username"];
  $phone          = $_POST["telephone"];
  $pass           = $_POST["password"];
  $pass_again     = $_POST["password_again"];
  $terms          = $_POST["terms"];
  $captcha        = $_POST['g-recaptcha-response'];
  $googlesecret   = $settings["recaptcha_secret"];
  $captcha_control= file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$googlesecret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
  $captcha_control= json_decode($captcha_control);

  if( $captcha && $settings["recaptcha"] == 2 && $captcha_control->success == false ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.recaptcha"];
  }elseif( empty($name) || strlen($name) < 5 ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.name"];
  }elseif( !email_check($email) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.email"];
  }elseif( userdata_check("email",$email) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.email.used"];
  }elseif( !username_check($username) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.usename"];
  }elseif( userdata_check("username",$username) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.username.used"];
  }elseif( empty($phone) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.telephone"];
  }elseif( userdata_check("telephone",$phone) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.telephone.used"];
  }elseif( strlen($pass) < 8 ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.password"];
  }elseif( $pass != $pass_again ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.password.notmatch"];
  }elseif( !$terms ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.terms"];
  }else{
    $apikey = CreateApiKey($_POST);
    $conn->beginTransaction();
    $insert = $conn->prepare("INSERT INTO clients SET name=:name, username=:username, email=:email, password=:pass, lang=:lang, telephone=:phone, register_date=:date, apikey=:key ");
    $insert = $insert-> execute(array("lang"=>$selectedLang,"name"=>$name,"username"=>$username,"email"=>$email,"pass"=>md5(sha1(md5($pass))),"phone"=>$phone,"date"=>date("Y.m.d H:i:s"),'key'=>$apikey ));
      if( $insert ): $client_id = $conn->lastInsertId(); endif;
    $insert2= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
    $insert2= $insert2->execute(array("c_id"=>$client_id,"action"=>"Kullanıcı kaydı yapıldı.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
      if( $insert && $insert2 ):
        $conn->commit();
        unset($_SESSION["data"]);
        $success    = 1;
        $successText= $languageArray["error.signup.success"];
        echo '<script>setInterval(function(){window.location="'.site_url('').'"},2000)</script>';
      else:
        $conn->rollBack();
        $error      = 1;
        $errorText  = $languageArray["error.signup.fail"];
      endif;
  }

}

