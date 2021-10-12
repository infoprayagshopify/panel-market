<?php
session_start();
ob_start();
use Slim\Http\Request;
use Slim\Http\Response;
use Stripe\Stripe;

if (isset($_SESSION["developerity_userid"])) {
    require_once '../../vendor/autoload.php';
    $config = require_once '../../app/config.php';

    try {
        $conn = new PDO("mysql:host=" . $config["db"]["host"] . ";dbname=" . $config["db"]["name"] . ";charset=" . $config["db"]["charset"] . ";", $config["db"]["user"], $config["db"]["pass"]);
    }
    catch(PDOException $e) {
        die($e->getMessage());
    }

    $method = $conn->prepare("SELECT * FROM payment_methods WHERE id=:id");
    $method->execute(array("id" => 2));
    $method = $method->fetch(PDO::FETCH_ASSOC);
    $extra = json_decode($method["method_extras"], true);

    $sysset = $conn->prepare("SELECT * FROM settings WHERE id=:id");
    $sysset->execute(array("id" => 1));
    $sysset = $sysset->fetch(PDO::FETCH_ASSOC);

    $user = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
    $user->execute(array("id" => $_SESSION["developerity_userid"]));
    $user = $user->fetch(PDO::FETCH_ASSOC);

    $payments = $conn->prepare("SELECT * FROM payments WHERE client_id=:id AND payment_method=:pm AND payment_delivery=:pd ORDER BY payment_id DESC");
    $payments->execute(array("id" => $_SESSION["developerity_userid"], "pm" => 2, "pd" => 1));
    $payments = $payments->fetch(PDO::FETCH_ASSOC);

    $amount_fee = ($payments['payment_amount'] + ($payments['payment_amount'] * $extra["fee"] / 100));
    $price = str_replace(array('.',','),'',number_format($amount_fee, 2));

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

    $app = new \Slim\App;

    // Instantiate the logger as a dependency
    $container = $app->getContainer();
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new Monolog\Logger($settings['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/logs/app.log', \Monolog\Logger::DEBUG));
        return $logger;
    };

    $app->add(function ($request, $response, $next) {
        global $extra;
        Stripe::setApiKey($extra['stripe_secret_key']);
        return $next($request, $response);
    });
    
    $app->get('/', function (Request $request, Response $response, array $args) {
        return $response->write(file_get_contents('connection.html'));
    });


    $app->get('/config', function (Request $request, Response $response, array $args) {
        global $extra, $sysset, $user, $price;
        $pub_key = $extra['stripe_publishable_key'];
        $currency = mb_strtolower($sysset['currency']);
        return $response->withJson([
            'publicKey' => $pub_key,
            'basePrice' => $price,
            'currency' => $currency
        ]);
    });

    // Fetch the Checkout Session to display the JSON result on the success page
    $app->get('/checkout-session', function (Request $request, Response $response, array $args) {
        $id = $request->getQueryParams()['sessionId'];
        $checkout_session = \Stripe\Checkout\Session::retrieve($id);

        return $response->withJson($checkout_session);
    });

    $app->post('/create-checkout-session', function (Request $request, Response $response, array $args) {
        global $payments, $settings, $sysset, $user, $price;
        $currency = mb_strtolower($sysset['currency']);
        $body = json_decode($request->getBody());
        $quantity = $body->quantity;

        \Stripe\PaymentIntent::create([
            'amount' => $price,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        $checkout_session = \Stripe\Checkout\Session::create([
        'success_url' => URL,
        'cancel_url' => URL,
        'client_reference_id' => $payments['payment_extra'],
        'customer_email' => $user['email'],
        'payment_method_types' => ['card'],
        'line_items' => [[
            'name' => $sysset['site_name'],
            'images' => ["https://picsum.photos/300/300?random=4"],
            'quantity' => $quantity,
            'amount' => $price,
            'currency' => $currency
        ]]
    ]);

        return $response->withJson(array('sessionId' => $checkout_session['id']));
    });

    $app->run();
}