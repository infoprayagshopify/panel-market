<?php

function getCurrencyUnit(){
    global $conn;
    $lang = $conn->prepare("SELECT site_currency FROM settings WHERE id=:id");
    $lang->execute(array("id" => 1));
    $langRow = $lang->fetch(PDO::FETCH_ASSOC);
    return $langRow["site_currency"] ?? 'TRY';
}

function CreateApiKey($data){
    global $conn;
    $data = md5($data["email"].$data["username"].rand(9999,2324332));
    $row  = $conn->prepare("SELECT * FROM clients WHERE apikey=:key ");
    $row-> execute(array("key"=>$data));
    if( $row->rowCount() ){
        CreateApiKey();
    }else{
        return $data;
    }
}

function createPaymentCode(){
    global $conn;
    $row  = $conn->prepare("SELECT * FROM payments WHERE payment_method!=:method ORDER BY payment_privatecode DESC LIMIT 1 ");
    $row-> execute(array("method"=>4 ));
    $row  = $row->fetch(PDO::FETCH_ASSOC);
    return $row["payment_privatecode"];
}

function generate_shopier_form($data){
    $api_key  = $data->apikey;
    $secret  = $data->apisecret;
    $user_registered = date("Y.m.d");
    $time_elapsed = time() - strtotime($user_registered);
    $buyer_account_age = (int)($time_elapsed/86400);
    $currency = 0;
    $dataArray = $data;

    $productinfo = $data->item_name;
    $producttype = 1;


    $productinfo = str_replace('"','',$productinfo);
    $productinfo = str_replace('"','',$productinfo);
    $current_language=0;
    $current_lan=0;
    $modul_version=('1.0.4');
    srand(time(NULL));
    $random_number=rand(1000000,9999999);
    $args = array(
        'API_key' => $api_key,
        'website_index' => $data->website_index,
        'platform_order_id' => $data->order_id,
        'product_name' => $productinfo,
        'product_type' => $producttype,
        'buyer_name' => $data->buyer_name,
        'buyer_surname' => $data->buyer_surname,
        'buyer_email' => $data->buyer_email,
        'buyer_account_age' => $buyer_account_age,
        'buyer_id_nr' => 0,
        'buyer_phone' => $data->buyer_phone,
        'billing_address' => $data->billing_address,
        'billing_city' => $data->city,
        'billing_country' => "TR",
        'billing_postcode' => "",
        'shipping_address' => $data->billing_address,
        'shipping_city' => $data->city,
        'shipping_country' => "TR",
        'shipping_postcode' => "",
        'total_order_value' => $data->ucret,
        'currency' => $currency,
        'platform' => 0,
        'is_in_frame' => 1,
        'current_language'=>$current_lan,
        'modul_version'=>$modul_version,
        'random_nr' => $random_number
    );

    $data = $args["random_nr"].$args["platform_order_id"].$args["total_order_value"].$args["currency"];
    $signature = hash_hmac("SHA256",$data,$secret,true);
    $signature = base64_encode($signature);
    $args['signature'] = $signature;

    $args_array = array();
    foreach($args as $key => $value){
        $args_array[] = "<input type='hidden' name='$key' value='$value'/>";
    }
    if( !empty($dataArray->apikey) && !empty($dataArray->apisecret) && !empty($dataArray->website_index) ){
        $_SESSION["data"]["payment_shopier"]  = true;

        return '<html> <!doctype html><head> <meta charset="UTF-8"> <meta content="True" name="HandheldFriendly"> <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="robots" content="noindex, nofollow, noarchive" />
      <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0" /> <title lang="tr">Güvenli Ödeme Sayfası</title><body><head>
      <form action="https://www.shopier.com/ShowProduct/api_pay4.php" method="post" id="shopier_payment_form" style="display: none">' . implode('', $args_array) .
            '<script>setInterval(function(){document.getElementById("shopier_payment_form").submit();},2000)</script></form></body></html>';
    }

}


function username_check($username){
    if (preg_match('/^[a-z\d_]{4,32}$/i', $username)){
        $validate = true;
    }else{
        $validate = false;
    }
    return $validate;
}

function email_check($email){
    if( filter_var($email,FILTER_VALIDATE_EMAIL) ){
        $validate = true;
    }else{
        $validate = false;
    }
    return $validate;
}

function userdata_check($where,$data){
    global $conn;
    $row  = $conn->prepare("SELECT * FROM clients WHERE $where=:data ");
    $row-> execute(array("data"=>$data));
    if( $row->rowCount() ){
        $validate = true;
    }else{
        $validate = false;
    }
    return $validate;
}

function userlogin_check($username,$pass){
    global $conn;
    $row  = $conn->prepare("SELECT * FROM clients WHERE username=:username && password=:password ");
    $row-> execute(array("username"=>$username,"password"=>md5(sha1(md5($pass))) ));
    if( $row->rowCount() ){
        $validate = true;
    }else{
        $validate = false;
    }
    return $validate;
}

function serviceSpeed($speed,$price){
    $siteLang = strtolower(getCurrencyUnit());
    switch ($speed) {
        case '1':
            return '<span style="color: #f24236;font-weight: 500;">'.priceFormat($price).' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i> <span style="font-size:10px;"  class="fa fa-arrow-down"> </span></span>';
            break;
        case '2':
            return '<span style="color: #fe6d86;font-weight: 500;">'.priceFormat($price).' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i></i> <span style="font-size:10px;"  class="fa fa-arrow-down"> </span></span>';
            break;
        case '3':
            return '<span style="color: #5696c9;font-weight: 500;">'.priceFormat($price).' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i></i> <span style="font-size:10px;" class="fa fa-compress"> </span></span>';
            break;
        case '4':
            return '<span style="color: #0dd887;font-weight: 500;">'.priceFormat($price).' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i></i> <span style="font-size:10px;"  class="fa fa-arrow-up"> </span></span>';
            break;
    }
}

function service_price($service){
    global $conn,$user;
    $row = $conn->prepare("SELECT * FROM clients_price WHERE service_id=:s_id && client_id=:c_id ");
    $row->execute(array("s_id"=>$service,"c_id"=>$user["client_id"] ));
    if( $row->rowCount() ){
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $price  = $row["service_price"];
    }else{
        $row    = $conn->prepare("SELECT * FROM services WHERE service_id=:id");
        $row    ->execute(array("id"=>$service ));
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $price  = $row["service_price"];
    }
    return $price;
}

function client_price($service,$userid){
    global $conn,$user;
    $row = $conn->prepare("SELECT * FROM clients_price WHERE service_id=:s_id && client_id=:c_id ");
    $row->execute(array("s_id"=>$service,"c_id"=>$userid ));
    if( $row->rowCount() ){
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $price  = $row["service_price"];
    }else{
        $row    = $conn->prepare("SELECT * FROM services WHERE service_id=:id");
        $row    ->execute(array("id"=>$service ));
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $price  = $row["service_price"];
    }
    return $price;
}

function open_bankpayment($user){
    global $conn;
    $row  = $conn->prepare("SELECT * FROM payments WHERE client_id=:client && payment_status=:status && payment_method=:method ");
    $row-> execute(array("client"=>$user,"status"=>1,"method"=>4 ));
    $validate = $row->rowCount();
    return $validate;
}

function open_ticket($user){
    global $conn;
    $row  = $conn->prepare("SELECT * FROM tickets WHERE client_id=:client && status=:status ");
    $row-> execute(array("client"=>$user,"status"=>"pending" ));
    $validate = $row->rowCount();
    return $validate;
}

function new_ticket($user){
    global $conn;
    $row  = $conn->prepare("SELECT * FROM tickets WHERE client_id=:client && support_new=:new ");
    $row-> execute(array("client"=>$user,"new"=>2 ));
    $validate = $row->rowCount();
    return $validate;
}

function countRow($data){
    global $conn;
    $where    = "";
    if( $data["where"] ):
        $where    = "WHERE ";
        foreach ($data["where"] as $key => $value) {
            $where.=" $key=:$key && ";
            $execute[$key]=$value;
        }
        $where    = substr($where,0,-3);
    else:
        $execute[]= "";
    endif;
    $row  = $conn->prepare("SELECT * FROM {$data['table']} $where ");
    $row-> execute($execute);
    $validate = $row->rowCount();
    return $validate;
}

function getRows($data){
    global $conn;
    $where    = "";
    $order    = "";
    $order    = "";
    $limit    = "";
    $execute[]= "";
    if( $data["where"] ):
        $where    = "WHERE ";
        foreach ($data["where"] as $key => $value) {
            $where.=" $key=:$key && ";
            $execute[$key]=$value;
        }
        $where    = substr($where,0,-3);
    endif;

    if( $data["order"] ): $order  = "ORDER BY ".$data["order"]." ".$data["order_type"]; endif;
    if( $data["limit"] ): $limit  = "LIMIT ".$data["limit"]; endif;
    $row  = $conn->prepare("SELECT * FROM {$data['table']} $where $order $limit ");
    $row-> execute($execute);
    if( $row->rowCount() ):
        $rows = $row->fetchAll(PDO::FETCH_ASSOC);
    else:
        $rows = [];
    endif;
    return $rows;
}

function getRow($data){
    global $conn;
    $where    = "WHERE ";
    foreach ($data["where"] as $key => $value) {
        $where.=" $key=:$key && ";
        $execute[$key]=$value;
    }
    $where    = substr($where,0,-3);
    $row  = $conn->prepare("SELECT * FROM {$data['table']} $where ");
    $row-> execute($execute);
    if( $row->rowCount() ):
        $row = $row->fetch(PDO::FETCH_ASSOC);
    else:
        $row = [];
    endif;
    return $row;
}

function statutoTR($status){

    switch ($status) {
        case 'pending':
            $statu  = "Beklemede";
            break;
        case 'inprogress':
            $statu  = "Yükleniyor";
            break;
        case 'completed':
            $statu  = "Tamamlandı";
            break;
        case 'partial':
            $statu  = "Kısmi tamamlandı";
            break;
        case 'processing':
            $statu  = "processing";
            break;
        case 'canceled':
            $statu  = "İptal";
            break;
    }

    return $statu;

}

function dripfeedstatutoTR($status){

    switch ($status) {
        case 'active':
            $statu  = "Aktif";
            break;
        case 'canceled':
            $statu  = "İptal";
            break;
        case 'completed':
            $statu  = "Tamamlandı";
            break;
    }

    return $statu;

}

function ticketStatu($status){

    switch ($status) {
        case 'closed':
            $statu  = "Kapalı";
            break;
        case 'answered':
            $statu  = "Yanıtlanmış";
            break;
        case 'pending':
            $statu  = "Cevap bekliyor";
            break;
    }
    return $statu;


}

function subscriptionstatutoTR($status){

    switch ($status) {
        case 'active':
            $statu  = "Aktif";
            break;
        case 'canceled':
            $statu  = "İptal";
            break;
        case 'completed':
            $statu  = "Tamamlanmış";
            break;
        case 'paused':
            $statu  = "Durdurulmuş";
            break;
        case 'expired':
            $statu  = "Süresi dolmuş";
            break;
        case 'limit':
            $statu  = "Gönderimde";
            break;
    }

    return $statu;

}

function serviceTypeGetList($type){
    switch ($type) {
        case "Default":
            $service_type = 1;
            break;
        case "Package":
            $service_type = 2;
            break;
        case "Custom Comments":
            $service_type = 3;
            break;
        case "Custom Comments Package":
            $service_type = 4;
            break;
        case "Mentions":
            $service_type = 5;
            break;
        case "Mentions with hashtags":
            $service_type = 6;
            break;
        case "Mentions custom list":
            $service_type = 7;
            break;
        case "Mentions custom list":
            $service_type = "8";
            break;
        case "Mentions user followers":
            $service_type = 9;
            break;
        case "Mentions media likers":
            $service_type = 10;
            break;
        case "Subscriptions":
            $service_type = 11;
            break;

        default:
            break;
    }
    return $service_type;
}


function array_group_by(array $arr, $key) : array{
    if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
        trigger_error('array_group_by(): The key should be a string, an integer, a float, or a function', E_USER_ERROR);
    }
    $isFunction = !is_string($key) && is_callable($key);
    $grouped = [];
    foreach ($arr as $value) {
        $groupKey = null;
        if ($isFunction) {
            $groupKey = $key($value);
        } else if (is_object($value)) {
            $groupKey = $value->{$key};
        } else {
            $groupKey = $value[$key];
        }
        $grouped[$groupKey][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $groupKey => $value) {
            $params = array_merge([$value], array_slice($args, 2, func_num_args()));
            $grouped[$groupKey] = call_user_func_array('array_group_by', $params);
        }
    }
    return $grouped;
}

function instagramProfilecheck($array){
    $type     = $array["type"];
    if( $type == "username" ):
        $profile = "https://www.instagram.com/".$array["url"];
        $search_type  = "profile";
    else:
        $profile  = $array["url"];
        $check = explode("instagram.com/",$profile);
        if( substr($check[1],0,2) == "p/" ):
            $search_type  = "photo";
        else:
            $search_type  = "profile";
        endif;
    endif;

    $html     = file_get_contents($profile);
    $arr      = explode('window._sharedData = ',$html);
    $arr      = explode(';</script>',$arr[1]);
    $obj      = json_decode($arr[0] , true);

    if( $search_type == "profile" ):
        $user		  =	$obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
        $private  =	$obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["is_private"];
    else:
        $user		  =	$obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["owner"];
        $private  =	$obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["owner"]["is_private"];
        if( !$user ):
            $user		  =	$obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
            $private  =	$obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["is_private"];
        endif;
    endif;

    if( $array["return"] == "private" ):
        return $private;
    endif;
}

function instagramCount($array){
    $type     = $array["type"];
    if( $type == "username" ):
        $profile = "https://www.instagram.com/".$array["url"];
        $search_type  = "profile";
    else:
        $profile  = $array["url"];
        $check = explode("instagram.com/",$profile);
        if( substr($check[1],0,2) == "p/" ):
            $search_type  = "photo";
        else:
            $search_type  = "profile";
        endif;
    endif;

    $html     = file_get_contents($profile);
    $arr      = explode('window._sharedData = ',$html);
    $arr      = explode(';</script>',$arr[1]);
    $obj      = json_decode($arr[0] , true);

    if( $array["search"] == "instagram_follower" ):
        $user		  =	$obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
        $count    =	$obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_followed_by"]["count"];
    else:

        $user		  =	$obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["edge_media_preview_like"]["count"];
        $count    =	$obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["edge_media_preview_like"]["count"];

    endif;
    if( !$count ):
        return 0;
    else:
        return $count;
    endif;
}

function force_download($file){
    if ((isset($file))&&(file_exists($file))) {
        header("Content-length: ".filesize($file));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile("$file");
    } else {
        echo "No file selected";
    }
}


function dayPayments($day,$ay,$year,$extra=null){
    global $conn;
    if( count($extra["methods"]) ):
        $where  = "&& ( ";
        foreach( $extra["methods"] as $method ):
            $where .= "payment_method='$method' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    $first  = $year."-".$ay."-".$day." 00:00:00";
    $last   = $year."-".$ay."-".$day." 23:59:59";
    $row    = $conn->query("SELECT SUM(payment_amount) FROM payments WHERE payment_delivery='2' && payment_status='3' && payment_create_date<='$last' && payment_create_date>='$first' $where  ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(payment_amount)'];
    return   number_format($charge,2,".",",");
}

function monthPayments($ay,$year,$extra=null){
    global $conn;
    if( count($extra["methods"]) ):
        $where  = "&& ( ";
        foreach( $extra["methods"] as $method ):
            $where .= "payment_method='$method' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    $first  = $year."-".$ay."-1 00:00:00";
    $last   = $year."-".$ay."-31 23:59:59";
    $row    = $conn->query("SELECT SUM(payment_amount) FROM payments WHERE payment_delivery='2' && payment_status='3' && payment_create_date<='$last' && payment_create_date>='$first' $where ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(payment_amount)'];
    return    number_format($charge,2,".",",");
}

function dayCharge($day,$ay,$year,$extra=null){
    global $conn;
    if( count($extra["status"]) ):
        $where  = "&& ( ";
        if( in_array("cron",$extra["status"]) ): $where .= "order_detail='cronpending' || "; endif;
        if( in_array("fail",$extra["status"]) ): $where .= "order_error!='-' || "; endif;
        foreach( $extra["status"] as $statu ):
            if( $statu != "cron" || $statu != "fail" ):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    if( count($_POST["services"]) ):
        $where .= "&& ( ";
        foreach( $extra["services"] as $service ):
            $where .= " service_id='$service' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    endif;
    $first  = $year."-".$ay."-".$day." 00:00:00";
    $last   = $year."-".$ay."-".$day." 23:59:59";
    $row    = $conn->query("SELECT SUM(order_charge) FROM orders WHERE order_create<='$last' && order_create>='$first' && dripfeed='1' && subscriptions_type='1'   $where   ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(order_charge)'];
    return   number_format($charge,2,".",",");
}

function monthCharge($month,$year,$extra=null){
    global $conn;
    if( count($extra["status"]) ):
        $where  = "&& ( ";
        if( in_array("cron",$extra["status"]) ): $where .= "order_detail='cronpending' || "; endif;
        if( in_array("fail",$extra["status"]) ): $where .= "order_error!='-' || "; endif;
        foreach( $extra["status"] as $statu ):
            if( $statu != "cron" || $statu != "fail" ):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ")";
    else:
        $where = "";
    endif;
    if( count($_POST["services"]) ):
        $where .= "&& ( ";
        foreach( $extra["services"] as $service ):
            $where .= " service_id='$service' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    endif;
    $first  = $year."-".$month."-1 00:00:00";
    $last   = $year."-".$month."-31 23:59:59";
    $row    = $conn->query("SELECT SUM(order_charge) FROM orders WHERE order_create<='$last' && order_create>='$first'  && dripfeed='1' && subscriptions_type='1' $where   ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(order_charge)'];
    return   number_format($charge,2,".",",");
}

function monthChargeNet($month,$year,$extra=null){
    global $conn;
    if( count($extra["status"]) ):
        $where  = "&& ( ";
        if( in_array("cron",$extra["status"]) ): $where .= "order_detail='cronpending' || "; endif;
        if( in_array("fail",$extra["status"]) ): $where .= "order_error!='-' || "; endif;
        foreach( $extra["status"] as $statu ):
            if( $statu != "cron" || $statu != "fail" ):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ")";
    else:
        $where = "";
    endif;
    if( count($_POST["services"]) ):
        $where .= "&& ( ";
        foreach( $extra["services"] as $service ):
            $where .= " service_id='$service' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    endif;
    $first  = $year."-".$month."-1 00:00:00";
    $last   = $year."-".$month."-31 23:59:59";
    $row    = $conn->query("SELECT SUM(order_profit) FROM orders WHERE order_create<='$last' && order_create>='$first' && dripfeed='1' && subscriptions_type='1' && order_api!='0' $where  ")->fetch(PDO::FETCH_ASSOC);
    $row2   = $conn->query("SELECT SUM(order_charge) FROM orders WHERE order_create<='$last' && order_create>='$first' && dripfeed='1' && subscriptions_type='1'  $where  ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row2['SUM(order_charge)']-$row['SUM(order_profit)'];
    return   number_format($charge,2,".",",");
}

function dayOrders($day,$month,$year,$extra=null){
    global $conn;
    if( count($extra["status"]) ):
        $where  = "&& ( ";
        if( in_array("cron",$extra["status"]) ): $where .= "order_detail='cronpending' || "; endif;
        if( in_array("fail",$extra["status"]) ): $where .= "order_error!='-' || "; endif;
        foreach( $extra["status"] as $statu ):
            if( $statu != "cron" || $statu != "fail" ):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    if( count($_POST["services"]) ):
        $where .= "&& ( ";
        foreach( $extra["services"] as $service ):
            $where .= " service_id='$service' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    endif;
    $first  = $year."-".$month."-".$day." 00:00:00";
    $last   = $year."-".$month."-".$day." 23:59:59";
    return $row    = $conn->query("SELECT order_id FROM orders WHERE order_create<='$last' && order_create>='$first' $where ")->rowCount();
}

function monthOrders($month,$year,$extra=null){
    global $conn;
    if( count($extra["status"]) ):
        $where  = "&& ( ";
        if( in_array("cron",$extra["status"]) ): $where .= "order_detail='cronpending' || "; endif;
        if( in_array("fail",$extra["status"]) ): $where .= "order_error!='-' || "; endif;
        foreach( $extra["status"] as $statu ):
            if( $statu != "cron" || $statu != "fail" ):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ")";
    else:
        $where = "";
    endif;
    if( count($_POST["services"]) ):
        $where .= "&& ( ";
        foreach( $extra["services"] as $service ):
            $where .= " service_id='$service' || ";
        endforeach;
        $where  = substr($where,0,-3);
        $where .= ") ";
    endif;
    $first  = $year."-".$month."-1 00:00:00";
    $last   = $year."-".$month."-31 23:59:59";
    return $row    = $conn->query("SELECT order_id FROM orders WHERE order_create<='$last' && order_create>='$first' $where ")->rowCount();
}

function priceFormat($price){
    $priceExplode = explode(".",$price);
    if( $priceExplode[1] )
    {
        if( strlen($priceExplode[1]) == 1 )
        {
            return $price."0";
        }else
        {
            return $price;
        }
    }else{
        return $price."";
    }
}

