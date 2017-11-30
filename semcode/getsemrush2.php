<?php
define("PATH_URI",dirname(__FILE__).'/');
require_once(PATH_URI.'comm.php');
$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT id FROM files_up_down WHERE type = 'semrushK' AND status = 'COMPLETE' LIMIT 1";
$result = $db->query($sql,PDO::FETCH_ASSOC)->fetch();
if(empty($result)){
	exit('no task');
}
$fileid = $result['id'];
$sql1 = "SELECT * FROM domain_file_down_url WHERE file_id = $fileid AND status = 'NEW'";
$result1 = $db->query($sql1,PDO::FETCH_ASSOC);
$datas = array();
while ($row = $result1->fetch()) {
	$datas[] = $row;
}
if(empty($datas)){
	exit('no task');
}
//$tablename = 'semrush_result'.$fileid;
// $sql2 = "CREATE TABLE IF NOT EXISTS `{$tablename}` (
//   `Domain` char(128) NOT NULL DEFAULT '',
//   `CompetitorRelevance` char(64) DEFAULT '',
//   `CommonKeywords` char(64) DEFAULT '',
//   `OrganicKeywords` char(64) DEFAULT '',
//   `OrganicTraffic` char(64) DEFAULT '',
//   `OrganicCost` char(64) DEFAULT '',
//   `AdwordsKeywords` char(64) DEFAULT '',
//   PRIMARY KEY (`Domain`)
// ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
// //$sqllock = "LOCK TABLES `".$tablename."` write;";
// $db->exec($sql2);
//$db->exec($sqllock);
$strnodown = "";
$body = "keywords\tDomain\tDate\tPosition\tUrl\tTitle\tDescription\tVisible Url\n";
$mitime = explode(" ",microtime());
$mtime = $mitime[0]*100000000;
$downname = PATH_DATA.'semrush/semrush_keywords_'.date("YmdHis").$mtime.'.dat';
$fw = fopen($downname,'w');
fwrite($fw, $body);
$senum = 0;
$ernum = 0;
foreach ($datas as $data) {
	$url = $data['url'];
	$sql3 = "UPDATE domain_file_down_url SET status = 'PARSERED' WHERE id = {$data['id']}";
	$s = $db->exec($sql3);
	if(!$s){
		$ernum++;
		$strnodown .= $data['domain']."\n";
		echo 'error1:the '.$data['id'].'-'.$data['domain'].' update status faild'."\n";
		$sql5 = "UPDATE domain_file_down_url SET status = 'FAILD' WHERE id = {$data['id']}";
		$db->exec($sql5);
		continue;
	}
	
	$content = get_content('http:'.$url);
	//echo $content;
	if(empty($content)){
		$ernum++;
		$strnodown .= $data['domain']."\n";
		echo 'error2:the '.$data['id'].'-'.$data['domain'].' down faild'."\n";
		$sql4 = "UPDATE domain_file_down_url SET status = 'FAILD' WHERE id = {$data['id']}";
		$db->exec($sql4);
		continue;
	}else{
		$senum++;
		$sql0 = "UPDATE domain_file_down_url SET status = 'DOWN' WHERE id = {$data['id']}";
		$db->exec($sql0);
	}
	$arr = explode("\n",$content);
	$ym1 = date("Ym", strtotime("-1 month"));
	$ym2 = date("Ym", strtotime("-2 month"));
	array_shift($arr);
	foreach ($arr as $value) {
		$arr1 = explode('","',$value);
		if(trim($arr1[1],'"')==$ym1.'15'){
			$arr1[4] = trim($arr1[4],'"');
			$arr1[5] = trim($arr1[5],'"');
			$arr1[6] = trim($arr1[6],'"');
			$arr1[7] = trim($arr1[7],'"');
			if(ord($arr1[7])==13){
				$arr1[7]='';
			}
			if(!empty($arr1[4])||!empty($arr1[5])||!empty($arr1[6])||!empty($arr1[7])){
				$a = true;
				$body = $data['domain']."\t".trim($arr1[0],'"')."\t".trim($arr1[1],'"')."\t".trim($arr1[2],'"')."\t".$arr1[4]."\t".$arr1[5]."\t".$arr1[6]."\t".$arr1[7]."\n";
				fwrite($fw,$body);
			}else{
				$a = false;
			}
		}
		if(trim($arr1[1],'"')==$ym2.'15'&&$a==false){
			$arr1[4] = trim($arr1[4],'"');
			$arr1[5] = trim($arr1[5],'"');
			$arr1[6] = trim($arr1[6],'"');
			$arr1[7] = trim($arr1[7],'"');
			if(ord($arr1[7])==13){
				$arr1[7]='';
			}
			if(!empty($arr1[4])||!empty($arr1[5])||!empty($arr1[6])||!empty($arr1[7])){
				$body = $data['domain']."\t".trim($arr1[0],'"')."\t".trim($arr1[1],'"')."\t".trim($arr1[2],'"')."\t".$arr1[4]."\t".$arr1[5]."\t".$arr1[6]."\t".$arr1[7]."\n";
				fwrite($fw,$body);
			}
		}
	}
}
if(!file_exists(PATH_DATA.'semrush/')){
    Directory(PATH_DATA.'semrush/');
}
fwrite($fw,$strnodown);
fclose($fw);
$sql8 = "UPDATE files_up_down SET createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$downname."',status = 'DOWN',senum = $senum,ernum = $ernum WHERE id=$fileid";
echo $sql8."\n";
if($db->exec($sql8)){
	echo "success";
}

function get_content($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);//连接超时
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 180);//执行超时
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
    $r = curl_exec($ch);
    return $r;
}

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
