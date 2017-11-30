<?php
define("PATH_URI",dirname(__FILE__).'/');
require_once(PATH_URI.'comm.php');

$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sesql = "select id,upfilename,type from files_up_down where type = 'shoponline' and status = 'NEW' order BY id limit 1";

$pdosel = $db->prepare($sesql);
$pdosel->execute();
$data = array();
while($row = $pdosel->fetch(PDO::FETCH_ASSOC)){
	$data[] = $row;
}
if(empty($data)){
	exit('no task');
}
$sql_filter ="SELECT * from shoponline_result where datatime = '".date('Y-m')."'";
$pdosel0 = $db->query($sql_filter,PDO::FETCH_ASSOC);
$result = array();
while($row = $pdosel0->fetch()){
    $result[$row['domain'].'-'.$row['datatime']] = "{$row['domain']}\t{$row['status']}\t{$row['words']}\t{$row['datatime']}\n";
}
foreach($data as $data){
	$senum = 0;
	$ernum = 0;
	$upd_sql0 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'PARSERED' where id=".$data['id'];
	$db->exec($upd_sql0);
	$file = PATH_REPORT.'data/'.$data['type'].'/'.$data['type'].$data['upfilename'].'.dat';
	if(!$file || !file_exists($file))
		throw new Exception('file is not exists');
	if(!file_exists(PATH_DATA.'shoponline/')){
        Directory(PATH_DATA.'shoponline/');
    }
	$wfile = PATH_DATA.'shoponline/shoponline'.date('Ymd').$mtime.'.dat';
	echo $wfile."\n";
	$fw = fopen($wfile,"w");
	$fp = fopen($file,"r");
	try {
		$mitime = explode(' ', microtime());
		$mtime = $mitime[0] * 100000000;
	    //  /app/site/reporting.soarinfotech.com/web/mermining/data/similarweb/similarweb_'.date("YmdHis").$mtime.'.dat';
		// $wfile = PATH_DATA.'shoponline/shoponline'.date('Ymd').$mtime.'.dat';
		// echo $wfile."\n";
		// $fw = fopen($wfile,"w");
		while (!feof($fp)) {
			$lr = explode("\t", trim(fgets($fp)));
			if(isset($result[$lr[0].'-'.date('Y-m')])){
				$senum++;
				fwrite($fw,$result[$lr[0].'-'.date('Y-m')]);
			}else{
				if(empty($lr[0]))
					continue;
				$words = "-";
				$status = "-";
				$uri = 'http://'.$lr[0];
				$pageContent = curl($uri);
				if(!$pageContent){
					$uri = 'https://'.$lr[0];
					$pageContent = curl($uri);
				}
				echo $uri."\n";
				if($pageContent){
					if(preg_match('/(\bcart\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|cart";
					}
					if(preg_match('/(\basket\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|basket";
					}
					if(preg_match('/(\checkout\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|checkout";
					}
					if(preg_match('/(\bshipping\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|shipping";
					}
					if(preg_match('/(\bsale\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|sale";
					}
					if(preg_match('/(\bsales\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|sales";
					}
					if(preg_match('/(\boffer\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|offer";
					}
					if(preg_match('/(\boffers\b)/i', $pageContent, $g)){
						$status = "YES";
						$words .= "|offers";
					}
					if(stripos($pageContent, "shopping bag") !== false){
						$status = "YES";
						$words .= "|shopping bag";
					}
					if(stripos($pageContent, "add to bag") !== false){
						$status = "YES";
						$words .= "|add to bag";
					}
					if(stripos($pageContent, "my bag") !== false){
						$status = "YES";
						$words .= "|my bag";
					}
					if(stripos($pageContent, "shop now") !== false){
						$status = "YES";
						$words .= "|shop now";
					}
					if(stripos($pageContent, "online store") !== false){
						$status = "YES";
						$words .= "|online store";
					}
					if(stripos($pageContent, "MasterCard") !== false){
						$status = "YES";
						$words .= "|MasterCard";
					}
					if(stripos($pageContent, "PayPal") !== false){
						$status = "YES";
						$words .= "|PayPal";
					}
					if(stripos($pageContent, "my cart") !== false){
						$status = "YES";
						$words .= "|my cart";
					}
					if(stripos($pageContent, "shipping policy") !== false){
						$status = "YES";
						$words .= "|shipping policy";
					}
					if(stripos($pageContent, "shipping & Returns") !== false){
						$status = "YES";
						$words .= "|shipping & Returns";
					}
					if(stripos($pageContent, "special offer") !== false){
						$status = "YES";
						$words .= "|special offer";
					}
					if(stripos($pageContent, "gift certificate") !== false){
						$status = "YES";
						$words .= "|gift certificate";
					}
					if(stripos($pageContent, "gift card") !== false){
						$status = "YES";
						$words .= "|gift card";
					}
					if(stripos($pageContent, "gift voucher") !== false){
						$status = "YES";
						$words .= "|gift voucher";
					}
					if(stripos($pageContent, '$') !== false){
						$status = "YES";
						$words .= '|$';
					}
					if(stripos($pageContent, "£") !== false){
						$status = "YES";
						$words .= '|£';
					}
					if(stripos($pageContent, "€") !== false){
						$status = "YES";
						$words .= "|€";
					}
					if(stripos($pageContent, "where to buy") !== false){
						$status = "NO";
						$words .= "|where to buy";
					}
					$senum++;
				}else{
					$ernum++;
				}
				//domain,status,keyword

				fwrite($fw, "{$lr[0]}\t{$status}\t{$words}\t".date('Y-m')."\n");
			}
		}
		fclose($fw);
		fclose($fp);
		//导入文件到数据库
		$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$wfile."',status = 'DOWN',senum = $senum,ernum = $ernum where id=".$data['id'];
		$sql = "LOAD DATA LOCAL INFILE '".$wfile."' REPLACE INTO TABLE shoponline_result FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' (domain,status,words,datatime)";
		if($num = $db->exec($sql)){
			echo $num."\n";
			$db->exec($upd_sql);//修改状态
			echo 'success:'.$senum."\n";
			echo 'error:'.$ernum."\n";
			unlink($file);
		}else{
			$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD',senum = $senum,ernum = $ernum where id=".$data['id'];
			$db->exec($upd_sql);//修改状态
			echo 'success:'.$senum."\n";
			echo 'error:'.$ernum."\n";
			unlink($file);
			echo 'error';
		}
	} catch(Exception $e){
		fclose($fw);
		fclose($fp);
		$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD',senum = $senum,ernum = $ernum where id=".$data['id'];
		$db->exec($upd_sql);//修改状态
		echo 'success:'.$senum."\n";
		echo 'error:'.$ernum."\n";
		unlink($file);
		printf("An error has occurred: %s\n", $e->getMessage());
		exit(1);
	}
}



function curl($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT,10);

    $out = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($httpCode != 200){
    	return false;
    }

    return $out;
}

//创建文件
function Directory($dir){
    if(is_dir($dir) || @mkdir($dir,0777)){
        //echo $dir."创建成功<br>";
    }else{
        $dirArr=explode('/',$dir);
        array_pop($dirArr);
        $newDir=implode('/',$dirArr);
        Directory($newDir);
        if(@mkdir($dir,0777)){
            // echo $dir."创建成功<br>";
        }
    }
}

?>
