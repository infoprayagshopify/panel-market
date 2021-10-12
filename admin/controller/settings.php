<?php

  if( !route(2) ):
    $route[2]   = "general";
  endif;

  if( $_SESSION["client"]["data"] ):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
      $$key = $value;
    }
    unset($_SESSION["client"]);
  endif;

  $menuList = ["General Settings"=>"general","Meta (SEO) Settings"=>"meta","Page Posts"=>"pages","Payment settings"=>"payment-methods","Bank Accounts"=>"bank-accounts","Payment bonuses"=>"payment-bonuses","Notification settings"=>"alert","Service providers"=>"providers","Language editor"=>"language","Theme editor"=>"themes","Child Panels"=>"child-panels"];

  if( !array_search(route(2),$menuList) ):
    header("Location:".site_url("admin/settings"));
  elseif( route(2) == "general" ):
    $access = $user["access"]["general_settings"];
      if( $access ):
        if( $_POST ):
         foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          if ( $_FILES["logo"] && ( $_FILES["logo"]["type"] == "image/jpeg" || $_FILES["logo"]["type"] == "image/jpg" || $_FILES["logo"]["type"] == "image/png" || $_FILES["logo"]["type"] == "image/gif"  ) ):
            $logo_name      = $_FILES["logo"]["name"];
            $uzanti         = substr($logo_name,-4,4);
            $logo_newname   = "public/images/".md5(rand(10,999)).".png";
            $upload_logo    = move_uploaded_file($_FILES["logo"]["tmp_name"],$logo_newname);
          elseif( $settings["site_logo"] != "" ):
            $logo_newname   = $settings["site_logo"];
          else:
            $logo_newname   = "";
          endif;
          if ( $_FILES["favicon"] && ( $_FILES["favicon"]["type"] == "image/jpeg" || $_FILES["favicon"]["type"] == "image/jpg" || $_FILES["favicon"]["type"] == "image/png" || $_FILES["favicon"]["type"] == "image/gif"  ) ):
            $favicon_name   = $_FILES["favicon"]["name"];
            $uzanti         = substr($logo_name,-4,4);
            $fv_newname     = "public/images/".sha1(rand(10,999)).".png";
            $upload_logo    = move_uploaded_file($_FILES["favicon"]["tmp_name"],$fv_newname);
          elseif( $settings["favicon"] != "" ):
            $fv_newname     = $settings["favicon"];
          else:
            $fv_newname     = "";
          endif;
          if( empty($name) ):
            $errorText  = "Panel adı boş olamaz";
            $error      = 1;
          else:
            $update = $conn->prepare("UPDATE settings SET 
			site_maintenance=:site_maintenance,
			resetpass_page=:resetpass_page,
			resetpass_sms=:resetpass_sms,
			resetpass_email=:resetpass_email,
			site_name=:name,
			site_logo=:logo,
			site_theme_alt=:site_theme_alt,
			favicon=:fv,
            site_currency=:site_currency,
			recaptcha=:recaptcha,
			recaptcha_key=:recaptcha_key, 
			recaptcha_secret=:recaptcha_secret, 
			dolar_charge=:dolar, 
			euro_charge=:euro, 
			ticket_system=:ticket_system, 
			register_page=:registration_page, 
			service_list=:service_list, 
			service_speed=:service_speed, 
			custom_header=:custom_header, 
			custom_footer=:custom_footer,
			bronz_statu=:bronz_statu,
			silver_statu=:silver_statu,
			gold_statu=:gold_statu,
			bayi_statu=:bayi_statu WHERE id=:id ");
            $update->execute(array(
                "id" => 1,
                "site_maintenance" => $site_maintenance,
                "resetpass_page" => $resetpass, 
                "resetpass_sms" => $resetsms,
                "resetpass_email" => $resetmail,
                "name" => $name,
                "logo" => $logo_newname,
                "fv" => $fv_newname,
                "site_theme_alt" => $site_theme_alt,
                "recaptcha" => $recaptcha,
                "recaptcha_secret" => $recaptcha_secret,
                "recaptcha_key" => $recaptcha_key,
                "dolar" => $_POST['dolar'],
                "euro" => $_POST['euro'],
                "ticket_system" => $ticket_system,
                "registration_page" => $registration_page,
                "service_list" => $service_list,
                "service_speed" => $service_speed,
                "custom_footer" => $custom_footer,
                "custom_header" => $custom_header,
                "bronz_statu" => $bronz_statu,
                "silver_statu" => $silver_statu,
                "gold_statu" => $gold_statu,
                "bayi_statu" => $bayi_statu, 
                "site_currency" => $site_currency));

                $referrer = site_url("admin/settings/general");
                $icon = "success";
                $error = 1;
                $errorText = "Success";
                
                header("Location:".site_url("admin/settings/general"));
                echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);

              if( $update ):
                header("Location:" . site_url("admin/settings/general"));
                    $_SESSION["client"]["data"]["success"] = 1;
                    $_SESSION["client"]["data"]["successText"] = "Successful";
              else:
                $errorText  = "Failed";
                $error      = 1;
				
              endif;
				
				
          endif;
        endif;
        if( route(3) == "delete-logo" ):
          $update = $conn->prepare("UPDATE settings SET site_logo=:type WHERE id=:id ");
          $update->execute(array("type"=>"","id"=>1));
          if ( $update ):
            unlink($settings["site_logo"]);
          endif;
          header("Location:".site_url("admin/settings/general"));
        elseif( route(3) == "delete-favicon" ):
          $update = $conn->prepare("UPDATE settings SET favicon=:type WHERE id=:id ");
          $update->execute(array("type"=>"","id"=>1));
          if ( $update ):
            unlink($settings["site_favicon"]);
          endif;
          header("Location:".site_url("admin/settings/general"));
        endif;
      endif;
  elseif( route(2) == "pages" ):
    $access = $user["access"]["pages"];
      if( $access ):
        if( route(3) == "edit" ):
          if( $_POST ):
            $id = route(4);
            foreach ($_POST as $key => $value) {
              $$key = $value;
            }
              if( $content == "<br>" ): $content = ""; endif;
            if( !countRow(["table"=>"pages","where"=>["page_get"=>$id]]) ):
              $error    = 1;
              $icon     = "error";
              $errorText= "Lütfen geçerli ödeme methodu seçin";
            else:
              $update = $conn->prepare("UPDATE pages SET page_content=:content WHERE page_get=:id ");
              $update->execute(array("id"=>$id,"content"=>$content ));
                if( $update ):
                  $success    = 1;
                  $successText= "Success";
                else:
                  $error    = 1;
                  $errorText= "Failed";
                endif;
            endif;
          endif;
          $page = $conn->prepare("SELECT * FROM pages WHERE page_get=:get ");
          $page->execute(array("get"=>route(4)));
          $page = $page->fetch(PDO::FETCH_ASSOC); if( !$page ): header("Location:".site_url("admin/settings/pages")); endif;
        elseif( !route(3) ):
          $pageList = $conn->prepare("SELECT * FROM pages ");
          $pageList->execute(array());
          $pageList = $pageList->fetchAll(PDO::FETCH_ASSOC);
        else:
          header("Location:".site_url("admin/settings/pages"));
        endif;
      endif;
    if( route(5) ): header("Location:".site_url("admin/settings/pages")); endif;
  elseif( route(2) == "payment-methods" ):
    $access = $user["access"]["payments_settings"];
      if( $access ):
        if( route(3) == "edit" && $_POST  ):
          $id = route(4);
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          if( !countRow(["table"=>"payment_methods","where"=>["method_get"=>$id]]) ):
            $error    = 1;
            $icon     = "error";
            $errorText= "Lütfen geçerli ödeme methodu seçin";
          else:
            $update = $conn->prepare("UPDATE payment_methods SET method_min=:min, method_max=:max, method_type=:type, method_extras=:extras WHERE method_get=:id ");
            $update->execute(array("id"=>$id,"min"=>$min,"max"=>$max,"type"=>$method_type,"extras"=>json_encode($_POST) ));
              if( $update ):
                $error    = 1;
                $icon     = "success";
                $errorText= "Success";
              else:
                $error    = 1;
                $icon     = "error";
                $errorText= "Failed";
              endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon]);
          exit();
        elseif( route(3) == "type" ):
          $id     = $_GET["id"];
          $type   = $_GET["type"]; if( $type == "off" ): $type = 1; elseif( $type == "on" ): $type = 2; endif;
          $update = $conn->prepare("UPDATE payment_methods SET method_type=:type WHERE id=:id ");
          $update->execute(array("id"=>$id,"type"=>$type));
            if( $update ):
              echo "1";
            else:
              echo "0";
            endif;
          exit();
        endif;
        $methodList = $conn->prepare("SELECT * FROM payment_methods ORDER BY method_line ");
        $methodList->execute(array());
        $methodList = $methodList->fetchAll(PDO::FETCH_ASSOC);
      endif;
    if( route(3) ): header("Location:".site_url("admin/settings/payment-methods")); endif;
  elseif( route(2) == "bank-accounts" ):
    $access = $user["access"]["bank_accounts"];
      if( $access ):
        if( route(3) == "new" && $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          if( empty($bank_name) ):
            $error    = 1;
            $errorText= "Banka adı boş olamaz";
            $icon     = "error";
          elseif( empty($bank_alici) ):
            $error    = 1;
            $errorText= "Alıcı boş olamaz";
            $icon     = "error";
          elseif( empty($bank_sube) ):
            $error    = 1;
            $errorText= "Şube no boş olamaz";
            $icon     = "error";
          elseif( empty($bank_hesap) ):
            $error    = 1;
            $errorText= "Hesap no boş olamaz";
            $icon     = "error";
          elseif( empty($bank_iban) ):
            $error    = 1;
            $errorText= "IBAN boş olamaz";
            $icon     = "error";
          else:
            $conn->beginTransaction();
            $insert = $conn->prepare("INSERT INTO bank_accounts SET bank_name=:name, bank_sube=:sube, bank_hesap=:hesap, bank_iban=:iban, bank_alici=:alici ");
            $insert = $insert->execute(array("name"=>$bank_name,"sube"=>$bank_sube,"hesap"=>$bank_hesap,"iban"=>$bank_iban,"alici"=>$bank_alici ));
            if( $insert ):
              $conn->commit();
              $referrer = site_url("admin/settings/bank-accounts");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);
          exit();
        elseif( route(3) == "edit" ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $id = route(4);
          if( empty($bank_name) ):
            $error    = 1;
            $errorText= "Banka adı boş olamaz";
            $icon     = "error";
          elseif( empty($bank_alici) ):
            $error    = 1;
            $errorText= "Alıcı boş olamaz";
            $icon     = "error";
          elseif( empty($bank_sube) ):
            $error    = 1;
            $errorText= "Şube no boş olamaz";
            $icon     = "error";
          elseif( empty($bank_hesap) ):
            $error    = 1;
            $errorText= "Hesap no boş olamaz";
            $icon     = "error";
          elseif( empty($bank_iban) ):
            $error    = 1;
            $errorText= "IBAN boş olamaz";
            $icon     = "error";
          else:
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE bank_accounts SET bank_name=:name, bank_sube=:sube, bank_hesap=:hesap, bank_iban=:iban, bank_alici=:alici WHERE id=:id ");
            $update = $update->execute(array("name"=>$bank_name,"sube"=>$bank_sube,"hesap"=>$bank_hesap,"iban"=>$bank_iban,"alici"=>$bank_alici,"id"=>$id ));
            if( $update ):
              $conn->commit();
              $referrer = site_url("admin/settings/bank-accounts");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);
          exit();
        elseif( route(3) == "delete" ):
          $id = route(4);
            if( !countRow(["table"=>"bank_accounts","where"=>["id"=>$id]]) ):
              $error    = 1;
              $icon     = "error";
              $errorText= "Lütfen geçerli ödeme bonusu seçin";
            else:
              $delete = $conn->prepare("DELETE FROM bank_accounts WHERE id=:id ");
              $delete->execute(array("id"=>$id));
                if( $delete ):
                  $error    = 1;
                  $icon     = "success";
                  $errorText= "Success";
                  $referrer = site_url("admin/settings/bank-accounts");
                else:
                  $error    = 1;
                  $icon     = "error";
                  $errorText= "Failed";
                endif;
            endif;
            echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>0]);
            exit();
        elseif( !route(3) ):
          $bankList = $conn->prepare("SELECT * FROM bank_accounts ");
          $bankList->execute(array());
          $bankList = $bankList->fetchAll(PDO::FETCH_ASSOC);
        else:
          header("Location:".site_url("admin/settings/bank-accounts"));
        endif;
      endif;
      if( route(5) ): header("Location:".site_url("admin/settings/bank-accounts")); endif;
  elseif( route(2) == "payment-bonuses" ):
    $access = $user["access"]["payments_bonus"];
      if( $access ):
        if( route(3) == "new" && $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          if( empty($method_type) ):
            $error    = 1;
            $errorText= "Method boş olamaz";
            $icon     = "error";
          elseif( empty($amount) ):
            $error    = 1;
            $errorText= "Bonus tutarı boş olamaz";
            $icon     = "error";
          elseif( empty($from) ):
            $error    = 1;
            $errorText= "İtibaren olamaz";
            $icon     = "error";
          else:
            $conn->beginTransaction();
            $insert = $conn->prepare("INSERT INTO payments_bonus SET bonus_method=:method, bonus_from=:from, bonus_amount=:amount, bonus_type=:type ");
            $insert = $insert->execute(array("method"=>$method_type,"from"=>$from,"amount"=>$amount,"type"=>2 ));
            if( $insert ):
              $conn->commit();
              $referrer = site_url("admin/settings/payment-bonuses");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);
          exit();
        elseif( route(3) == "edit" && $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $id = route(4);
          if( empty($method_type) ):
            $error    = 1;
            $errorText= "Method boş olamaz";
            $icon     = "error";
          elseif( empty($amount) ):
            $error    = 1;
            $errorText= "Bonus tutarı boş olamaz";
            $icon     = "error";
          elseif( empty($from) ):
            $error    = 1;
            $errorText= "İtibaren olamaz";
            $icon     = "error";
          else:
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE payments_bonus SET bonus_method=:method, bonus_from=:from, bonus_amount=:amount WHERE bonus_id=:id ");
            $update = $update->execute(array("method"=>$method_type,"from"=>$from,"amount"=>$amount,"id"=>$id ));
            if( $update ):
              $conn->commit();
              $referrer = site_url("admin/settings/payment-bonuses");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);
          exit();
        elseif( route(3) == "delete" ):
          $id = route(4);
            if( !countRow(["table"=>"payments_bonus","where"=>["bonus_id"=>$id]]) ):
              $error    = 1;
              $icon     = "error";
              $errorText= "Lütfen geçerli ödeme bonusu seçin";
            else:
              $delete = $conn->prepare("DELETE FROM payments_bonus WHERE bonus_id=:id ");
              $delete->execute(array("id"=>$id));
                if( $delete ):
                  $error    = 1;
                  $icon     = "success";
                  $errorText= "Success";
                  $referrer = site_url("admin/settings/payment-bonuses");
                else:
                  $error    = 1;
                  $icon     = "error";
                  $errorText= "Failed";
                endif;
            endif;
            echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>0]);
            exit();
        elseif( !route(3) ):
          $bonusList = $conn->prepare("SELECT * FROM payments_bonus INNER JOIN payment_methods WHERE payment_methods.id = payments_bonus.bonus_method ORDER BY payment_methods.id DESC ");
          $bonusList->execute(array());
          $bonusList = $bonusList->fetchAll(PDO::FETCH_ASSOC);
        else:
          header("Location:".site_url("admin/settings/payment-bonuses"));
        endif;
      endif;
  elseif( route(2) == "providers" ):
    $access = $user["access"]["providers"];
      if( $access ):
        if( route(3) == "new" && $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          if( empty($name) ):
            $error    = 1;
            $errorText= "Sağlayıcı adı boş olamaz";
            $icon     = "error";
          elseif( empty($type) ):
            $error    = 1;
            $errorText= "Sağlayıcı tipi boş olamaz";
            $icon     = "error";
          elseif( empty($url) ):
            $error    = 1;
            $errorText= "Sağlayıcı API URL boş olamaz";
            $icon     = "error";
          elseif( empty($apikey) ):
            $error    = 1;
            $errorText= "Sağlayıcı API Key boş olamaz";
            $icon     = "error";
          else:
            $conn->beginTransaction();
            $insert = $conn->prepare("INSERT INTO service_api SET api_name=:name, api_key=:key, api_url=:url, api_limit=:limit, currency=:currency, api_type=:type, api_alert=:alert ");
            $insert = $insert->execute(array("name"=>$name,"key"=>$apikey,"url"=>$url,"limit"=>$limit,"currency"=>$currency,"type"=>$type,"alert"=>2 ));
            if( $insert ):
              $conn->commit();
              $referrer = site_url("admin/settings/providers");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);
          exit();
        elseif( route(3) == "edit" && $_POST  ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $id = route(4);
          if( empty($name) ):
            $error    = 1;
            $errorText= "Sağlayıcı adı boş olamaz";
            $icon     = "error";
          elseif( empty($type) ):
            $error    = 1;
            $errorText= "Sağlayıcı tipi boş olamaz";
            $icon     = "error";
          elseif( empty($url) ):
            $error    = 1;
            $errorText= "Sağlayıcı API URL boş olamaz";
            $icon     = "error";
          elseif( empty($apikey) ):
            $error    = 1;
            $errorText= "Sağlayıcı API Key boş olamaz";
            $icon     = "error";
          else:
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE service_api SET api_name=:name, api_key=:key, api_url=:url, api_limit=:limit, currency=:currency, api_type=:type WHERE id=:id ");
            $update = $update->execute(array("name"=>$name,"key"=>$apikey,"url"=>$url,"limit"=>$limit,"currency"=>$currency,"type"=>$type,"id"=>$id));
            if( $update ):
              $conn->commit();
              $referrer = site_url("admin/settings/providers");
              $error    = 1;
              $errorText= "Success";
              $icon     = "success";
            else:
              $conn->rollBack();
              $error    = 1;
              $errorText= "Failed";
              $icon     = "error";
            endif;
          endif;
          echo json_encode(["t"=>"error","m"=>$errorText,"s"=>$icon,"r"=>$referrer,"time"=>1]);
          exit();
        elseif( !route(3) ):
          $providersList = $conn->prepare("SELECT * FROM service_api ");
          $providersList->execute(array());
          $providersList = $providersList->fetchAll(PDO::FETCH_ASSOC);
		  
		 
        else:
          header("Location:".site_url("admin/settings/providers"));
        endif;
      endif;
      if( route(5) ): header("Location:".site_url("admin/settings/providers")); endif;
      
  elseif( route(2) == "alert" ):
    $access = $user["access"]["alert_settings"];
      if( $access ):
        if( $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $conn->beginTransaction();
          $update = $conn->prepare("UPDATE settings SET alert_apibalance=:alert_apibalance, alert_serviceapialert=:alert_serviceapialert, admin_mail=:mail, admin_telephone=:telephone, alert_type=:alert_type, alert_newticket=:alert_newticket, alert_newmanuelservice=:alert_newmanuelservice,alert_newbankpayment=:alert_newbankpayment, sms_provider=:sms_provider, sms_title=:sms_title, sms_user=:sms_user, sms_pass=:sms_pass, smtp_user=:smtp_user, smtp_pass=:smtp_pass, smtp_server=:smtp_server, smtp_port=:smtp_port, smtp_protocol=:smtp_protocol WHERE id=:id ");
          $update = $update->execute(array("id"=>1,"alert_apibalance"=>$alert_apibalance,"alert_serviceapialert"=>$serviceapialert,"mail"=>$admin_mail,"telephone"=>$admin_telephone,"alert_type"=>$alert_type,"alert_newticket"=>$alert_newticket,"alert_newmanuelservice"=>$alert_newmanuelservice,"alert_newbankpayment"=>$alert_newbankpayment,"sms_provider"=>$sms_provider,"sms_title"=>$sms_title,"sms_user"=>$sms_user,"sms_pass"=>$sms_pass,"smtp_user"=>$smtp_user,"smtp_pass"=>$smtp_pass,"smtp_server"=>$smtp_server,"smtp_port"=>$smtp_port,"smtp_protocol"=>$smtp_protocol));
          if( $update ):
            $conn->commit();
            header("Location:".site_url("admin/settings/alert"));
            $_SESSION["client"]["data"]["success"]    = 1;
            $_SESSION["client"]["data"]["successText"]= "Success";
          else:
            $conn->rollBack();
            $error    = 1;
            $errorText= "Failed";
          endif;
        endif;
      endif;
    if( route(3) ): header("Location:".site_url("admin/settings/alert")); endif;
  elseif( route(2) == "language" ):
    $access = $user["access"]["language"];
      if( $access ):
        $languageList = $conn->prepare("SELECT * FROM languages");
        $languageList->execute(array());
        $languageList = $languageList->fetchAll(PDO::FETCH_ASSOC);
        if( route(3) && route(3) != "new" && !countRow(["table"=>"languages","where"=>["language_code"=>route(3)]]) ):
          header("Location:".site_url("admin/settings/language"));
        elseif( route(3) == "new" ):
          include 'app/language/default.php';
        else:
          $language = $conn->prepare("SELECT * FROM languages WHERE language_code=:code");
          $language->execute(array("code"=>route(3)));
          $language = $language->fetch(PDO::FETCH_ASSOC);
          include 'app/language/'.route(3).'.php';
        endif;
        if( $_POST && route(3) != "new" && countRow(["table"=>"languages","where"=>["language_code"=>route(3)]]) ):
          $html = '<?php '.PHP_EOL.PHP_EOL;
          $html.= '$languageArray= [';
          foreach ($_POST["Language"] as $key => $value):
            $html .= ' "'.$key.'" => "'.$value.'", '.PHP_EOL;
          endforeach;
          $html .=  '];';
          file_put_contents('app/language/'.route(3).'.php', $html);
          header("Location:".site_url("admin/settings/language/".route(3)));
        elseif( route(3) == "new" && $_POST ):
          $name = $_POST["language"];
          $code = $_POST["languagecode"];
          if( countRow(["table"=>"languages","where"=>["language_code"=>$code]]) ):
            $error      = 1;
            $errorText  = "Bu dil kodu zaten kullanılıyor.";
          else:
            $insert = $conn->prepare("INSERT INTO languages SET language_name=:name, language_code=:code ");
            $insert->execute(array("name"=>$name,"code"=>$code ));
              if( $insert ):
                $html = '<?php '.PHP_EOL.PHP_EOL;
                $html.= '$languageArray= [';
                foreach ($_POST["Language"] as $key => $value):
                  $html .= ' "'.$key.'" => "'.$value.'", '.PHP_EOL;
                endforeach;
                $html .=  '];';
                file_put_contents('app/language/'.$code.'.php', $html);
                header("Location:".site_url("admin/settings/language/"));
              endif;
          endif;
        elseif( $_GET["lang-default"] && $_GET["lang-id"] ):
          $update = $conn->prepare("UPDATE languages SET default_language=:default");
          $update->execute(array("default"=>0));
          $update = $conn->prepare("UPDATE languages SET default_language=:default WHERE language_code=:code ");
          $update->execute(array("code"=>$_GET["lang-id"],"default"=>1));
          header("Location:".site_url("admin/settings/language"));
        elseif( $_GET["lang-type"] && $_GET["lang-id"] ):
          if( countRow(["table"=>"languages","where"=>["language_type"=>"2"]]) > 1 && $_GET["lang-type"] == 1 ):
            $update = $conn->prepare("UPDATE languages SET language_type=:type WHERE language_code=:code ");
            $update->execute(array("code"=>$_GET["lang-id"],"type"=>$_GET["lang-type"]));
          elseif( $_GET["lang-type"] == 2 ):
            $update = $conn->prepare("UPDATE languages SET language_type=:type WHERE language_code=:code ");
            $update->execute(array("code"=>$_GET["lang-id"],"type"=>$_GET["lang-type"]));
          endif;
          header("Location:".site_url("admin/settings/language"));
        endif;
      endif;
  elseif( route(2) == "themes" ):
    $access = $user["access"]["themes"];
      if( $access ):
        if( route(3) == "active" && countRow(["table"=>"themes","where"=>["theme_dirname"=>route(4)]]) ):
          $update = $conn->prepare("UPDATE settings SET site_theme=:theme WHERE id=:id ");
          $update->execute(array("id"=>1,"theme"=>route(4)));
          header("Location:".site_url("admin/settings/themes"));
        elseif( route(3) && countRow(["table"=>"themes","where"=>["theme_dirname"=>route(3)]]) ):
          $lyt   =  $_GET["file"];
          $theme = $conn->prepare("SELECT * FROM themes WHERE theme_dirname=:name");
          $theme->execute(array("name"=>route(3)));
          $theme = $theme->fetch(PDO::FETCH_ASSOC);
            if( substr($lyt, -3) == "css"  ){
              $fn       = "public/".$theme["theme_dirname"]."/".$lyt;
              $codeType = "css";
              $dir      = "CSS";
            }elseif( substr($lyt, -2) == "js"  ){
              $fn       = "public/".$theme["theme_dirname"]."/".$lyt;
              $codeType = "js";
              $dir      = "JS";
            }else{
              $fn       = "app/views/".$theme["theme_dirname"]."/".$lyt;
              $codeType = "twig";
              $dir      = "HTML";
            }
          if( $_POST ):
            $text = $_POST["code"];
            $text = str_replace("&lt;","<",$text);
            $text = str_replace("&gt;",">",$text);
            $text = str_replace("&quot;",'"',$text);
            $updated_file   = fopen($fn,"w");
            fwrite($updated_file, $text);
            fclose($updated_file);
            header("Location:".site_url("admin/settings/themes/".$theme["theme_dirname"]."?file=".$lyt));
          endif;
        elseif( route(3) && !countRow(["table"=>"themes","where"=>["theme_dirname"=>route(3)]]) ):
          header("Location:".site_url("admin/settings/themes"));
        else:
          $themes = $conn->prepare("SELECT * FROM themes");
          $themes->execute(array());
          $themes = $themes->fetchAll(PDO::FETCH_ASSOC);
        endif;
      endif;
      
    elseif( route(2) == "child-panels" ):
    $access = $user["access"]["child-panels"];
      if( $access ):
        if( $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $conn->beginTransaction();
          $update = $conn->prepare("UPDATE settings SET ns1=:ns1,
			ns2=:ns2,
			childpanel_price=:price WHERE id=:id ");
          $update = $update->execute(array("id"=>1,"ns1" => $ns1,
                "ns2" => $ns2,
                "price" => $price,  ));
          if( $update ):
            $conn->commit();
            header("Location:".site_url("admin/settings/child-panels"));
            $_SESSION["client"]["data"]["success"]    = 1;
            $_SESSION["client"]["data"]["successText"]= "Success";
          else:
            $conn->rollBack();
            $error    = 1;
            $errorText= "Failed";
          endif;
        endif;
      endif;
      
    elseif( route(2) == "meta" ):
          
    $access = $user["access"]["meta"];
    
      if( $access ):
         
        if( $_POST ):
          foreach ($_POST as $key => $value) {
            $$key = $value;
          }
          $conn->beginTransaction();
          $update = $conn->prepare("UPDATE settings SET site_seo=:seo,
			site_title=:title,
			
			site_keywords=:keys,
			site_description=:desc WHERE id=:id ");
          $update = $update->execute(array("id"=>1,"seo" => $seo,
                "title" => $title,
                "keys" => $keywords,
                "desc" => $description,  ));
          if( $update ):
            $conn->commit();
            header("Location:".site_url("admin/settings/meta"));
            $_SESSION["client"]["data"]["success"]    = 1;
            $_SESSION["client"]["data"]["successText"]= "Success";
          else:
            $conn->rollBack();
            $error    = 1;
            $errorText= "Failed";
          endif;
        endif;
      endif;
    if( route(3) ): header("Location:".site_url("admin/settings/alert")); endif;
      
  endif;

  require admin_view('settings');
