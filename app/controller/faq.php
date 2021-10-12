<?php

$title .= $languageArray["faq.title"];

if( $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}
