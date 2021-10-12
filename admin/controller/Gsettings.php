<?php
if (!route(2)):
    $route[2] = "general";
endif;
if ($_SESSION["client"]["data"]):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
        $$key = $value;
    }
    unset($_SESSION["client"]);
endif;
$menuList = ["General settings" => "general", "Page settings" => "pages", "Payment settings" => "payment-methods", "Bank accounts" => "bank-accounts", "Payment bonuses" => "payment-bonuses", "Alert settings" => "alert", "Service providers" => "providers"];
if (!array_search(route(2), $menuList)):
    header("Location:" . site_url("admin/settings"));
elseif (route(2) == "general"):
    $access = $user["access"]["general_settings"];
    if ($access):
        if ($_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            if ($_FILES["logo"] && ($_FILES["logo"]["type"] == "image/jpeg" || $_FILES["logo"]["type"] == "image/jpg" || $_FILES["logo"]["type"] == "image/png" || $_FILES["logo"]["type"] == "image/gif")):
                $logo_name = $_FILES["logo"]["name"];
                $uzanti = substr($logo_name, -4, 4);
                $logo_newname = "theme/assets/img/logo.png";
                $upload_logo = move_uploaded_file($_FILES["logo"]["tmp_name"], $logo_newname);
            elseif ($settings["site_logo"] != ""):
                $logo_newname = $settings["site_logo"];
            else:
                $logo_newname = "";
            endif;
            if ($_FILES["favicon"] && ($_FILES["favicon"]["type"] == "image/jpeg" || $_FILES["favicon"]["type"] == "image/jpg" || $_FILES["favicon"]["type"] == "image/png" || $_FILES["favicon"]["type"] == "image/gif")):
                $favicon_name = $_FILES["favicon"]["name"];
                $uzanti = substr($logo_name, -4, 4);
                $fv_newname = "theme/assets/img/favicon.png";
                $upload_logo = move_uploaded_file($_FILES["favicon"]["tmp_name"], $fv_newname);
            elseif ($settings["favicon"] != ""):
                $fv_newname = $settings["favicon"];
            else:
                $fv_newname = "";
            endif;
            if (empty($name)):
                $errorText = "Site name can not be empty.";
                $error = 1;
            elseif (empty($currency) or empty($csymbol)):
                $errorText = "Currency and symbol can not be empty.";
                $error = 1;
            else:
                $update = $conn->prepare("UPDATE settings SET site_guvenlik=:site_guvenlik,whatsapp=:whatsapp, mail=:mail, site_maintenance=:site_maintenance, resetpass_page=:resetpass_page, resetpass_sms=:resetpass_sms, resetpass_email=:resetpass_email, site_name=:name, site_logo=:logo, favicon=:fv, site_title=:title, site_keywords=:keys, site_description=:desc, recaptcha=:recaptcha, recaptcha_key=:recaptcha_key, recaptcha_secret=:recaptcha_secret, dolar_charge=:dolar, euro_charge=:euro, ticket_system=:ticket_system, register_page=:registration_page, service_list=:service_list, service_speed=:service_speed, custom_header=:custom_header, custom_footer=:custom_footer, currency=:currency, csymbol=:csymbol WHERE id=:id ");
                $update->execute(array("id" => 1, "resetpass_page" => $resetpass, "site_maintenance" => $site_maintenance, "mail" => $mail, "site_guvenlik" => $site_guvenlik, "whatsapp" => $whatsapp, "resetpass_sms" => $resetsms, "resetpass_email" => $resetmail, "name" => $name, "logo" => $logo_newname, "fv" => $fv_newname, "title" => $title, "keys" => $keywords, "desc" => $description, "recaptcha" => $recaptcha, "recaptcha_secret" => $recaptcha_secret, "recaptcha_key" => $recaptcha_key, "dolar" => $dolar, "euro" => $euro, "ticket_system" => $ticket_system, "registration_page" => $registration_page, "service_list" => $service_list, "service_speed" => $service_speed, "custom_footer" => $custom_footer, "custom_header" => $custom_header, "currency" => $currency, "csymbol" => $csymbol));
                if ($update):
                    header("Location:" . site_url("admin/settings/general"));
                    $_SESSION["client"]["data"]["success"] = 1;
                    $_SESSION["client"]["data"]["successText"] = "Successful";
                else:
                    $errorText = "Error";
                    $error = 1;
                endif;
            endif;
        endif;
        if (route(3) == "delete-logo"):
            $update = $conn->prepare("UPDATE settings SET site_logo=:type WHERE id=:id ");
            $update->execute(array("type" => "", "id" => 1));
            if ($update):
                unlink('theme/assets/img/logo.png');
            endif;
            header("Location:" . site_url("admin/settings/general"));
        elseif (route(3) == "delete-favicon"):
            $update = $conn->prepare("UPDATE settings SET favicon=:type WHERE id=:id ");
            $update->execute(array("type" => "", "id" => 1));
            if ($update):
                unlink('theme/assets/img/favicon.png');
            endif;
            header("Location:" . site_url("admin/settings/general"));
        endif;
    endif;
elseif (route(2) == "pages"):
    $access = $user["access"]["pages"];
    if ($access):
        if (route(3) == "edit"):
            if ($_POST):
                $id = route(4);
                foreach ($_POST as $key => $value) {
                    $$key = $value;
                }
                if ($content == "<br>"):
                    $content = "";
                endif;
                if (!countRow(["table" => "pages", "where" => ["page_get" => $id]])):
                    $error = 1;
                    $icon = "error";
                    $errorText = "Please select a valid payment method";
                else:
                    $update = $conn->prepare("UPDATE pages SET page_content=:content WHERE page_get=:id ");
                    $update->execute(array("id" => $id, "content" => $content));
                    if ($update):
                        $success = 1;
                        $successText = "Successful";
                    else:
                        $error = 1;
                        $errorText = "Error";
                    endif;
                endif;
            endif;
            $page = $conn->prepare("SELECT * FROM pages WHERE page_get=:get ");
            $page->execute(array("get" => route(4)));
            $page = $page->fetch(PDO::FETCH_ASSOC);
            if (!$page):
                header("Location:" . site_url("admin/settings/pages"));
            endif;
        elseif (!route(3)):
            $pageList = $conn->prepare("SELECT * FROM pages ");
            $pageList->execute(array());
            $pageList = $pageList->fetchAll(PDO::FETCH_ASSOC);
        else:
            header("Location:" . site_url("admin/settings/pages"));
        endif;
    endif;
    if (route(5)):
        header("Location:" . site_url("admin/settings/pages"));
    endif;
elseif (route(2) == "payment-methods"):
    $access = $user["access"]["payments_settings"];
    if ($access):
        if (route(3) == "edit" && $_POST):
            $id = route(4);
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            if (!countRow(["table" => "payment_methods", "where" => ["method_get" => $id]])):
                $error = 1;
                $icon = "error";
                $errorText = "Please select a valid payment method";
            else:
                $update = $conn->prepare("UPDATE payment_methods SET method_min=:min, method_max=:max, method_type=:type, method_extras=:extras WHERE method_get=:id ");
                $update->execute(array("id" => $id, "min" => $min, "max" => $max, "type" => $method_type, "extras" => json_encode($_POST)));
                if ($update):
                    $error = 1;
                    $icon = "success";
                    $errorText = "Successful";
                else:
                    $error = 1;
                    $icon = "error";
                    $errorText = "Error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon]);
            exit();
        elseif (route(3) == "type"):
            $id = $_GET["id"];
            $type = $_GET["type"];
            if ($type == "off"):
                $type = 1;
            elseif ($type == "on"):
                $type = 2;
            endif;
            $update = $conn->prepare("UPDATE payment_methods SET method_type=:type WHERE id=:id ");
            $update->execute(array("id" => $id, "type" => $type));
            if ($update):
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
    if (route(3)):
        header("Location:" . site_url("admin/settings/payment-methods"));
    endif;
elseif (route(2) == "bank-accounts"):
    $access = $user["access"]["bank_accounts"];
    if ($access):
        if (route(3) == "new" && $_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            if (empty($bank_name)):
                $error = 1;
                $errorText = "Bank name can not be empty.";
                $icon = "error";
            elseif (empty($bank_alici)):
                $error = 1;
                $errorText = "Holder can not be empty.";
                $icon = "error";
            elseif (empty($bank_sube)):
                $error = 1;
                $errorText = "Branch no can not be empty.";
                $icon = "error";
            elseif (empty($bank_hesap)):
                $error = 1;
                $errorText = "Account no can not be empty.";
                $icon = "error";
            elseif (empty($bank_iban)):
                $error = 1;
                $errorText = "IBAN can not be empty.";
                $icon = "error";
            else:
                $conn->beginTransaction();
                $insert = $conn->prepare("INSERT INTO bank_accounts SET bank_name=:name, bank_sube=:sube, bank_hesap=:hesap, bank_iban=:iban, bank_alici=:alici ");
                $insert = $insert->execute(array("name" => $bank_name, "sube" => $bank_sube, "hesap" => $bank_hesap, "iban" => $bank_iban, "alici" => $bank_alici));
                if ($insert):
                    $conn->commit();
                    $referrer = site_url("admin/settings/bank-accounts");
                    $error = 1;
                    $errorText = "Successful";
                    $icon = "success";
                else:
                    $conn->rollBack();
                    $error = 1;
                    $errorText = "Error";
                    $icon = "error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
            exit();
        elseif (route(3) == "edit"):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            $id = route(4);
            if (empty($bank_name)):
                $error = 1;
                $errorText = "Bank name can not be empty.";
                $icon = "error";
            elseif (empty($bank_alici)):
                $error = 1;
                $errorText = "Holder can not be empty.";
                $icon = "error";
            elseif (empty($bank_sube)):
                $error = 1;
                $errorText = "Branch no can not be empty.";
                $icon = "error";
            elseif (empty($bank_hesap)):
                $error = 1;
                $errorText = "Account no can not be empty.";
                $icon = "error";
            elseif (empty($bank_iban)):
                $error = 1;
                $errorText = "IBAN can not be empty.";
                $icon = "error";
            else:
                $conn->beginTransaction();
                $update = $conn->prepare("UPDATE bank_accounts SET bank_name=:name, bank_sube=:sube, bank_hesap=:hesap, bank_iban=:iban, bank_alici=:alici WHERE id=:id ");
                $update = $update->execute(array("name" => $bank_name, "sube" => $bank_sube, "hesap" => $bank_hesap, "iban" => $bank_iban, "alici" => $bank_alici, "id" => $id));
                if ($update):
                    $conn->commit();
                    $referrer = site_url("admin/settings/bank-accounts");
                    $error = 1;
                    $errorText = "Successful";
                    $icon = "success";
                else:
                    $conn->rollBack();
                    $error = 1;
                    $errorText = "Error";
                    $icon = "error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
            exit();
        elseif (route(3) == "delete"):
            $id = route(4);
            if (!countRow(["table" => "bank_accounts", "where" => ["id" => $id]])):
                $error = 1;
                $icon = "error";
                $errorText = "Please select a valid payment bonus";
            else:
                $delete = $conn->prepare("DELETE FROM bank_accounts WHERE id=:id ");
                $delete->execute(array("id" => $id));
                if ($delete):
                    $error = 1;
                    $icon = "success";
                    $errorText = "Successful";
                    $referrer = site_url("admin/settings/bank-accounts");
                else:
                    $error = 1;
                    $icon = "error";
                    $errorText = "Error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 0]);
            exit();
        elseif (!route(3)):
            $bankList = $conn->prepare("SELECT * FROM bank_accounts ");
            $bankList->execute(array());
            $bankList = $bankList->fetchAll(PDO::FETCH_ASSOC);
        else:
            header("Location:" . site_url("admin/settings/bank-accounts"));
        endif;
    endif;
    if (route(5)):
        header("Location:" . site_url("admin/settings/bank-accounts"));
    endif;
elseif (route(2) == "payment-bonuses"):
    $access = $user["access"]["payments_bonus"];
    if ($access):
        if (route(3) == "new" && $_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            if (empty($method_type)):
                $error = 1;
                $errorText = "Method can not be empty.";
                $icon = "error";
            elseif (empty($amount)):
                $error = 1;
                $errorText = "Bonus can not be empty.";
                $icon = "error";
            elseif (empty($from)):
                $error = 1;
                $errorText = "Can not be from";
                $icon = "error";
            else:
                $conn->beginTransaction();
                $insert = $conn->prepare("INSERT INTO payments_bonus SET bonus_method=:method, bonus_from=:from, bonus_amount=:amount, bonus_type=:type ");
                $insert = $insert->execute(array("method" => $method_type, "from" => $from, "amount" => $amount, "type" => 2));
                if ($insert):
                    $conn->commit();
                    $referrer = site_url("admin/settings/payment-bonuses");
                    $error = 1;
                    $errorText = "Successful";
                    $icon = "success";
                else:
                    $conn->rollBack();
                    $error = 1;
                    $errorText = "Error";
                    $icon = "error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
            exit();
        elseif (route(3) == "edit" && $_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            $id = route(4);
            if (empty($method_type)):
                $error = 1;
                $errorText = "Method can not be empty.";
                $icon = "error";
            elseif (empty($amount)):
                $error = 1;
                $errorText = "Bonus can not be empty.";
                $icon = "error";
            elseif (empty($from)):
                $error = 1;
                $errorText = "Can not be from";
                $icon = "error";
            else:
                $conn->beginTransaction();
                $update = $conn->prepare("UPDATE payments_bonus SET bonus_method=:method, bonus_from=:from, bonus_amount=:amount WHERE bonus_id=:id ");
                $update = $update->execute(array("method" => $method_type, "from" => $from, "amount" => $amount, "id" => $id));
                if ($update):
                    $conn->commit();
                    $referrer = site_url("admin/settings/payment-bonuses");
                    $error = 1;
                    $errorText = "Successful";
                    $icon = "success";
                else:
                    $conn->rollBack();
                    $error = 1;
                    $errorText = "Error";
                    $icon = "error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
            exit();
        elseif (route(3) == "delete"):
            $id = route(4);
            if (!countRow(["table" => "payments_bonus", "where" => ["bonus_id" => $id]])):
                $error = 1;
                $icon = "error";
                $errorText = "Please select a valid payment bonus.";
            else:
                $delete = $conn->prepare("DELETE FROM payments_bonus WHERE bonus_id=:id ");
                $delete->execute(array("id" => $id));
                if ($delete):
                    $error = 1;
                    $icon = "success";
                    $errorText = "Successful";
                    $referrer = site_url("admin/settings/payment-bonuses");
                else:
                    $error = 1;
                    $icon = "error";
                    $errorText = "Error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 0]);
            exit();
        elseif (!route(3)):
            $bonusList = $conn->prepare("SELECT * FROM payments_bonus INNER JOIN payment_methods WHERE payment_methods.id = payments_bonus.bonus_method ORDER BY payment_methods.id DESC ");
            $bonusList->execute(array());
            $bonusList = $bonusList->fetchAll(PDO::FETCH_ASSOC);
        else:
            header("Location:" . site_url("admin/settings/payment-bonuses"));
        endif;
    endif;
elseif (route(2) == "providers"):
    $access = $user["access"]["providers"];
    if ($access):
        if (route(3) == "new" && $_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            if (empty($name)):
                $error = 1;
                $errorText = "Provider name can not be empty.";
                $icon = "error";
            elseif (empty($type)):
                $error = 1;
                $errorText = "Provider type can not be empty.";
                $icon = "error";
            elseif (empty($url)):
                $error = 1;
                $errorText = "Provider API URL can not be empty.";
                $icon = "error";
            elseif (empty($apikey)):
                $error = 1;
                $errorText = "Provider API Key can not be empty.";
                $icon = "error";
            else:
                $conn->beginTransaction();
                $insert = $conn->prepare("INSERT INTO service_api SET api_name=:name, api_key=:key, api_url=:url, api_limit=:limit, api_type=:type, api_alert=:alert ");
                $insert = $insert->execute(array("name" => $name, "key" => $apikey, "url" => $url, "limit" => $limit, "type" => $type, "alert" => 2));
                if ($insert):
                    $conn->commit();
                    $referrer = site_url("admin/settings/providers");
                    $error = 1;
                    $errorText = "Successful";
                    $icon = "success";
                else:
                    $conn->rollBack();
                    $error = 1;
                    $errorText = "Error";
                    $icon = "error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
            exit();
        elseif (route(3) == "edit" && $_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            $id = route(4);
            if (empty($name)):
                $error = 1;
                $errorText = "Provider name can not be empty.";
                $icon = "error";
            elseif (empty($type)):
                $error = 1;
                $errorText = "Provider type can not be empty.";
                $icon = "error";
            elseif (empty($url)):
                $error = 1;
                $errorText = "Provider API URL can not be empty.";
                $icon = "error";
            elseif (empty($apikey)):
                $error = 1;
                $errorText = "Provider API Key can not be empty.";
                $icon = "error";
            else:
                $conn->beginTransaction();
                $update = $conn->prepare("UPDATE service_api SET api_name=:name, api_key=:key, api_url=:url, api_limit=:limit, api_type=:type WHERE id=:id ");
                $update = $update->execute(array("name" => $name, "key" => $apikey, "url" => $url, "limit" => $limit, "type" => $type, "id" => $id));
                if ($update):
                    $conn->commit();
                    $referrer = site_url("admin/settings/providers");
                    $error = 1;
                    $errorText = "Successful";
                    $icon = "success";
                else:
                    $conn->rollBack();
                    $error = 1;
                    $errorText = "Error";
                    $icon = "error";
                endif;
            endif;
            echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
            exit();
        elseif (!route(3)):
            $providersList = $conn->prepare("SELECT * FROM service_api ");
            $providersList->execute(array());
            $providersList = $providersList->fetchAll(PDO::FETCH_ASSOC);
        else:
            header("Location:" . site_url("admin/settings/providers"));
        endif;
    endif;
    if (route(5)):
        header("Location:" . site_url("admin/settings/providers"));
    endif;
elseif (route(2) == "alert"):
    $access = $user["access"]["alert_settings"];
    if ($access):
        if ($_POST):
            foreach ($_POST as $key => $value) {
                $$key = $value;
            }
            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE settings SET alert_apibalance=:alert_apibalance, alert_serviceapialert=:alert_serviceapialert, admin_mail=:mail, admin_telephone=:telephone, alert_type=:alert_type, alert_newticket=:alert_newticket, alert_newmanuelservice=:alert_newmanuelservice,alert_newbankpayment=:alert_newbankpayment, sms_provider=:sms_provider, sms_title=:sms_title, sms_user=:sms_user, sms_pass=:sms_pass, smtp_user=:smtp_user, smtp_pass=:smtp_pass, smtp_server=:smtp_server, smtp_port=:smtp_port, smtp_protocol=:smtp_protocol WHERE id=:id ");
            $update = $update->execute(array("id" => 1, "alert_apibalance" => $alert_apibalance, "alert_serviceapialert" => $serviceapialert, "mail" => $admin_mail, "telephone" => $admin_telephone, "alert_type" => $alert_type, "alert_newticket" => $alert_newticket, "alert_newmanuelservice" => $alert_newmanuelservice, "alert_newbankpayment" => $alert_newbankpayment, "sms_provider" => $sms_provider, "sms_title" => $sms_title, "sms_user" => $sms_user, "sms_pass" => $sms_pass, "smtp_user" => $smtp_user, "smtp_pass" => $smtp_pass, "smtp_server" => $smtp_server, "smtp_port" => $smtp_port, "smtp_protocol" => $smtp_protocol));
            if ($update):
                $conn->commit();
                header("Location:" . site_url("admin/settings/alert"));
                $_SESSION["client"]["data"]["success"] = 1;
                $_SESSION["client"]["data"]["successText"] = "Successful";
            else:
                $conn->rollBack();
                $error = 1;
                $errorText = "Error";
            endif;
        endif;
    endif;
    if (route(3)):
        header("Location:" . site_url("admin/settings/alert"));
    endif;
endif;
require admin_view('settings');
