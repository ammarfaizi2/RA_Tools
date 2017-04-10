<?php
date_default_timezone_set("Asia/Jakarta");
header("content-type:application/json");
class git_dw
{
	const dgit = __DIR__."/.git_dw/";
	private $master;
	public function __construct($url)
	{
		if(!isset($_COOKIE['auth'],$_COOKIE['key'])){
			exit();
		}
		is_dir(self::dgit) or mkdir(self::dgit);
		is_dir(self::dgit.'files') or mkdir(self::dgit.'files');
		if(file_exists(self::dgit.'data.json')){
			file_put_contents(self::dgit.'data.json',json_encode(array("author"=>$_COOKIE['auth'],"first_make"=>(date("Y-m-d H:i:s")),"commit"=>0,"last_commit"=>null)));
			
		}
		if(file_exists(self::dgit.'/pclzip.lib.php')){
		$a =	$this->download("https://raw.githubusercontent.com/ammarfaizi2/RA_Tools/master/pclzip.lib.php");
		if($a[0]){
			exit($this->err("Error download lib : ".$a[0]." ".$a[1]));
		}
		file_put_contents(self::dgit.'/pclzib.lib.php',$a[1]);
		}
		$this->master = rtrim(trim($url),"/")."/archive/master.zip";
		$this->data = json_decode(file_get_contents(self::dgit.'data.json'),true);
	}
	public function run()
	{
$a=$this->download($this->master);
if($a[0]){
	exit($this->err("Error download file : ".$a[0]." ".$a[1]));
}
$hash = md5($a[1]);
if($hash==$this->data['last_commit']){
	exit($this->msg("Everything up-to-date"));
}
file_put_contents(self::dgit.'files/aa_'.(++$this->data['commit']).'.zip',$a[1]);
$this->data['author'] = $_COOKIE['auth'];
$this->data['last_commit_at'] = date("Y-m-d H:i:s");
$this->data['last_commit'] = $hash;
file_put_contents(self::dgit.'data.json',json_encode($this->data));
require self::dgit.'pclzip.lib.php';
$e = new PclZip(self::dgit.'files/aa_'.(++$this->data['commit']).'.zip');
$zx = $e->extract(PCLZIP_OPT_PATH,$r,PCLZIP_OPT_REMOVE_PATH,$r);
$zx==0 and exit(
$this->err("Error on extract : ".$e->errorInfo(true))
);
exit($this->msg($this->data));
	}
	private function err($msg)
	{
		return json_encode(array("error_msg"=>$msg));
	}
	private function msg($msg)
	{
		return json_encode(array("msg"=>$msg));
	}
	private function download($url)
	{
		$ch = curl_init($url);
		$op = array(
		CURLOPT_RETURNTRANSFER=>true,
		CURLOPT_SSL_VERIFYPEER=>false,
		CURLOPT_SSL_VERIFYHOST=>false,
		CURLOPT_TIMEOUT=>500,
		CURLOPT_CONNECTTIMEOUT=>500,
		CURLOPT_COOKIE=>self::dgit.'.git_cookie',
		CURLOPT_COOKIEJAR=>self::dgit.'.git_cookie',
		CURLOPT_FOLLOWLOCATION=>true
		);
		curl_setopt_array($ch,$op);
		$out = curl_exec($ch);
		$err = curl_error($ch) and $out = $err;
		$ern = curl_errno($ch);
		curl_close($ch);
		return array($ern,$out);
	}
}
if(!isset($_POST['url'])){
	die();
}
$app = new git_dw(trim($_POST['url']));
$app->run();