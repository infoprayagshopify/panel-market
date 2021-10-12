<?php

$title .= $languageArray["resetpassword.title"];

if( $_SESSION["msmbilisim_userlogin"] == 1  || $user["client_type"] == 1 || $settings["resetpass_page"] == 1  ){
  Header("Location:".site_url());
}

// $resetType  = array();
// if( $settings["resetpass_sms"] == 2 ):
// $resetType[] = ["type"=>"sms","name"=>$languageArray["resetpassword.type.sms"]];
// endif;
// if( $settings["resetpass_email"] == 2 ):
// $resetType[] = ["type"=>"email","name"=>$languageArray["resetpassword.type.email"]];
// endif;

if( $_POST ):

  $captcha        = $_POST['g-recaptcha-response'];
  $googlesecret   = $settings["recaptcha_secret"];
  $captcha_control= file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$googlesecret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
  $captcha_control= json_decode($captcha_control);
  $user = $_POST["user"];
  $type = $_POST["type"];
    $row= $conn->prepare("SELECT * FROM clients WHERE username=:username");
    $row->execute(array("username"=>$user));
    if( empty($user) ):
      $error      = 1;
      $errorText  = $languageArray["error.resetpassword.user.empty"];
    elseif( !$row->rowCount() ):
      $error      = 1;
      $errorText  = $languageArray["error.resetpassword.user.notmatch"];
    elseif( $settings["recaptcha"] == 2 && $captcha_control->success == false ):
      $error      = 1;
      $errorText  = $languageArray["error.resetpassword.recaptcha"];
    else:
      $row    = $row->fetch(PDO::FETCH_ASSOC);
      $pass   = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
      $update = $conn->prepare("UPDATE clients SET password=:pass WHERE client_id=:id ");
      $update->execute(array("id"=>$row["client_id"],"pass"=>md5(sha1(md5($pass))) ));
    //   if( $type == "sms" ):
    //     $send = SMSUser($row["telephone"],"Hesabınıza ait yeni şifreniz :".$pass);
    //   endif;
    //   if( $type == "email" ):
        $msg = "Your new password is: ".$pass;
        $send = mail($row['email'],"Password Reset",$msg);
    //   endif;
      if( $send ):
        $success    = 1;
        $successText= $languageArray["error.resetpassword.success"];
        echo '<script>setInterval(function(){window.location="'.site_url('auth/login').'"},2000)</script>';
      else:
        $error      = 1;
        $errorText  = $languageArray["error.resetpassword.fail"];
      endif;

    endif;

endif;
