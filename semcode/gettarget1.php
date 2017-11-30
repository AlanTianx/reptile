<?php
define('PATH_URI',dirname(__FILE__).'/');
define('PATH_REPORT', '/app/site/reporting.soarinfotech.com/web/mermining/');
define('PATH_CODE',dirname(dirname(dirname(__FILE__))).'/');
define('DB_CONFIG', PATH_CODE .'etc/db.ini');
define('PATH_DATA', dirname(PATH_CODE).'/semdata/');
require_once(PATH_URI.'comm.php');

function GetKeywordsStats(AdWordsUser $user, $sources, &$fw, $lo) {
    // Get the service, which loads the required classes.
    $targetingIdeaService  = $user->GetService('TargetingIdeaService');
	
    $queries = array_keys($sources);
    $kwd = new RelatedToQuerySearchParameter($queries, 'PHRASE');
	if($lo > 0){
    	$loc1 = new Location();
   		$loc1->id = $lo; //DE:2276//CA: 2124;//US: 2840;//UK:2826;
    	$arr[] = $loc1;            
    	$location = new LocationSearchParameter($arr);
	}
    $selector = new TargetingIdeaSelector();
    $selector->ideaType = 'KEYWORD';
    $selector->requestType = 'STATS';    
    $selector->requestedAttributeTypes = array('SEARCH_VOLUME','TARGETED_MONTHLY_SEARCHES','AVERAGE_CPC','COMPETITION');
	if($lo > 0){
		$selector->searchParameters = array($kwd, $location);
	}else{
		$selector->searchParameters = array($kwd);	
	}
    $paging = new Paging();
    $paging->startIndex = 0;
    $paging->numberResults = 800;
    $selector->paging = $paging;

    echo "Request...".count($sources)."\n";
    $rs = $targetingIdeaService->get($selector);

    $stats = array();
    foreach ($rs->entries as $k =>$v) {
        $mid =  $sources[$queries[$k]];
		$stats[$mid][$queries[$k]] = $v->data[1]->value->value[0]->count;
	}

	$termlo = array(2840=>array('promo codes','coupon','coupon code'),2826=>array('discount code','promo code','Vouchers code'),2036=>array('promo code','discount code','ouchers'));

    foreach ($stats as $mid => $v) {
		arsort($v);
		$v = array_slice($v,0,3);
		$body = "{$mid}";
		$arr = array();
		$n = 0;
		
		foreach($v as $i=>$j){
			if($j == 0 && isset($termlo[$lo])){
				if($lo == '2250'){
					$i = $termlo[$lo][$n]." {$mid}";
				}else{
					$i = $mid." {$termlo[$lo][$n]}";
				}
				while(1==1){
					if(!isset($arr[$i]))
						break;
					$n++;
					if($n >= 3)
						break;
				}
				$j = 0;
				$n++;
			}

			$arr[$i] = '';
			$body .= "\t{$i}\t{$j}";
		}
		if(count($v) < 3){
			$m = 3-count($v);
			for($f=0;$f<$m;$f++){
				$body .= "\t\t";
			}
		}
		$datemonth = date('Y-m');
		$body .= "\t{$datemonth}";
		// echo $body;

        	fwrite($fw, $body."\n");
		// if(count($v) > 0)
			// fwrite($fw_temporary, $body."\t{$lo}\n");
    }
	
}

$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass ,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sesql = "select * from files_up_down where type = 'keywords' and status = 'NEW' order by id asc limit 1";
$datas = array();
$pdosel = $db->query($sesql,PDO::FETCH_ASSOC);
while($row = $pdosel->fetch()){
	$datas[] = $row;
}

if(empty($datas))
	exit('data is null');

foreach($datas as $data){
	$upd_sql0 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'PARSERED' where id=".$data['id'];
	$db->exec($upd_sql0);
	try {
	    $account = 'A';

		// $upload_filename = PATH_REPORT.'data/'.$data['type'].'/'.$data['upfilename'];

	    $file = PATH_REPORT.'data/'.$data['type'].'/'.$data['type'].$data['upfilename'].'.dat';
	    echo $file;

	    if (!isset($file) || $file == "" || !file_exists($file))
	        throw new Exception ("No file input");
	    $location = $data['location'];
	    if (!isset($location) || $location == "")
	        throw new Exception ("No location input");
	    $terms = $data['terms'];
	    if (!isset($terms) || $terms == "")
			$terms = array('promo code','promo codes','coupon code','coupon codes','coupon','coupons','promotion code','promotion codes','discount code','discount codes','Voucher code','Voucher codes','Vouchers');
		$terms = explode(",", $terms);


	    // Get AdWordsUser from credentials in "../auth.ini"
	    // relative to the AdWordsUser.php file's directory.
	    $conf = new Zend_Config_Ini(AUTH_CONFIG, strtoupper($account));
	    //5.3.1 $user = new AdWordsUser(null, null, null, null, null, null, $conf->client);    
		$user = new AdWordsUser(NULL,NULL,NULL, $conf->client,NULL,NULL);

	    // Log every SOAP XML request and response.
	    $user->LogAll();

		
	    $kw_limit = intval(500/(count($terms) > 0? count($terms) : 1 ));
	    $kw_cnt = 0;

	    if(!file_exists(PATH_DATA.'target/')){
	        Directory(PATH_DATA.'target/');
	    }
		
		$wfile = PATH_DATA.'target/'.md5($data['upfilename'].'_'.date('YmdHis')).'.dat';
		echo $wfile;
	    $fw = fopen($wfile, 'w');//打开任务结果
		fwrite($fw, "Keyword\tkw1\tSE vol1\tkw2\tSE vol2\tkw3\tSE vol3\tDateMonth\n");
	    $fp = fopen($file, 'r');//打开任务文件
	    $k = 0;
	    while (!feof($fp)) {
	        $lr = explode("\t", trim(fgets($fp)));

	        //if ($k++ == 0 || $lr[0] == '')
	          //  continue;
			$k++;
			if ($k % $kw_limit == 0) {
				echo $k."\n";
				do {
					try {
						GetKeywordsStats($user, $kwds, $fw, $location);            
					}
					catch (Exception $e) {
						if (stripos($e->getMessage(), 'retryAfterSeconds') !== false) {
							echo "retry afeter 30 sencods, because of ". $e->getMessage()."\n";
							sleep(35);
						}
						else {
							$sql = "update files_up_down set status='FAILD',createdate='".date('Y-m-d H:i:s')."' where id={$data['id']}";
							echo $sql."+1 \n";
							$db -> exec($sql);
							die($e->getMessage());
							//break;
						}
					}
					break;
				}while (true);

				$k = 0;
				$kwds = array();
			}
			
			$encode = mb_detect_encoding($lr[0], array('gb2312','gbk','utf-8','ASCII'), true);
			if (!$encode){
				//$sql = "update files_up_down set status='FAILD',createdate='".date('Y-m-d H:i:s')."' where id={$data['id']}";
				//echo $sql."+2 \n";
                                //$db -> exec($sql);
				continue;
			}

			if ($encode != 'utf-8')
				$lr[0] = iconv($encode, 'utf-8', $lr[0]);

			if (count($terms) > 0) {
				if($location==2250){
					foreach($terms as $t){
						$kwds[$t." {$lr[0]}"]= $lr[0];
					}
				}else{
					foreach($terms as $t){
						$kwds[$lr[0]." {$t}"]= $lr[0];
					}

				}
		
			}
			else {
				$kwds[$lr[0]] = $lr[0];	
			}
			// }
	    }
		
		if ($k > 0) {
	        GetKeywordsStats($user, $kwds, $fw, $location);
	    }
		
	    fclose($fp);
	    // fclose($fw_temporary);	
		
		$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$wfile."',status = 'DOWN' where id=".$data['id'];
		echo $upd_sql."\n";
	    $db->exec($upd_sql);//修改状态
		unset($file);

	}
	catch (Exception $e) {
		$sql = "update files_up_down set status='FAILD',createdate='".date('Y-m-d H:i:s')."' where id={$data['id']}";
		echo $sql."+3 \n";
		$db -> exec($sql);
		printf("An error has occurred: %s\n", $e->getMessage());
		exit(1);
	}
}



//创建文件
function Directory($dir){
    if(is_dir($dir) || @mkdir($dir,0777)){
        //echo $dir."创建成功<br>";
    }else{
        $dirArr=explode('/',$dir);
        array_pop($dirArr);
        $newDir=implode('/',$dirArr);
        Directory($newDir,0777);
        if(@mkdir($dir)){
            // echo $dir."创建成功<br>";
        }
    }
}







