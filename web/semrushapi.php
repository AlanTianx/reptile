<?php
session_start();
/**
 * Created by PhpStorm.
 * User: tiantian
 * Date: 2017/11/3
 * Time: 9:17
 */
require_once('comm.php');
$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass);
//$db = new PDO('mysql:host=localhost;dbname=test' ,'root',123,array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));


$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = @$_REQUEST['action'];
//$action = 'gettask';
switch($action){
    case 'gettask':
            $sesql = "select id,upfilename,type,location from files_up_down where (type = 'semrushD' OR type = 'semrushK') AND status = 'NEW' order by id limit 1";
            $sesql0 = "select id from files_up_down where (type = 'semrushD' OR type = 'semrushK') AND status = 'PARSERED' order by id limit 1";
            if($db->query($sesql0,PDO::FETCH_ASSOC)->fetch()){
                $arr['code'] = 0;
                $arr['msg'] = 'having play';
                echo json_encode($arr);
                exit;
            }
            $result = $db->query($sesql,PDO::FETCH_ASSOC);
            $data = array();
            while($row = $result->fetch()){
                $data[] = $row;
            }
            if(empty($data)){
                $arr['code'] = 0;
                $arr['msg'] = 'no task';
                echo json_encode($arr);
                exit;
            }
            foreach($data as $value){
                $upd_sql0 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'PARSERED' where id=".$value['id'];
                $db->exec($upd_sql0);
                $_SESSION['num'] = 0;
                $file = PATH_CODE.'/mermining/data/'.$value['type'].'/'.$value['type'].$value['upfilename'].'.dat';
                $fp = fopen($file,"r");
                $domain_arr = array();
                while (!feof($fp)) {
                    $lr = explode("\t", trim(fgets($fp)));
                    $domain_arr[] = $lr[0];
                }
                fclose($fp);
                $domain_arr = array_filter($domain_arr);
                $cnt = count($domain_arr);
                if($cnt>100){
                    $upd_sql9 = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'FAILD' where id=".$value['id'];
                    $db->exec($upd_sql9);
                    $arr['code'] = 0;
                    $arr['msg'] = 'File line > 100';
                    echo json_encode($arr);
                    continue;
                }
                if($value['type']=='semrushD'){
                    $value['semrush_type'] = '1';
                }else{
                    $value['semrush_type'] = '2';
                }
                $arr['code'] = 1;
                $arr['type'] = $value['semrush_type'];
                $arr['id'] = $value['id'];
                $arr['msg'] ='success';
                $arr['data'] = $domain_arr;
                $arr['location'] = country($value['location']);
                $arr['arr_len'] = count($domain_arr);
                //$arr['location'] = 'us';
                echo json_encode($arr);
            }
            break;
    case 'settask':
            $domain = trim($_REQUEST['domain']);
            $oc = trim($_REQUEST['OC']);
            $id = trim($_REQUEST['id']);
            $num = trim($_REQUEST['num']);
            $_SESSION['num'] = $_SESSION['num']+1;
            $sql = "insert ignore into domain_file_down_url SET domain = '{$domain}',file_id = $id,url = '$oc',status = 'NEW'";
            if($a = $db->exec($sql)){
                $arr['code'] = 0;
                $arr['msg'] ='success';
                $arr['id'] = $a;
                $arr['data']=$_POST;
                echo json_encode($arr);
            }else{
                $arr['code'] = 0;
                $arr['msg'] ='error';
                $arr['data']=$_POST;
                echo json_encode($arr);
            }
            if($_SESSION['num']==$num){
                $sqlp = "update files_up_down set createdate = '".date('Y-m-d H:i:s')."',status = 'COMPLETE' where id=".$id;
                $db->exec($sqlp);
                $sqlc = "select id,upfilename,type,location from files_up_down where id = ".$id;
                $row = $db->query($sqlc,PDO::FETCH_ASSOC)->fetch();
                $filename = PATH_CODE.'/mermining/data/'.$row['type'].'/'.$row['type'].$row['upfilename'].'.dat';
                unlink($filename);
            }
            break;
    default:
        exit('error action');
        break;

}

function country($num){
    switch($num){
        case '2840':
            $local = 'us';
            break;
        case '2124':
            $local = 'ca';
            break;
        case '2276':
            $local = 'de';
            break;
        case '2826':
            $local = 'uk';
            break;
        case '2036':
            $local = 'au';
            break;
        case '2040':
            $local = 'at';
            break;
        case '2756':
            $local = 'ch';
            break;
        case '2250':
            $local = 'fr';
            break;
        case '2392':
            $local = 'jp';
            break;
        case '0':
            $local = 'us';
            break;
    }
    return $local;
}
