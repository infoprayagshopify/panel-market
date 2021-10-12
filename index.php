<?php

require __DIR__.'/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
$mail = new PHPMailer;
require __DIR__.'/app/init.php';

$first_route  = explode('?',$_SERVER["REQUEST_URI"]);
$gets         = explode('&',$first_route[1]);
  foreach ($gets as $get) {
    $get = explode('=',$get);
    $_GET[$get[0]]  = $get[1];
  }
$routes       = array_filter( explode('/',$first_route[0]) );

  if( SUBFOLDER === true ){
    array_shift($routes);
    $route = $routes;
  }else {
    foreach ($routes as $index => $value):
      $route[$index-1] = $value;
    endforeach;
  }


  if( $_GET["lang"] && $user['auth'] != 1 ):
    include 'app/language/list.php';
    if( countRow(["table"=>"languages","where"=>["language_type"=>2,"language_code"=>$_GET["lang"]]]) ):
      unset($_SESSION["lang"]);
      $_SESSION["lang"] = $_GET["lang"];
      include 'app/language/'.$_GET["lang"].'.php';
    else:
           $_SESSION["lang"] = $_GET["lang"];
      include 'app/language/'.$_GET["lang"].'.php';
    endif;
    $selectedLang = $_SESSION["lang"];
    header("Location:".site_url());
  else:
    if( $_SESSION["lang"] && $user['auth'] != 1 ):
      $language = $_SESSION["lang"];
    elseif( $user['auth'] != 1 ):
      $language = $conn->prepare("SELECT * FROM languages WHERE default_language=:default ");
      $language->execute(array("default"=>1));
      $language = $language->fetch(PDO::FETCH_ASSOC);
      $language = $language["language_code"];
    else:
      if( getRow(["table"=>"languages","where"=>["language_code"=>$user["lang"]]]) ):
        $language = $user["lang"];
      else:
        $language = $conn->prepare("SELECT * FROM languages WHERE default_language=:default ");
        $language->execute(array("default"=>1));
        $language = $language->fetch(PDO::FETCH_ASSOC);
        $language = $language["language_code"];
      endif;
    endif;
    include 'app/language/'.$language.'.php';
    $selectedLang = $language;
  endif;

  if( !isset($route[0]) && $_SESSION["msmbilisim_userlogin"] == true ){
    $route[0] = "neworder";
    $routeType= 0;
  }elseif( !isset($route[0]) && $_SESSION["msmbilisim_userlogin"] == false ){
    $route[0] = "auth";
    $routeType= 1;
  }elseif( $route[0] == "auth" && $_SESSION["msmbilisim_userlogin"] == false ){
    $routeType= 1;
  }else{
    $routeType= 0;
  }


  if( !file_exists( controller($route[0]) ) ){
    $route[0] = "404";
  }


  if( route(0) != "admin" && $settings["site_maintenance"] == 1 ): include 'app/views/maintenance.php';exit(); endif;
  if( $settings["service_list"] == 2 ): $serviceList = 1; endif;

  require controller($route[0]);

  if( $settings["recaptcha"] == 1 ){
    $captcha = false;
  }elseif( ( $settings["recaptcha"] == 2 && ( route(1) == "register" || route(0) == "resetpassword" ) ) || $_SESSION["recaptcha"] ){
    $captcha = true;
  }

  if( $settings["resetpass_page"] == 1 ){
    $resetPage = false;
  }elseif( $settings["resetpass_page"] == 2 ){
    $resetPage = true;
  }

  if( route(0) == "auth" ): $active_menu = route(1); else: $active_menu = route(0); endif;

  if( route(0) != "admin" && route(0) != "ajax_data" ){
    $languages  = $conn->prepare("SELECT * FROM languages WHERE language_type=:type");
    $languages->execute(array("type"=>2));
    $languages  = $languages->fetchAll(PDO::FETCH_ASSOC);
    $languagesL = [];
      foreach ($languages as $language) {
        $l["name"] = $language["language_name"];
        $l["code"] = $language["language_code"];
          if( $_SESSION["lang"] && $language["language_code"] == $_SESSION["lang"] ){
            $l["active"] = 1;
          }elseif( !$_SESSION["lang"] ){
            $l["active"] = $language["default_language"];
          }else{
            $l["active"] = 0;
          }
        array_push($languagesL,$l);
      }

    if( !$templateDir ){
      $templateDir = route($routeType);
    }
      if( $templateDir == "login" || $templateDir == "register" ):
        $contentGet = "auth";
      else:
        $contentGet = $templateDir;
      endif;
    $content  = $conn->prepare("SELECT * FROM pages WHERE page_get=:get ");
    $content->execute(array("get"=>$contentGet));
    $content  = $content->fetch(PDO::FETCH_ASSOC);
    $content  = $content["page_content"];
	

	
	if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
	  
	  
	  echo $twig->render( $templateDir.'.twig',
      array(
            'site'=>[
                      'url'=>URL,'favicon'=>$settings['favicon'],"logo"=>$settings["site_logo"],"site_name"=>$settings["site_name"],
                      'service_speed'=>$settings["service_speed"],"keywords"=>$settings["site_keywords"],
                      "description"=>$settings["site_description"],'languages'=>$languagesL,"childpanel_price"=>$settings["childpanel_price"],"ns1"=>$settings["ns1"],"ns2"=>$settings["ns2"]
                    ],
            'styleList'=>$stylesheet["stylesheets"],'scriptList'=>$stylesheet["scripts"],'captchaKey'=>$settings["recaptcha_key"],'captcha'=>$captcha,'resetPage'=>$resetPage,'serviceCategory'=>$categories,'categories'=>$categories,
            'error'=>$error,'errorText'=>$errorText,'success'=>$success,"servicesPage"=>$serviceList,"resetType"=>$resetType,'successText'=>$successText,'title'=>$title,
            'user'=>$user,'data'=>$_SESSION["data"],'settings'=>$settings,'total_orders'=>$totalRows,'search'=>urldecode($_GET["search"]),"active_menu"=>$active_menu,'ticketList'=>$ticketList,'messageList'=>$messageList,
            'ticketCount'=>new_ticket($user['client_id']),'paymentsList'=>$methodList,'transactions'=>$transaction_logs,'chilpanel_logs'=>$panel_logs,'PaytmQR'=>$PaytmQR["method_type"],'PaytmQRimage'=>$PaytmQRimage,'bankPayment'=>$bankPayment["method_type"],'bankList'=>$bankList,
            'status'=>$route[1],'orders'=>$ordersList,'pagination'=>$paginationArr,'contentText'=>$content,'headerCode'=>$settings["custom_header"],
            'footerCode'=>$settings["custom_footer"],'lang'=>$languageArray,'timezones'=>$timezones
      )
    );
	
	
	}else{
		

	$uye_id = $user["client_id"];
	$dripfeedvarmi = $conn->query("SELECT * FROM orders WHERE client_id=$uye_id and dripfeed=2");
	if ( $dripfeedvarmi->rowCount() ){
		$dripfeedcount=1;
	}else{
		$dripfeedcount=0;
	}
	
	
	$subscriptionsvarmi = $conn->query("SELECT * FROM orders WHERE client_id=$uye_id and subscriptions_type=2");
	if ( $subscriptionsvarmi->rowCount() ){
		$subscriptionscount=1;
	}else{
		$subscriptionscount=0;
	}
	

		  $statubul = $conn->prepare("SELECT SUM(payment_amount) as toplam FROM payments WHERE client_id=:client_id ");
          $statubul->execute(array("client_id"=>$uye_id));
          $statubul = $statubul->fetch(PDO::FETCH_ASSOC);
		  
		  
		  if($statubul["toplam"]<=$bronz_statu):
			$statusu = "Bronz Statü";
		  endif;
		  
		  if($statubul["toplam"]>$bronz_statu and $statubul["toplam"]<=$silver_statu):
			$statusu = "Silver Statü";
		  endif;
		  
		  if($statubul["toplam"]>$silver_statu and $statubul["toplam"]<=$gold_statu):
			$statusu = "Gold Statü";
		  endif;
		  
		  if($statubul["toplam"]>$gold_statu and $statubul["toplam"]<=$bayi_statu):
			$statusu = "Bayi Statü";
		  endif;
		  
		  if($statubul["toplam"]>$bayi_statu):
			$statusu = "Bayi Statü";
		  endif;
	
	
	 echo $twig->render( $templateDir.'.twig',
      array(
            'site'=>[
                      'url'=>URL,'favicon'=>$settings['favicon'],"logo"=>$settings["site_logo"],"site_name"=>$settings["site_name"],'service_speed'=>$settings["service_speed"],"keywords"=>$settings["site_keywords"],
                      "description"=>$settings["site_description"],'languages'=>$languagesL,"dripfeedcount"=>$dripfeedcount,"subscriptionscount"=>$subscriptionscount,"childpanel_price"=>$settings["childpanel_price"],"ns1"=>$settings["ns1"],"ns2"=>$settings["ns2"]
                    ],
            'styleList'=>$stylesheet["stylesheets"],'scriptList'=>$stylesheet["scripts"],'captchaKey'=>$settings["recaptcha_key"],'captcha'=>$captcha,'resetPage'=>$resetPage,'serviceCategory'=>$categories,'categories'=>$categories,
            'error'=>$error,'errorText'=>$errorText,'success'=>$success,"servicesPage"=>$serviceList,"resetType"=>$resetType,'successText'=>$successText,'title'=>$title,
            'user'=>$user,'data'=>$_SESSION["data"],'statu'=>$statusu,'settings'=>$settings,'total_orders'=>$totalRows,'search'=>urldecode($_GET["search"]),"active_menu"=>$active_menu,'ticketList'=>$ticketList,'messageList'=>$messageList,
            'ticketCount'=>new_ticket($user['client_id']),'paymentsList'=>$methodList,'transactions'=>$transaction_logs,'chilpanel_logs'=>$panel_logs,'PaytmQRimage'=>$PaytmQRimage,'PaytmQR'=>$PaytmQR["method_type"],'bankPayment'=>$bankPayment["method_type"],'bankList'=>$bankList,
            'status'=>$route[1],'orders'=>$ordersList,'pagination'=>$paginationArr,'contentText'=>$content,'headerCode'=>$settings["custom_header"],
            'footerCode'=>$settings["custom_footer"],'lang'=>$languageArray,'timezones'=>$timezones
      )
    );
	
	}


   
  }

  if( route(0) != "neworder" && route(0) != "child-panels" && route(0) != "ajax_data" && ( route(0) != "admin" && route(1) != "services" ) ):
    unset($_SESSION["data"]);
  endif;