<?php
define("PATH_URI",dirname(dirname(dirname(__FILE__))).'/');
require_once(PATH_URI.'googlecrawler/vendor/autoload.php');
use \howie6879\PhpGoogle\MagicGoogle;

require_once(PATH_URI.'comm.php');
// $dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
// $db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// $sesql = "select id,upfilename,type from files_up_down where type = 'google_index' and status = 'NEW' order BY id limit 1";

// $pdosel = $db->prepare($sesql);
// $pdosel->execute();
// $data = array();
// while($row = $pdosel->fetch(PDO::FETCH_ASSOC)){
// 	$data[] = $row;
// }
// if(empty($data)){
// 	exit('no task');
// }
// $ymtime = date('Y-m');
// $sql_filter ="SELECT * from google_indexs_result where datatime = '".$ymtime."'";
// $pdosel0 = $db->query($sql_filter,PDO::FETCH_ASSOC);
// $result = array();
// while($row = $pdosel0->fetch()){
//     $result[$row['keyword'].'-'.$row['datatime']] = "{$row['keyword']}\t{$row['indexs']}\t{$row['google_domain']}\t{$row['rank1_title']}\t{$row['rank1_link']}\t{$row['rank2_title']}\t{$row['rank2_link']}\t{$row['rank3_title']}\t{$row['rank3_link']}\t{$row['rank4_title']}\t{$row['rank4_link']}\t{$row['rank5_title']}\t{$row['rank5_link']}\t{$row['rank6_title']}\t{$row['rank6_link']}\t{$row['rank7_title']}\t{$row['rank7_link']}\t{$row['rank8_title']}\t{$row['rank8_link']}\t{$row['rank9_title']}\t{$row['rank9_link']}\t{$row['rank10_title']}\t{$row['rank10_link']}\t{$row['datatime']}\n";
// }
//foreach ($data as $data) {
	// $upd_sql0 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'PARSERED' where id=".$data['id'];
	// $db->exec($upd_sql0);
	// $file = PATH_REPORT.'data/'.$data['type'].'/'.$data['type'].$data['upfilename'].'.dat';//执行一次文件 单独一次
//	$str = file_get_contents('/app/sem/semcode/mermining/googlecrawler/examples/1.txt');
//	if(strpos($str, 'google having disabled our ip')!==false){
//		exit(3);
//	}else{
//		echo "ok".date('Y-m-d H:i:s')."\n";
//	}
	$file = "/app/site/reporting.soarinfotech.com/web/mermining/data/google_index/google_indexemmadong201710292312184524.csv.dat";
	// if(!file_exists(PATH_DATA.'googlesearch/')){
      //   	Directory(PATH_DATA.'googlesearch/');
    //	}
	if(empty($file) || !file_exists($file))
		exit("file is empty");
	$fp = fopen($file,"r");
	$mitime = explode(" ",microtime());
	$mtime = $mitime[0]*100000000;
	$wfile = PATH_DATA.'googlesearch/google_index_'.date('Ymd').$mtime.'.dat';
	$temporary = PATH_DATA.'googlesearch/'.$mtime.'temporary_sim.dat';
	$fw_temporary = fopen($temporary, 'w');
	$fw = fopen($wfile,"w");
	fwrite($fw,"keyword\tindexs\tgoogle_domain\trank1_title\trank1_link\trank2_title\trank2_link\trank3_title\trank3_link\trank4_title\trank4_link\trank5_title\trank5_link\trank6_title\trank6_link\trank7_title\trank7_link\trank8_title\trank8_link\trank9_title\trank9_link\trank10_title\trank10_link\tpeople_search1\tpeople_search2\tpeople_search3\tpeople_search4\tpeople_search5\tpeople_search6\tdatatime\n");
	try{
		$success_num = 0;
		$faild_num = 0;
		$cnt = 0;
		$f = 70;
		$i = 1;
		$magicGoogle = new MagicGoogle();//google indexs 
		while (!feof($fp)) {
			$lr = explode("\t", trim(fgets($fp)));
			// if(isset($result[$lr[0].'-'.date('Y-m')])){
			// 	fwrite($fw,$result[$lr[0].'-'.date('Y-m')]);
			// }else{
				if(empty($lr[0]))
					continue;
				$body = "";
				$temporary_body = "";
				if($cnt==$f*$i){
					echo 'now_times='.date('Y-m-d H:i:s')."\n";
					echo 'now_num='.$cnt."\n";
                                        echo 'now success_num = '.$success_num."\n";
					sleep(1080);
					$i++;
				}
				$cnt++;
//sleep(rand(3,7));
				$google_arr = $magicGoogle->search_page($lr[0]);
				if(isset($google_arr['code'])&&@$google_arr['code']==0){
					fclose($fw);
					fclose($fw_temporary);
					fclose($fp);
					// $upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD' where id=".$data['id'];
					// $db->exec($upd_sql);//修改状态
					echo $google_arr['msg']."\n";
					echo 'now num='.$cnt."\n";
					echo 'IP disabled time :'.$google_arr['ti']."\n";
					echo 'now success_num = '.$success_num."\n";
					exit(2);
				}
				@$indexs = explode(' ',$google_arr['indexes']);
				if($google_arr){
					$success_num++;
					//echo "success time=".date('Y-m-d H:i:s')."\n";
					@$temporary_body .= $lr[0]."\t".$indexs[1]."\t".$google_arr['google_domain']."\t".$google_arr['linkdata'][0]['title']."\t".$google_arr['linkdata'][0]['link']."\t".$google_arr['linkdata'][1]['title']."\t".$google_arr['linkdata'][1]['link']."\t".$google_arr['linkdata'][2]['title']."\t".$google_arr['linkdata'][2]['link']."\t".$google_arr['linkdata'][3]['title']."\t".$google_arr['linkdata'][3]['link']."\t".$google_arr['linkdata'][4]['title']."\t".$google_arr['linkdata'][4]['link']."\t".$google_arr['linkdata'][5]['title']."\t".$google_arr['linkdata'][5]['link']."\t".$google_arr['linkdata'][6]['title']."\t".$google_arr['linkdata'][6]['link']."\t".$google_arr['linkdata'][7]['title']."\t".$google_arr['linkdata'][7]['link']."\t".$google_arr['linkdata'][8]['title']."\t".$google_arr['linkdata'][8]['link']."\t".$google_arr['linkdata'][9]['title']."\t".$google_arr['linkdata'][9]['link']."\t".$google_arr['people_search'][0]."\t".$google_arr['people_search'][1]."\t".$google_arr['people_search'][2]."\t".$google_arr['people_search'][3]."\t".$google_arr['people_search'][4]."\t".$google_arr['people_search'][5]."\t".date('Y-m')."\n";
					@$body .= $lr[0]."\t".$indexs[1]."\t".$google_arr['google_domain']."\t".$google_arr['linkdata'][0]['title']."\t".$google_arr['linkdata'][0]['link']."\t".$google_arr['linkdata'][1]['title']."\t".$google_arr['linkdata'][1]['link']."\t".$google_arr['linkdata'][2]['title']."\t".$google_arr['linkdata'][2]['link']."\t".$google_arr['linkdata'][3]['title']."\t".$google_arr['linkdata'][3]['link']."\t".$google_arr['linkdata'][4]['title']."\t".$google_arr['linkdata'][4]['link']."\t".$google_arr['linkdata'][5]['title']."\t".$google_arr['linkdata'][5]['link']."\t".$google_arr['linkdata'][6]['title']."\t".$google_arr['linkdata'][6]['link']."\t".$google_arr['linkdata'][7]['title']."\t".$google_arr['linkdata'][7]['link']."\t".$google_arr['linkdata'][8]['title']."\t".$google_arr['linkdata'][8]['link']."\t".$google_arr['linkdata'][9]['title']."\t".$google_arr['linkdata'][9]['link']."\t".$google_arr['people_search'][0]."\t".$google_arr['people_search'][1]."\t".$google_arr['people_search'][2]."\t".$google_arr['people_search'][3]."\t".$google_arr['people_search'][4]."\t".$google_arr['people_search'][5]."\t".date('Y-m')."\n";
				}else{
					$faild_num++;
					$body .= $lr[0]."\t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t \t".date('Y-m')."\n";
				}
				fwrite($fw,$body);
				fwrite($fw_temporary,$temporary_body);
			//}
		}
		fclose($fw);
		fclose($fw_temporary);
		fclose($fp);
		//导入文件到数据库
		// $upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',downfilename = '".$wfile."',status = 'DOWN' where id=".$data['id'];
		// $sql = "LOAD DATA LOCAL INFILE '".$temporary."' REPLACE INTO TABLE google_indexs_result FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' (keyword,indexs,google_domain,rank1_title,rank1_link,rank2_title,rank2_link,rank3_title,rank3_link,rank4_title,rank4_link,rank5_title,rank5_link,rank6_title,rank6_link,rank7_title,rank7_link,rank8_title,rank8_link,rank9_title,rank9_link,rank10_title,rank10_link,people_search1,people_search2,people_search3,people_search4,people_search5,people_search6,datatime)";
		echo 'count='.$cnt."\n";
		echo 'success='.$success_num."\n";
		echo 'unsuccess='.$faild_num."\n";
		// if($num = $db->exec($sql)||$cnt==0){
		// 	$db->exec($upd_sql);//修改状态
		// 	//unlink($file);
		// 	unlink($temporary);
		// }else{
		// 	$upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD' where id=".$data['id'];
		// 	$db->exec($upd_sql);//修改状态
		// 	//unlink($file);
		// 	unlink($temporary);
		// 	echo 'error';
		// }
	}catch(Exception $e){
		fclose($fw);
		fclose($fw_temporary);
		fclose($fp);
		// $upd_sql = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD' where id=".$data['id'];
		// $db->exec($upd_sql);//修改状态
		//unlink($file);
		unlink($temporary);
		printf("An error has occurred: %s\n", $e->getMessage());
		exit(1);
	}
//}

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
