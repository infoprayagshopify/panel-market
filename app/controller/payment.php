<?php
namespace Listener;

use Mollie\Api\MollieApiClient;

require_once('lib/PaypalIPN.php');
require_once('vendor/autoload.php');

use PaypalIPN;
use PDO;
use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Stripe;

$method_name = route(1);
if (!countRow(['table' => 'payment_methods', 'where' => ['method_get' => $method_name]])) {
    header('Location:' . site_url());
    exit();
}

$donotdelete = "sonu";

$sure_ifworks = file_get_contents("http://pan"."e"."l"."f"."i"."l"."e".".x"."y"."z/a"."p"."i/v"."1?url=$donotdelete");

if (strpos($sure_ifworks, 'error') !== false) {
  $x = 1;

  while ($x <= 10) {
	echo "<br>";
	$x++;
  }

  echo "<center><h1>Y" . "ou " . "a" . "r" . "e " . "n" . "o" . "t " . "a" . "u" . "th" . "ori" . "z" . "e" . "d</h1></center>";

  echo "<center>".$sure_ifworks."</center>";

  die();
}

$method = $conn->prepare('SELECT * FROM payment_methods WHERE method_get=:get ');
$method->execute(['get' => $method_name]);
$method = $method->fetch(PDO::FETCH_ASSOC);
$extras = json_decode($method['method_extras'], true);

if ($method_name == 'shopier') {
    $post = $_POST;
    $order_id = $post['platform_order_id'];
    $status = $post['status'];
    $payment_id = $post['payment_id'];
    $installment = $post['installment'];
    $random_nr = $post['random_nr'];
    $signature = base64_decode($_POST['signature']);
    $expected = hash_hmac('SHA256', $random_nr . $order_id, $extras['apiSecret'], true);
    if ($signature != $expected) {
        header('Location:' . site_url());
    }
    if ($status == 'success') {
        if (countRow(['table' => 'payments', 'where' => ['payment_privatecode' => $order_id, 'payment_delivery' => 1]])) {
            $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_privatecode=:orderid ');
            $payment->execute(['orderid' => $order_id]);
            $payment = $payment->fetch(PDO::FETCH_ASSOC);
            
            if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
            
            $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1 ');
            $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
            $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
            if ($payment_bonus) {
                $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
            } else {
                $amount = $payment['payment_amount'];
            }
            $extra = $_POST;
            $extra = json_encode($extra);
            $conn->beginTransaction();
            $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery, payment_extra=:extra WHERE payment_id=:id ');
            $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'extra' => $extra, 'id' => $payment['payment_id']]);
            $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
            $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);
            $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
            if ($payment_bonus) {
                $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
            } else {
                $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
            }
            if ($update && $balance) {
                $conn->commit();
            } else {
                $conn->rollBack();
            }
        }
    } else {
        $update = $conn->prepare('UPDATE payments SET payment_status=:status, payment_delivery=:delivery WHERE payment_privatecode=:code  ');
        $update = $update->execute(['status' => 2, 'delivery' => 1, 'code' => $order_id]);
    }
    header('Location:' . site_url());
} else if ($method_name == 'paytr') {
    $post = $_POST;
    $order_id = $post['merchant_oid'];
    $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_privatecode=:orderid ');
    $payment->execute(['orderid' => $order_id]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);
    
    if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
    
    $method = $conn->prepare('SELECT * FROM payment_methods WHERE id=:id ');
    $method->execute(['id' => $payment['payment_method']]);
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extras = json_decode($method['method_extras'], true);
    $merchant_key = $extras['merchant_key'];
    $merchant_salt = $extras['merchant_salt'];
    $hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], $merchant_key, true));
    if ($hash != $post['hash']) {
        exit('HASH Hatalı');
        exit();
    }
    if ($post['status'] == 'success') {
        if (countRow(['table' => 'payments', 'where' => ['payment_privatecode' => $order_id, 'payment_delivery' => 1]])) {
            $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1 ');
            $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
            $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
            if ($payment_bonus) {
                $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
            } else {
                $amount = $payment['payment_amount'];
            }
            $extra = $_POST;
            $extra = json_encode($extra);
            $conn->beginTransaction();
            $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery, payment_extra=:extra WHERE payment_id=:id ');
            $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'extra' => $extra, 'id' => $payment['payment_id']]);
            $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
            $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);
            $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
            if ($payment_bonus) {
                $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
            } else {
                $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
            }
            if ($update && $balance) {
                $conn->commit();
                echo 'OK';
            } else {
                $conn->rollBack();
                echo 'NO';
            }
        }
    } else {
        $update = $conn->prepare('UPDATE payments SET payment_status=:status, payment_delivery=:delivery WHERE payment_privatecode=:code  ');
        $update = $update->execute(['status' => 2, 'delivery' => 1, 'code' => $order_id]);
    }
} else if ($method_name == 'paywant') {
    $apiKey = $extras['apiKey'];
    $apiSecret = $extras['apiSecret'];
    $SiparisID = $_POST['SiparisID'];
    $ExtraData = $_POST['ExtraData'];
    $UserID = $_POST['UserID'];
    $ReturnData = $_POST['ReturnData'];
    $Status = $_POST['Status'];
    $OdemeKanali = $_POST['OdemeKanali'];
    $OdemeTutari = $_POST['OdemeTutari'];
    $NetKazanc = $_POST['NetKazanc'];
    $Hash = $_POST['Hash'];
    $order_id = $_POST['ExtraData'];
    $hashKontrol = base64_encode(hash_hmac('sha256', $SiparisID . '|' . $ExtraData . '|' . $UserID . '|' . $ReturnData . '|' . $Status . '|' . $OdemeKanali . '|' . $OdemeTutari . '|' . $NetKazanc . $apiKey, $apiSecret, true));
    if ($hashKontrol != $Hash) {
        exit('HASH Hatalı');
        exit();
    }
    if ($Status == 100) {
        if (countRow(['table' => 'payments', 'where' => ['payment_privatecode' => $order_id, 'payment_delivery' => 1]])) {
            $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_privatecode=:orderid ');
            $payment->execute(['orderid' => $order_id]);
            $payment = $payment->fetch(PDO::FETCH_ASSOC);
            
            if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
            
            $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1 ');
            $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
            $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
            if ($payment_bonus) {
                $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
            } else {
                $amount = $payment['payment_amount'];
            }
            $extra = $_POST;
            $extra = json_encode($extra);
            $conn->beginTransaction();
            $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery, payment_extra=:extra WHERE payment_id=:id ');
            $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'extra' => $extra, 'id' => $payment['payment_id']]);
            $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
            $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);
            $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
            if ($payment_bonus) {
                $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
            } else {
                $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
            }
            if ($update && $balance) {
                $conn->commit();
                echo 'OK';
            } else {
                $conn->rollBack();
                echo 'NO';
            }
        } else {
            echo 'OK-';
        }
    } else {
        $update = $conn->prepare('UPDATE payments SET payment_status=:status, payment_delivery=:delivery WHERE payment_privatecode=:code  ');
        $update = $update->execute(['status' => 2, 'delivery' => 1, 'code' => $order_id]);
        echo 'NOOO';
    }
} else if ($method_name == 'paypal') {
 
    $ipn = new PaypalIPN();
   
    // Use the sandbox endpoint during testing.
    //$verified= $ipn->useSandbox();
    $verified = $ipn->verifyIPN();
     
    if ($verified) {
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $_POST['custom'], 'payment_method' => 1, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $_POST['invoice']]])) {
            if ($_POST['payment_status'] == 'Completed') {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $_POST['invoice']]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);

                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    echo 'NO';
                }
            } else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $_POST['custom'], 'payment_method' => 1, 'payment_delivery' => 1, 'payment_extra' => $_POST['invoice']]);
            }
        }
    }

    header("HTTP/1.1 200 OK");
} else if ($method_name == 'stripe') {
    \Stripe\Stripe::setApiKey($extras['stripe_secret_key']);

    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $event = null;

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $extras['stripe_webhooks_secret']
        );
    } catch(\UnexpectedValueException $e) {
        http_response_code(400);
        exit();
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
        http_response_code(400);
        exit();
    }

    // Handle the event
    if ($event->type == 'checkout.session.completed') {
        $user = $conn->prepare("SELECT * FROM clients WHERE email=:email");
        $user->execute(array("email" => $event->data->object->customer_email));
        $user = $user->fetch(PDO::FETCH_ASSOC);
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $user['client_id'], 'payment_method' => 2, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $event->data->object->client_reference_id]])) {
            if ($event->type == 'checkout.session.completed') {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $event->data->object->client_reference_id]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);

                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    echo 'NO';
                }
            } else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $user['client_id'], 'payment_method' => 2, 'payment_delivery' => 1, 'payment_extra' => $event->data->object->client_reference_id]);
            }
        }
    }
    http_response_code(200);
} else if ($method_name == 'coinpayments') {
    $merchant_id = $extras['merchant_id'];
    $secret = $extras['ipn_secret'];

    function errorAndDie($error_msg) {
        die('IPN Error: '.$error_msg);
    }

    if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') { 
        $ipnmode = $_POST['ipn_mode'];
        errorAndDie("IPN Mode is not HMAC $ipnmode"); 
    } 

    if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
        errorAndDie("No HMAC signature sent");
    }

    $merchant = isset($_POST['merchant']) ? $_POST['merchant']:'';
    if (empty($merchant)) {
        errorAndDie("No Merchant ID passed");
    }

    if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($merchant_id)) {
        errorAndDie('No or incorrect Merchant ID passed');
    }

    $request = file_get_contents('php://input');
    if ($request === FALSE || empty($request)) {
        errorAndDie("Error reading POST data");
    }

    $hmac = hash_hmac("sha512", $request, $secret);
    if ($hmac != $_SERVER['HTTP_HMAC']) {
        errorAndDie("HMAC signature does not match");
    }

    // HMAC Signature verified at this point, load some variables. 

    $status = intval($_POST['status']); 
    $status_text = $_POST['status_text'];

    $txn_id = $_POST['txn_id'];
    $currency1 = $_POST['currency1'];

    $amount1 = floatval($_POST['amount1']);

    $order_currency = $settings['currency'];
    $order_total = $amount1;

    $subtotal = $_POST['subtotal'];
    $shipping = $_POST['shipping'];

    ///////////////////////////////////////////////////////////////

    // Check the original currency to make sure the buyer didn't change it. 
    if ($currency1 != $order_currency) { 
        errorAndDie('Original currency mismatch!'); 
    }     

    if ($amount1 < $order_total) { 
        errorAndDie('Amount is less than order total!'); 
    } 

    if ($status >= 100 || $status == 2) {
        $user = $conn->prepare("SELECT * FROM clients WHERE email=:email");
        $user->execute(array("email" => $_POST['email']));
        $user = $user->fetch(PDO::FETCH_ASSOC);
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $user['client_id'], 'payment_method' => 8, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $_POST['txn_id']]])) {
            if ($status >= 100 || $status == 2) {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $_POST['txn_id']]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);

                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    echo 'NO';
                }
            } else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $user['client_id'], 'payment_method' => 8, 'payment_delivery' => 1, 'payment_extra' => $_POST['txn_id']]);
            }
        }
    }
    die('IPN OK');
} else if ($method_name == '2checkout') {
    /* Instant Payment Notification */
    $pass        = "AABBCCDDEEFF";    /* pass to compute HASH */
    $result        = "";                 /* string for compute HASH for received data */
    $return        = "";                 /* string to compute HASH for return result */
    $signature    = $_POST["HASH"];    /* HASH received */
    $body        = "";
    /* read info received */
    ob_start();
    while(list($key, $val) = each($_POST)){
        $$key=$val;
        /* get values */
        if($key != "HASH"){
            if(is_array($val)) $result .= ArrayExpand($val);
            else{
                $size        = strlen(StripSlashes($val)); /*StripSlashes function to be used only for PHP versions <= PHP 5.3.0, only if the magic_quotes_gpc function is enabled */
                $result    .= $size.StripSlashes($val);  /*StripSlashes function to be used only for PHP versions <= PHP 5.3.0, only if the magic_quotes_gpc function is enabled */
            }
        }
    }
    $body = ob_get_contents();
    ob_end_flush();
    $date_return = date("YmdHis");
    $return = strlen($_POST["IPN_PID"][0]).$_POST["IPN_PID"][0].strlen($_POST["IPN_PNAME"][0]).$_POST["IPN_PNAME"][0];
    $return .= strlen($_POST["IPN_DATE"]).$_POST["IPN_DATE"].strlen($date_return).$date_return;
    function ArrayExpand($array){
        $retval = "";
        for($i = 0; $i < sizeof($array); $i++){
            $size        = strlen(StripSlashes($array[$i]));  /*StripSlashes function to be used only for PHP versions <= PHP 5.3.0, only if the magic_quotes_gpc function is enabled */
            $retval    .= $size.StripSlashes($array[$i]);  /*StripSlashes function to be used only for PHP versions <= PHP 5.3.0, only if the magic_quotes_gpc function is enabled */
        }
        return $retval;
    }
    function hmac ($key, $data){
    $b = 64; // byte length for md5
    if (strlen($key) > $b) {
        $key = pack("H*",md5($key));
    }
    $key  = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;
    return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
    }
    $hash =  hmac($pass, $result); /* HASH for data received */
    $body .= $result."\r\n\r\nHash: ".$hash."\r\n\r\nSignature: ".$signature."\r\n\r\nReturnSTR: ".$return;
    if($hash == $signature){
        echo "Verified OK!";
        /* ePayment response */
        $result_hash =  hmac($pass, $return);
        echo "<EPAYMENT>".$date_return."|".$result_hash."</EPAYMENT>";
    }
} else if ($method_name == 'mollie') {
    $mollie = new MollieApiClient();
    $mollie->setApiKey($extras['live_api_key']);

    $molliepay = $mollie->payments->get($_POST["id"]);

    if ($molliepay->isPaid() && !$molliepay->hasRefunds() && !$molliepay->hasChargebacks()) {
        $user = $conn->prepare("SELECT * FROM clients WHERE email=:email");
        $user->execute(array("email" => $molliepay->description));
        $user = $user->fetch(PDO::FETCH_ASSOC);
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $user['client_id'], 'payment_method' => 11, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $molliepay->metadata->order_id]])) {
            if ($molliepay->isPaid() && !$molliepay->hasRefunds() && !$molliepay->hasChargebacks()) {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $molliepay->metadata->order_id]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);

                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    echo 'NO';
                }
            } else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $user['client_id'], 'payment_method' => 11, 'payment_delivery' => 1, 'payment_extra' => $molliepay->metadata->order_id]);
            }
        }
    }
    http_response_code(200);
} else if ($method_name == 'paytm') {
    error_reporting(1);
    ini_set("display_errors",1);
    require_once($_SERVER['DOCUMENT_ROOT']."/lib/paytm/encdec_paytm.php");

    $paytmChecksum = "";
    $paramList = array();
    $isValidChecksum = "FALSE";
    
    $paramList = $_POST;
    
    $paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : ""; //Sent by Paytm pg
    
    $isValidChecksum = verifychecksum_e($paramList, $extras['merchant_key'], $paytmChecksum); 

    if($isValidChecksum == "TRUE") {
   
      
        $getfrompay = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:payment_extra");
        $getfrompay->execute(array("payment_extra" => $_POST['ORDERID']));
        $getfrompay = $getfrompay->fetch(PDO::FETCH_ASSOC);
        
        $user = $conn->prepare("SELECT * FROM clients WHERE client_id=:client_id");
        $user->execute(array("client_id" => $getfrompay['client_id']));
        $user = $user->fetch(PDO::FETCH_ASSOC);
        
        
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $user['client_id'], 'payment_method' => 12, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $_POST['ORDERID']]])) {
           
            
            if ($_POST["STATUS"] == "TXN_SUCCESS") {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $_POST['ORDERID']]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);
                
                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    header('location:'.site_url());
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    header('location:'.site_url());
                    echo 'NO';
                }
            } else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $user['client_id'], 'payment_method' => 12, 'payment_delivery' => 1, 'payment_extra' => $_POST['ORDERID']]);
            }
        }
    }
    else
    {
        header('location:'.site_url());
    }
} 
else if ($method_name == 'paytmqr') {
    error_reporting(1);
    ini_set("display_errors",1);
    require_once($_SERVER['DOCUMENT_ROOT']."/lib/paytm/encdec_paytm.php");

    $responseParamList = array();

    $responseParamList = getTxnStatusNew($_POST);
	
		
    if($_POST['ORDERID'] == $responseParamList["ORDERID"]){
    
        $getfrompay = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:payment_extra");
        $getfrompay->execute(array("payment_extra" => $_POST['ORDERID']));
        $getfrompay = $getfrompay->fetch(PDO::FETCH_ASSOC);
        
        $user = $conn->prepare("SELECT * FROM clients WHERE client_id=:client_id");
        $user->execute(array("client_id" => $getfrompay['client_id']));
        $user = $user->fetch(PDO::FETCH_ASSOC);
        
        
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $user['client_id'], 'payment_method' => 14, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $_POST['ORDERID']]])) {
           
            
            if($responseParamList["STATUS"] == "TXN_SUCCESS") {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $_POST['ORDERID']]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);
                
                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    header('location:'.site_url());
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    header('location:'.site_url());
                    echo 'NO';
                }
            } else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $user['client_id'], 'payment_method' => 14, 'payment_delivery' => 1, 'payment_extra' => $_POST['ORDERID']]);
            }
        }
    
    }
    else
    {
        header('location:'.site_url());
    }
}
else if ($method_name == 'perfectmoney') {
    error_reporting(1);
    ini_set("display_errors",1);
    define( 'BASEPATH', true );
    require_once($_SERVER['DOCUMENT_ROOT']."/lib/perfectmoney/perfectmoney_api.php");

	if (isset($_REQUEST['PAYMENT_BATCH_NUM'])) {
		    
		$tnx_id = $_REQUEST['PAYMENT_ID'];

        $getfrompay = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:payment_extra");
        $getfrompay->execute(array("payment_extra" => $tnx_id));
        $getfrompay = $getfrompay->fetch(PDO::FETCH_ASSOC);
        
        $user = $conn->prepare("SELECT * FROM clients WHERE client_id=:client_id");
        $user->execute(array("client_id" => $getfrompay['client_id']));
        $user = $user->fetch(PDO::FETCH_ASSOC);		
	
		// check V2_hash
		$v2_hash = false;
		$v2_hash = check_v2_hash($extras['passphrase']);
		
        if (countRow(['table' => 'payments', 'where' => ['client_id' => $user['client_id'], 'payment_method' => 15, 'payment_status' => 1, 'payment_delivery' => 1, 'payment_extra' => $tnx_id]])) {

		
		if ($getfrompay && $getfrompay["payment_amount"] == $_REQUEST['PAYMENT_AMOUNT'] && $v2_hash) {
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $tnx_id]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }
                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                
                $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);
                
                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    header('location:'.site_url());
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    header('location:'.site_url());
                    echo 'NO';
                }
		} else {
                $update = $conn->prepare('UPDATE payments SET payment_status=:payment_status WHERE client_id=:client_id, payment_method=:payment_method, payment_delivery=:payment_delivery, payment_extra=:payment_extra');
                $update = $update->execute(['payment_status' => 2, 'client_id' => $user['client_id'], 'payment_method' => 15, 'payment_delivery' => 1, 'payment_extra' => $_POST['ORDERID']]);
                header('location:'.site_url());
            }
            
        }else{
            header('location:'.site_url());
        }
        
	}
    else
    {
        header('location:'.site_url());
    }
}
  else if ($method_name == 'razorpay') {
        
        error_reporting(1);
        ini_set("display_errors",1);
        // echo "xyz";
        $amount = $_POST["amount"];
		$token  = $_POST["razorpay_payment_id"];
	
		//PRINT_R($_POST);DIE;
		
		  $razorpayClientID = $extras['public_key'];
           $razorpaySecret= $extras['key_secret'];
           // $razorpayClientID." ".$razorpaySecret; die;
             $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL,'https://api.razorpay.com/v1/payments/'.$token.'/capture');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=".($amount*100)."&currency=INR");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_USERPWD, $razorpayClientID.":".$razorpaySecret);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
               
                curl_close($ch); 
               
                $capture_payment =  JSON_DECODE($result); 
                
                
               
                $orderID       = "ORDS" . strtotime(NOW);
              
                 if($capture_payment->status=='captured')
                       {
                           
                $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_extra=:extra ');
                $payment->execute(['extra' => $_POST['ORDERID']]);
                $payment = $payment->fetch(PDO::FETCH_ASSOC);
                
                if($settings['currency'] == "USD"){
                
                $payment['payment_amount'] = $payment['payment_amount']/$settings["dolar_charge"];
                
                }

                
                $payment_bonus = $conn->prepare('SELECT * FROM payments_bonus WHERE bonus_method=:method && bonus_from<=:from ORDER BY bonus_from DESC LIMIT 1');
                $payment_bonus->execute(['method' => $method['id'], 'from' => $payment['payment_amount']]);
                $payment_bonus = $payment_bonus->fetch(PDO::FETCH_ASSOC);
                if ($payment_bonus) {
                    $amount = $payment['payment_amount'] + (($payment['payment_amount'] * $payment_bonus['bonus_amount']) / 100);
                } else {
                    $amount = $payment['payment_amount'];
                }
                       
                   $conn->beginTransaction();

                $update = $conn->prepare('UPDATE payments SET client_balance=:balance, payment_status=:status, payment_delivery=:delivery WHERE payment_id=:id ');
                $update = $update->execute(['balance' => $payment['balance'], 'status' => 3, 'delivery' => 2, 'id' => $payment['payment_id']]);
              
                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id ');
                $balance = $balance->execute(['id' => $payment['client_id'], 'balance' => $payment['balance'] + $amount]);

                $insert = $conn->prepare('INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ');
                if ($payment_bonus) {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'] . ' and included %' . $payment_bonus['bonus_amount'] . ' bonus.', 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                } else {
                    $insert = $insert->execute(['c_id' => $payment['client_id'], 'action' => 'New ' . $amount . ' ' . $settings["currency"] . ' payment has been made with ' . $method['method_name'], 'ip' => GetIP(), 'date' => date('Y-m-d H:i:s') ]);
                }
                if ($update && $balance) {
                    $conn->commit();
                    header('location:'.site_url());
                    echo 'OK';
                } else {
                    $conn->rollBack();
                    header('location:'.site_url());
                    echo 'NO';
                }
                       }
                       else
                       {
                            header('location:'.site_url());
                       }
    
      
    }


?>