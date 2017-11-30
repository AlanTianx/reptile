<?php
define("PATH_URI",dirname(__FILE__).'/');
require_once(PATH_URI.'comm.php');
$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sesql = "select id,upfilename,type from files_up_down where type = 'similarweb' and status = 'NEW' order BY id limit 1";

	$pdosel = $db->prepare($sesql);
	$pdosel->execute();
	$data = array();
	while($row = $pdosel->fetch(PDO::FETCH_ASSOC)){
		$data[] = $row;
	}
	if(empty($data)){
		exit('no task');
	}
	$y_m_time = date('Y-m');
	$sql_filter ="SELECT * from simlariweb_result where datatime = '".$y_m_time."'";
	$pdosel0 = $db->query($sql_filter,PDO::FETCH_ASSOC);
	$result = array();
    while($row = $pdosel0->fetch()){
        $result[$row['domain'].'-'.$row['datatime']] = "{$row['domain']}\t{$row['estimatedMonthlyVisits']}\t{$row['country']}\t{$row['countryrank']}\t{$row['globalrank']}\t{$row['category']}\t{$row['categoryrank']}\t{$row['searchsource']}\t{$row['socialsource']}\t{$row['mailsource']}\t{$row['paidreferralssource']}\t{$row['directsource']}\t{$row['referrals']}\t{$row['kw1']}\t{$row['value1']}\t{$row['kw2']}\t{$row['value2']}\t{$row['kw3']}\t{$row['value3']}\t{$row['kw4']}\t{$row['value4']}\t{$row['kw5']}\t{$row['value5']}\t{$row['site1']}\t{$row['site2']}\t{$row['site3']}\t{$row['site4']}\t{$row['site5']}\t{$row['site6']}\t{$row['site7']}\t{$row['site8']}\t{$row['site9']}\t{$row['site10']}\t{$row['datatime']}\n";
    }

	foreach ($data as $data) {
		$senum = 0;
		$ernum = 0;
		$upd_sql0 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'PARSERED' where id=".$data['id'];
		$db->exec($upd_sql0);

		$file = PATH_REPORT.'data/'.$data['type'].'/'.$data['type'].$data['upfilename'].'.dat';//执行一次文件 单独一次

		if(!isset($file) || !file_exists($file))
			throw new Exception ("no file input");

		$country_json = json_decode(file_get_contents(PATH_REPORT.'country.json'),true);
		foreach($country_json as $v){
			$country[$v['id']]['code'] = $v['code'];
			$country[$v['id']]['name'] = $v['name'];
		}
		$date = date("Y-m-01",strtotime('-37 days'));
		$fp = fopen($file,"r");
		$mitime = explode(" ",microtime());
		$mtime = $mitime[0]*100000000;
		if(!file_exists(PATH_DATA.'similarweb/')){
	        Directory(PATH_DATA.'similarweb/');
	    }
		$wfile = PATH_DATA.'similarweb/similarweb_'.date("YmdHis").$mtime.'.dat';
		$temporary = PATH_DATA.'similarweb/'.$mtime.'temporary_sim.dat';
		
		$fw = fopen($wfile,"w");
		$fw_temporary = fopen($temporary, 'w');

		fwrite($fw,"domain\tEstimatedMonthlyVisits\tCountry\tCountryRank\tGlobalRank\tCategory\tCategoryRank\tSearchSource\tSocialSource\tMailSource\tPaidReferralsSource\tDirectSource\tReferrals\tKW1\tValue1\tKW2\tValue2\tKW3\tValue3\tKW4\tValue4\tKW5\tValue5\tSite1\tSite2\tSite3\tSite4\tSite5\tSite6\tSite7\tSite8\tSite9\tSite10\tdatatime\n");
		try{
			while(!feof($fp)){
				$lr = explode("\t",trim(fgets($fp)));
				if(isset($result[$lr[0].'-'.date('Y-m')])){
					$senum++;
					fwrite($fw,$result[$lr[0].'-'.date('Y-m')]);
					//fwrite($fw_temporary,$result[$lr[0].'-'.date('Y-m')]);
				}else{
					$uri = "https://api.similarweb.com/SimilarWebAddon/{$lr[0]}/all";
					echo $uri."\n";
					$out = curl($uri);
					if($out){
						$senum++;
						$output = json_decode($out,true);
						@$search = round($output['TrafficSources']['Search']*100,4);
						@$social = round($output['TrafficSources']['Social']*100,4);
						@$mail = round($output['TrafficSources']['Mail']*100,4);
						@$paidrefer = round($output['TrafficSources']['Paid Referrals']*100,4);
						@$direct = round($output['TrafficSources']['Direct']*100,4);
						@$referrals = round($output['TrafficSources']['Referrals']*100,4);
						@$v0 = round($output['TopOrganicKeywords'][0]['Value']*100,4);
						@$v1 = round($output['TopOrganicKeywords'][1]['Value']*100,4);
						@$v2 = round($output['TopOrganicKeywords'][2]['Value']*100,4);
						@$v3 = round($output['TopOrganicKeywords'][3]['Value']*100,4);
						@$v4 = round($output['TopOrganicKeywords'][4]['Value']*100,4);
						@fwrite($fw,"{$lr[0]}\t{$output['EstimatedMonthlyVisits'][$date]}\t{$country[$output['CountryRank']['Country']]['name']}\t{$output['CountryRank']['Rank']}\t{$output['GlobalRank']['Rank']}\t{$output['Category']}\t{$output['CategoryRank']['Rank']}\t{$search}%\t{$social}%\t{$mail}%\t{$paidrefer}%\t{$direct}%\t{$referrals}%\t{$output['TopOrganicKeywords'][0]['Keyword']}\t{$v0}%\t{$output['TopOrganicKeywords'][1]['Keyword']}\t{$v1}%\t{$output['TopOrganicKeywords'][2]['Keyword']}\t{$v2}%\t{$output['TopOrganicKeywords'][3]['Keyword']}\t{$v3}%\t{$output['TopOrganicKeywords'][4]['Keyword']}\t{$v4}%\t{$output['SimilarSites'][0]['Site']}\t{$output['SimilarSites'][1]['Site']}\t{$output['SimilarSites'][2]['Site']}\t{$output['SimilarSites'][3]['Site']}\t{$output['SimilarSites'][4]['Site']}\t{$output['SimilarSites'][5]['Site']}\t{$output['SimilarSites'][6]['Site']}\t{$output['SimilarSites'][7]['Site']}\t{$output['SimilarSites'][8]['Site']}\t{$output['SimilarSites'][9]['Site']}\t".date('Y-m')."\n");
						@fwrite($fw_temporary,"{$lr[0]}\t{$output['EstimatedMonthlyVisits'][$date]}\t{$country[$output['CountryRank']['Country']]['name']}\t{$output['CountryRank']['Rank']}\t{$output['GlobalRank']['Rank']}\t{$output['Category']}\t{$output['CategoryRank']['Rank']}\t{$search}%\t{$social}%\t{$mail}%\t{$paidrefer}%\t{$direct}%\t{$referrals}%\t{$output['TopOrganicKeywords'][0]['Keyword']}\t{$v0}%\t{$output['TopOrganicKeywords'][1]['Keyword']}\t{$v1}%\t{$output['TopOrganicKeywords'][2]['Keyword']}\t{$v2}%\t{$output['TopOrganicKeywords'][3]['Keyword']}\t{$v3}%\t{$output['TopOrganicKeywords'][4]['Keyword']}\t{$v4}%\t{$output['SimilarSites'][0]['Site']}\t{$output['SimilarSites'][1]['Site']}\t{$output['SimilarSites'][2]['Site']}\t{$output['SimilarSites'][3]['Site']}\t{$output['SimilarSites'][4]['Site']}\t{$output['SimilarSites'][5]['Site']}\t{$output['SimilarSites'][6]['Site']}\t{$output['SimilarSites'][7]['Site']}\t{$output['SimilarSites'][8]['Site']}\t{$output['SimilarSites'][9]['Site']}\t".date('Y-m')."\n");
					}else{
						$ernum++;
						continue;
					}
					
				}
			}
			fclose($fw_temporary);
			fclose($fw);
			fclose($fp);
			//导入文件到数据库
			$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$wfile."',status = 'DOWN',senum = $senum,ernum = $ernum where id=".$data['id'];
			$sql = "LOAD DATA LOCAL INFILE '".$temporary."' REPLACE INTO TABLE simlariweb_result FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' (domain,estimatedMonthlyVisits,country,countryrank,globalrank,category,categoryrank,searchsource,socialsource,mailsource,paidreferralssource,directsource,referrals,kw1,value1,kw2,value2,kw3,value3,kw4,value4,kw5,value5,site1,site2,site3,site4,site5,site6,site7,site8,site9,site10,datatime)";
			if($num = $db->exec($sql)){
				echo $num."\n";
				$db->exec($upd_sql);//修改状态
				echo 'success:'.$senum."\n";
				echo 'erroe:'.$ernum."\n";
				unlink($temporary);
				unlink($file);
			}else{
				$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD',senum = $senum,ernum = $ernum where id=".$data['id'];
				$db->exec($upd_sql);//修改状态
				echo 'success:'.$senum."\n";
				echo 'erroe:'.$ernum."\n";
				unlink($temporary);
				unlink($file);
				echo 'error'."\n";
			}
		}catch(Exception $e){
			fclose($fw);
			fclose($fp);
			$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD',senum = $senum,ernum = $ernum where id=".$data['id'];
			$db->exec($upd_sql);//修改状态
			echo 'success:'.$senum."\n";
			echo 'erroe:'.$ernum."\n";
			unlink($file);
			printf("An error has occurred: %s\n", $e->getMessage());
			continue;
		}
	}
	
function curl($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_HEADER, 0);
	
	$out = curl_exec($ch);
	curl_close($ch);

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
