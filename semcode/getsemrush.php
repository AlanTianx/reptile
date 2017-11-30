<?php
define("PATH_URI",dirname(__FILE__).'/');
require_once(PATH_URI.'comm.php');
$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT id FROM files_up_down WHERE type = 'semrushD' AND status = 'COMPLETE' LIMIT 1";
$result = $db->query($sql,PDO::FETCH_ASSOC)->fetch();
if(empty($result)){
	exit('no task');
}
$fileid = $result['id'];
$sql1 = "SELECT * FROM domain_file_down_url WHERE file_id = $fileid AND status = 'NEW'";
$result1 = $db->query($sql1,PDO::FETCH_ASSOC);
$data = array();
while ($row = $result1->fetch()) {
	$data[] = $row;
}
if(empty($data)){
	exit('no task');
}
$tablename = 'semrush_result'.$fileid;
$sql2 = "CREATE TABLE IF NOT EXISTS `{$tablename}` (
  `Domain` char(128) NOT NULL DEFAULT '',
  `CompetitorRelevance` char(64) DEFAULT '',
  `CommonKeywords` char(64) DEFAULT '',
  `OrganicKeywords` char(64) DEFAULT '',
  `OrganicTraffic` char(64) DEFAULT '',
  `OrganicCost` char(64) DEFAULT '',
  `AdwordsKeywords` char(64) DEFAULT '',
  PRIMARY KEY (`Domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
//$sqllock = "LOCK TABLES `".$tablename."` write;";
$db->exec($sql2);
//$db->exec($sqllock);
$strnodown = "";
$senum = 0;
$ernum = 0;
foreach ($data as $data) {
	$url = $data['url'];
	$sql3 = "UPDATE domain_file_down_url SET status = 'PARSERED' WHERE id = {$data['id']}";
	$s = $db->exec($sql3);
	if(!$s){
		$ernum++;
		$strnodown .= $data['domain'].','.$data['url']."\n";
		echo 'error1:the '.$data['id'].'-'.$data['domain'].' update status faild'."\n";
		$sql5 = "UPDATE domain_file_down_url SET status = 'FAILD' WHERE id = {$data['id']}";
		$db->exec($sql5);
		continue;
	}
	if(!file_exists(PATH_DATA.'semrush/')){
        Directory(PATH_DATA.'semrush/');
    }
    $mitime = explode(" ",microtime());
	$mtime = $mitime[0]*100000000;
	$wfile = PATH_DATA.'semrush/semrush_'.date("YmdHis").$mtime.'.dat';
	$content = curl($url,$wfile);
	if($content){
		$senum++;
		$sql4 = "LOAD DATA LOCAL INFILE '".$wfile."' REPLACE INTO TABLE $tablename FIELDS TERMINATED BY ',' LINES TERMINATED BY '\\n' (Domain,CompetitorRelevance,CommonKeywords,OrganicKeywords,OrganicTraffic,OrganicCost,AdwordsKeywords)";
		echo $sql4."\n";
		$c = $db->exec($sql4);
		unlink($wfile);
	}else{
		$ernum++;
		$strnodown .= $data['domain'].','.$data['url']."\n";
		echo 'error2:the '.$data['id'].'-'.$data['domain'].' load faild'."\n";
		echo 'filename:'.$wfile."\n";
		$sql5 = "UPDATE domain_file_down_url SET status = 'FAILD' WHERE id = {$data['id']}";
		$db->exec($sql5);
		continue;
	}
	$sql6 = "UPDATE domain_file_down_url SET status = 'DOWN' WHERE id = {$data['id']}";
	$db->exec($sql6);
}
//$sql7 = "SELECT id FROM domain_file_down_url WHERE file_id = $fileid AND status = 'NEW'";
$downname = PATH_DATA.'semrush/semrush_'.date("YmdHis").$mtime.'.csv';
$fw = fopen($downname,'w');
$sql7 = "SELECT SQL_NO_CACHE * FROM $tablename";
$re = $db->query($sql7,PDO::FETCH_ASSOC);
while ($row = $re->fetch()) {
	$str = $row['Domain'].','.$row['CompetitorRelevance'].','.$row['CommonKeywords'].','.$row['OrganicKeywords'].','.$row['OrganicTraffic'].','.$row['OrganicCost'].','.$row['AdwordsKeywords']."\n";
	fwrite($fw,$str);
}
fwrite($fw,$strnodown);
fclose($fw);
$sql8 = "UPDATE files_up_down SET createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$downname."',status = 'DOWN',senum = $senum,ernum = $ernum WHERE id=$fileid";
if($db->exec($sql8)){
	$sql9 = "DROP TABLE $tablename";
	$db->exec($sql9);
};


function curl($uri,$file){
    $ch = curl_init();
    $fp = fopen($file,"a");
    $options = array(
        CURLOPT_URL => $uri,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FILE => $fp
    );
    curl_setopt_array($ch, $options);
    $output = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $output;
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
