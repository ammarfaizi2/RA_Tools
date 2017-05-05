<?php
require __DIR__.'/vendor/autoload.php';
use Curl\CMCurl;
$a = new CMCurl("https://m.facebook.com");
print $a->execute();