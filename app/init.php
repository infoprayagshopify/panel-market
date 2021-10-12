<?php

session_start();
ob_start();

$config = require __DIR__.'/config.php';

try {
  $conn = new PDO("mysql:host=".$config["db"]["host"].";dbname=".$config["db"]["name"].";charset=".$config["db"]["charset"].";", $config["db"]["user"], $config["db"]["pass"] );
} catch (PDOException $e) {
  die($e->getMessage());
}

if( $_COOKIE["u_id"] && $_COOKIE["u_login"] && $_COOKIE["u_password"] ):

  $row      = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
  $row      ->execute(array("id"=>$_COOKIE["u_id"] ));
  $row      = $row->fetch(PDO::FETCH_ASSOC);
  $access   = json_decode($row["access"],true);
  $password = $row["password"];

  if( @$_COOKIE["u_password"] == $password ):
    $_SESSION["msmbilisim_userlogin"]      = 1;
    $_SESSION["msmbilisim_userid"]         = $row["client_id"];
    $_SESSION["msmbilisim_userpass"]       = $row["password"];
      if( $access["admin_access"] ):
        $_SESSION["msmbilisim_adminlogin"] = 1;
      endif;
  else:
    unset($_SESSION["msmbilisim_userlogin"]);
    unset($_SESSION["msmbilisim_userid"]);
    unset($_SESSION["msmbilisim_userpass"]);
    unset($_SESSION["msmbilisim_adminlogin"]);
    unset($_SESSION);
    setcookie("u_id", $row["client_id"], time()-(60*60*24*7), '/', null, null, true );
    setcookie("u_password", $row["password"], time()-(60*60*24*7), '/', null, null, true );
    setcookie("u_login", 'ok', time()-(60*60*24*7), '/', null, null, true );
    session_destroy();
  endif;

endif;



$settings = $conn->prepare("SELECT * FROM settings WHERE id=:id");
$settings->execute(array("id"=>1));
$settings = $settings->fetch(PDO::FETCH_ASSOC);

define('THEME', $settings["site_theme"]);

$loader   = new Twig_Loader_Filesystem(__DIR__.'/views/'.THEME);
$twig     = new Twig_Environment($loader, ['autoescape' => false]);

$user = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
$user->execute(array("id"=>$_SESSION["msmbilisim_userid"] ));
$user = $user->fetch(PDO::FETCH_ASSOC);
$user['auth']     = $_SESSION["msmbilisim_userlogin"];
$user["access"]   = json_decode($user["access"],true);


foreach ( glob(__DIR__.'/helper/*.php') as $helper ) {
  require $helper;
}

foreach ( glob(__DIR__.'/classes/*.php') as $class ) {
  require $class;
}


$timezones  = [
  ["label"=>"(UTC -12:00) Baker/Howland Island","timezone"=>"-54000"],
  ["label"=>"(UTC -11:00) Niue","timezone"=>"-50400"],
  ["label"=>"(UTC -10:00) Hawaii-Aleutian Standard Time, Cook Islands, Tahiti","timezone"=>"-46800"],
  ["label"=>"(UTC -9:30) Marquesas Islands","timezone"=>"-45000"],
  ["label"=>"(UTC -9:00) Alaska Standard Time, Gambier Islands","timezone"=>"-43200"],
  ["label"=>"(UTC -8:00) Pacific Standard Time, Clipperton Island","timezone"=>"-39600"],
  ["label"=>"(UTC -7:00) Mountain Standard Time","timezone"=>"-36000"],
  ["label"=>"(UTC -6:00) Central Standard Time","timezone"=>"-32400"],
  ["label"=>"(UTC -5:00) Eastern Standard Time, Western Caribbean Standard Time","timezone"=>"-28800"],
  ["label"=>"(UTC -4:30) Venezuelan Standard Time","timezone"=>"-27000"],
  ["label"=>"(UTC -4:00) Atlantic Standard Time, Eastern Caribbean Standard Time","timezone"=>"-25200"],
  ["label"=>"(UTC -3:30) Newfoundland Standard Time","timezone"=>"-23400"],
  ["label"=>"(UTC -3:00) Argentina, Brazil, French Guiana, Uruguay","timezone"=>"-21600"],
  ["label"=>"(UTC -2:00) South Georgia/South Sandwich Islands","timezone"=>"-18000"],
  ["label"=>"(UTC -1:00) Azores, Cape Verde Islands","timezone"=>"-14400"],
  ["label"=>"(UTC) Greenwich Mean Time, Western European Time","timezone"=>"-10800"],
  ["label"=>"(UTC +1:00) Central European Time, West Africa Time","timezone"=>"-7200"],
  ["label"=>"(UTC +2:00) Central Africa Time, Eastern European Time, Kaliningrad Time","timezone"=>"-3600"],
  ["label"=>"(UTC +3:00) Moscow Time, East Africa Time, Arabia Standard Time","timezone"=>"0"],
  ["label"=>"(UTC +3:30) Iran Standard Time","timezone"=>"1800"],
  ["label"=>"(UTC +4:00) Azerbaijan Standard Time, Samara Time","timezone"=>"3600"],
  ["label"=>"(UTC +4:30) Afghanistan","timezone"=>"5400"],
  ["label"=>"(UTC +5:00) Pakistan Standard Time, Yekaterinburg Time","timezone"=>"7200"],
  ["label"=>"(UTC +5:30) Indian Standard Time, Sri Lanka Time","timezone"=>"9000"],
  ["label"=>"(UTC +5:45) Nepal Time","timezone"=>"9900"],
  ["label"=>"(UTC +6:00) Bangladesh Standard Time, Bhutan Time, Omsk Time","timezone"=>"10800"],
  ["label"=>"(UTC +6:30) Cocos Islands, Myanmar","timezone"=>"12600"],
  ["label"=>"(UTC +7:00) Krasnoyarsk Time, Cambodia, Laos, Thailand, Vietnam","timezone"=>"14400"],
  ["label"=>"(UTC +8:00) Australian Western Standard Time, Beijing Time, Irkutsk Time","timezone"=>"18000"],
  ["label"=>"(UTC +8:45) Australian Central Western Standard Time","timezone"=>"20700"],
  ["label"=>"(UTC +9:00) Japan Standard Time, Korea Standard Time, Yakutsk Time","timezone"=>"21600"],
  ["label"=>"(UTC +9:30) Australian Central Standard Time","timezone"=>"23400"],
  ["label"=>"(UTC +10:00) Australian Eastern Standard Time, Vladivostok Time","timezone"=>"25200"],
  ["label"=>"(UTC +10:30) Lord Howe Island","timezone"=>"27000"],
  ["label"=>"(UTC +11:00) Srednekolymsk Time, Solomon Islands, Vanuatu","timezone"=>"28800"],
  ["label"=>"(UTC +11:30) Norfolk Island","timezone"=>"30600"],
  ["label"=>"(UTC +12:00) Fiji, Gilbert Islands, Kamchatka Time, New Zealand Standard Time","timezone"=>"32400"],
  ["label"=>"(UTC +12:45) Chatham Islands Standard Time","timezone"=>"35100"],
  ["label"=>"(UTC +13:00) Samoa Time Zone, Phoenix Islands Time, Tonga","timezone"=>"36000"],
  ["label"=>"(UTC +14:00) Line Islands","timezone"=>"39600"]
];
