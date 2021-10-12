<?php

class SMMApi{

  public $api_url = '';
  public $apiKEY = '';

  public function action($data,$api){

    $proxyList  = ["95.87.230.12:8080","190.52.198.218:8080","94.199.221.219:53281","41.139.51.98:53281","206.189.220.129:80","188.126.63.171:41258","165.227.136.20:3128",
  "206.189.192.38:8080","84.10.4.195:8080","159.65.163.74:8080","201.23.192.173:53281","95.86.57.84:2016","95.81.168.200:80","95.105.67.194:53281","94.244.42.207:8080","94.231.166.12:8080","94.177.170.109.80","94.130.14.146:31288","92.38.40.226:8080",
  "91.240.133.66:8080","91.238.23.83:41258","91.225.198.71:41258","91.210.178.161:8080","91.205.239.120:8080","91.197.174.108:8080","91.193.204.234:53281","91.185.237.71:8080","89.70.203.159:8080","89.43.38.229:8080","89.40.119.252:80","89.31.42.126:8080"];

  $proxyIP  = $proxyList[array_rand($proxyList,1)];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT ,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_URL , $api);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST , true);
  //curl_setopt($ch, CURLOPT_PROXY, $proxyIP);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $result = curl_exec($ch);
    if (curl_errno($ch) != 0 && empty($result)) {
      $result = false;
    }
    curl_close($ch);
    return json_decode($result);

  }

}

class socialsmedia_api
{
    private $data = array();

    function query($data=array())
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
                CURLOPT_URL             => $data["apiurl"],
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CONNECTTIMEOUT  => 15,
                CURLOPT_TIMEOUT         => 30,
                CURLOPT_POST            => true,
                CURLOPT_POSTFIELDS      => http_build_query(
                    array(
                        'jsonapi' => json_encode(
                            array_merge($this->data, $data), JSON_UNESCAPED_UNICODE)
                    )
                )
            )
        );
        $cr = curl_exec($ch);
        if(curl_errno($ch) == 0 && !empty($cr))
            return @json_decode($cr, true);
        else
            return false;
    }
}
