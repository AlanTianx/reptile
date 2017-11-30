<?php
define("PATH_URI",dirname(__FILE__).'/');
require_once(PATH_URI.'comm.php');
// require_once (dirname(__FILE__).'/comm.php');
// 

$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sesql = "select id,upfilename,type from files_up_down where type = 'bingsearch' and status = 'NEW' order BY id limit 1";

$pdosel = $db->prepare($sesql);
$pdosel->execute();
$data = array();
while($row = $pdosel->fetch(PDO::FETCH_ASSOC)){
	$data[] = $row;
}
if(empty($data)){
	exit('no task');
}
$uas = array(
"Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_8; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
"Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
"Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
"Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",
"Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)",
"Opera/9.80 (Windows NT 5.1; U; zh-cn) Presto/2.9.168 Version/11.50",
"Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0",
"Mozilla/5.0 (Windows NT 5.2) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.122 Safari/534.30",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; LBBROWSER)",
"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SV1; QQDownload 732; .NET4.0C; .NET4.0E; 360SE)",
"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.84 Safari/535.11 SE 2.X MetaSr 1.0",
"Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1",
"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SV1; QQDownload 732; .NET4.0C; .NET4.0E; SE 2.X MetaSr 1.0)",
"Opera/9.27 (Windows NT 5.2; U; zh-cn)",
"Opera/8.0 (Macintosh; PPC Mac OS X; U; en)",
"Mozilla/5.0 (Macintosh; PPC Mac OS X; U; en) Opera 8.0",
"Mozilla/5.0 (Windows; U; Windows NT 5.2) Gecko/2008070208 Firefox/3.0.1",
"Mozilla/5.0 (Windows; U; Windows NT 5.1) Gecko/20070309 Firefox/2.0.0.3",
"Mozilla/5.0 (Windows; U; Windows NT 5.1) Gecko/20070803 Firefox/1.5.0.12",
"Mozilla/4.0 (compatible; MSIE 12.0","Mozilla/5.0 (Windows NT 5.1; rv:44.0) Gecko/20100101 Firefox/44.0");
$sql_filter ="SELECT * from bingsearch_result where datatime = '".date('Y-m')."'";
	$pdosel0 = $db->query($sql_filter,PDO::FETCH_ASSOC);
	$result = array();
    while($row = $pdosel0->fetch()){
        $result[$row['domain'].'-'.$row['datatime']] = "{$row['domain']}\t{$row['kw1']}\t{$row['title1']}\t{$row['uri1']}\t{$row['kw2']}\t{$row['title2']}\t{$row['uri2']}\t{$row['kw3']}\t{$row['title3']}\t{$row['uri3']}\t{$row['datatime']}\n";
    }

foreach ($data as $data) {
	$senum = 0;
	$ernum = 0;
	$upd_sql0 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'PARSERED' where id=".$data['id'];
	$db->exec($upd_sql0);


	$uacount = rand(0,count($uas)-1);
	$referer = 'http://www.bing.com';
	$file = PATH_REPORT.'data/'.$data['type'].'/'.$data['type'].$data['upfilename'].'.dat';//执行一次文件 单独一次
	if(empty($file) || !file_exists($file))
		exit("file is empty");
	$fp = fopen($file,"r");
	$mitime = explode(" ",microtime());
	$mtime = $mitime[0]*100000000;
	$wfile = PATH_DATA.'bingsearch/bingsearch_'.date('Ymd').$mtime.'.dat';
	$temporary = PATH_DATA.'bingsearch/'.$mtime.'temporary_sim.dat';
	$fw_temporary = fopen($temporary, 'w');
	echo $wfile."\n";
	$fw = fopen($wfile,"w");
	try{
		while(!feof($fp)){
			$lr = explode("\t",trim(fgets($fp)));
			if(isset($result[$lr[0].'-'.date('Y-m')])){
				$senum++;
				fwrite($fw,$result[$lr[0].'-'.date('Y-m')]);
				//fwrite($fw_temporary,$result[$lr[0].'-'.date('Y-m')]);
			}else{
				$ua = $uas[$uacount];
			
				$ip = array("X-FORWARDED-FOR:162.225.209.105", "CLIENT-IP:162.225.209.105");
				$lr = urlencode($lr[0]);
				$url = "http://www.bing.com/search?q={$lr}&qs=n&form=QBRE&sp=-1&ghc=1&pq=ebay&sc=9-4&sk=";
				$out = curl($url,$ua,$ip,$referer);
				preg_match_all("/<h2>(.*?)(<strong>(.*?)<\/strong>)(.*?)<\/h2>/",$out,$n);
				$str = "{$lr[0]}\t";
				if($n){
					$senum++;
					foreach($n[3] as $k=>$v){
						preg_match('/(https?|ftp|file):\/\/[-A-Za-z0-9+&@#\/\%?=~_|!:,.;]+[-A-Za-z0-9+&@#\/\%=~_|]/',$n[1][$k],$uri);
						$uri = $uri[0];
						$title = $v.$n[4][$k];
						
						$title = str_ireplace('<strong>','',$title);
						$title = str_ireplace('</strong>','',$title);
						$title = str_ireplace('<a>','',$title);
						$title = str_ireplace('</a>','',$title);
						$str .= "{$v}\t{$title}\t{$uri}\t";
						if($k>=2)
							break;
					}
					if(count($n[3]) < 3){
						$m = 3-count($n[3]);
						for($i=0;$i<$m;$i++){
							$str .= "\t\t\t";
						}
					}
					$str .= date('Y-m')."\n";
					fwrite($fw,$str);
					if(!empty($n[3])){
						fwrite($fw_temporary,$str);
					}
				}else{
					$ernum++;
				}
			}
			
		}
		fclose($fw);
		fclose($fp);
		fclose($fw_temporary);
		//导入文件到数据库
		$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$wfile."',status = 'DOWN',senum = $senum,ernum = $ernum where id=".$data['id'];
		$sql = "LOAD DATA LOCAL INFILE '".$temporary."' REPLACE INTO TABLE bingsearch_result FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' (domain,kw1,title1,uri1,kw2,title2,uri2,kw3,title3,uri3,datatime)";
echo $sql;
		if($num = $db->exec($sql)){
			echo $num."\n";
			$db->exec($upd_sql);//修改状态
			echo 'success:'.$senum."\n";
			echo 'error:'.$ernum."\n";
			unlink($file);
			unlink($temporary);
		}else{
			$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD',senum = $senum,ernum = $ernum where id=".$data['id'];
			$db->exec($upd_sql);//修改状态
			echo 'success:'.$senum."\n";
			echo 'error:'.$ernum."\n";
			unlink($file);
			unlink($temporary);
			echo 'error';
		}
	}catch(Exception $e){
		fclose($fw);
		fclose($fp);
		fclose($fw_temporary);
		$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD',senum = $senum,ernum = $ernum where id=".$data['id'];
		echo 'success:'.$senum."\n";
		echo 'error:'.$ernum."\n";
		$db->exec($upd_sql);//修改状态
		unlink($file);
		unlink($temporary);
		printf("An error has occurred: %s\n", $e->getMessage());
		exit(1);
	}
}



function curl($url,$ua,$ip,$referer = '',$status = 0){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $ip);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$out = curl_exec($ch);
	// $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
	curl_close($ch);
	return $out;
}
