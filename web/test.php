<?php
$location = @$_POST['location'];
$terms = @trim($_POST['terms'],' ');
$type = @$_POST['type'];
$user = @$_SERVER['PHP_AUTH_USER'];
if($type=='keywords'){

    if(empty($terms)){
        echo "<script language =\"javascript\">alert('terms can\'t be empty!');history.back(-1)</script>\r\n";
        exit;
    }
}
require_once('comm.php');
$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if($_FILES && $_FILES['uploadfile']){
    if(stripos($_FILES['uploadfile']['name'], '.csv')===false){
        echo "<script language =\"javascript\">alert('file ex is not correct!');history.back(-1)</script>\r\n";
        exit;
    }
    switch ($type) {
        case 'keywords':
            $fp = fopen($_FILES['uploadfile']['tmp_name'],'r');
            if ($fp) {
                while (stream_get_line($fp, 8192, "\n")) {
                    $line++;
                }
                fclose($fp); //关闭文件
            }
            if($line>2000){
                echo "<script language =\"javascript\">alert('The number of file rows is greater than 2000');history.back(-1)</script>\r\n";
                exit;
            }
            break;
        case 'semrushD':
            $fp = fopen($_FILES['uploadfile']['tmp_name'],'r');
            if ($fp) {
                while (stream_get_line($fp, 8192, "\n")) {
                    $line++;
                }
                fclose($fp); //关闭文件
            }
            if($line>100){
                echo "<script language =\"javascript\">alert('The number of file rows is greater than 100');history.back(-1)</script>\r\n";
                exit;
            }
            break;
        case 'semrushK':
            $fp = fopen($_FILES['uploadfile']['tmp_name'],'r');
            if ($fp) {
                while (stream_get_line($fp, 8192, "\n")) {
                    $line++;
                }
                fclose($fp); //关闭文件
            }
            if($line>100){
                echo "<script language =\"javascript\">alert('The number of file rows is greater than 100');history.back(-1)</script>\r\n";
                exit;
            }
            break;
        case 'similarweb':
            $fp = fopen($_FILES['uploadfile']['tmp_name'],'r');
            if ($fp) {
                while (stream_get_line($fp, 8192, "\n")) {
                    $line++;
                }
                fclose($fp); //关闭文件
            }
            if($line>20000){
                echo "<script language =\"javascript\">alert('The number of file rows is greater than 20000');history.back(-1)</script>\r\n";
                exit;
            }
            break;
        default:
            # code...
            break;
    }
    if(!file_exists('./data/'.$type.'/')){
        Directory('./data/'.$type.'/');
    }
// switch ($type) {
//     case 'similarweb':
//         $sql_filter ="SELECT CONCAT(domain,'-',datatime) as info from simlariweb_result where datatime = '".date('Y-m')."'";
//         break;
//     case 'keywords':
//         $sql_filter ="SELECT CONCAT(keyword,'-',datatime) as info from keywords_result where datatime = '".date('Y-m')."'";
//         break;
//     case 'shoponline':
//         $sql_filter ="SELECT CONCAT(domain,'-',datatime) as info from shoponline_result where datatime = '".date('Y-m')."'";
//         break;
//     default:
//         echo 55;
//         break;
// }
// $pdosel = $db->query($sql_filter,PDO::FETCH_ASSOC);
// while($row = $pdosel->fetch()){
//     $result[$row['info']] = '';
// }
// if($result){
//     //$result = array_flip($result);
//     //读取文件成数组
//     $file_list = array();
//     $file = fopen($_FILES['uploadfile']['tmp_name'],'r');
//     //$file_repetition_record = fopen
//     while ($data = fgetcsv($file)) {
//         if($data[0]){
//             $data = explode('/',$data[0]);
//             $pd_data = $data[0].'-'.date('Y-m');
//             if(isset($result[$pd_data])||$data[0]=='domain'){

//             }else{
//                 $file_list[] = $data[0];
//             }
        
//         }
//     }
//     var_dump($file_list);
// }else{

// }
// exit;

    //读取文件成数组
    $file_list = array();
    $file = fopen($_FILES['uploadfile']['tmp_name'],'r');
    while ($data = fgetcsv($file)) {
        if($data[0]=='domain'||empty($data[0])||$data[0]=='keyword'){
            continue;
        }
        if($type=='google_index'){
            if($data[0]){
                $file_list[] = $data[0];
            }
        }else{
            if($data[0]){
                $data = explode('/',$data[0]);
                $data = explode('?',$data[0]);
                $file_list[] = $data[0];
            }
        }
    }
    $file_list = array_flip($file_list);

    $file_list = array_flip($file_list);

    //$new_file_arr = filter($file_list,$result,'info');

    $new_file_name = $user.date('YmdHis').$_FILES['uploadfile']['name'];
    
    if($file_list){
        $fw = fopen('./data/'.$type.'/'.$type.$new_file_name.'.dat','w');
        $content = '';
        foreach($file_list as $val){
            $content .=$val."\n";
        }
        fwrite($fw,$content);
        fclose($fw);
    }else{
        echo "<script language =\"javascript\">alert('upload faild!Your CSV file is not standard!');history.back(-1)</script>\r\n";
        exit;
    }


    $sql = "insert ignore into files_up_down SET upfilename = '".$new_file_name."',type = '".$type."',uptime =
    '".date('Y-m-d H:i:s')."',location = '".$location."',terms = '".$terms."',user = '".$user."',status = 'NEW'";
    
    if($db->exec($sql)){
        echo "<script language =\"javascript\">alert('upload success!');window.location.href='http://reporting.soarinfotech.com/mermining/upload.php';</script>\r\n";
    }else{
        echo "<script language =\"javascript\">alert('upload faild!');history.back(-1)</script>\r\n";
    }

}


//创建文件
function Directory($dir){
    if(is_dir($dir) || @mkdir($dir,0777)){
        chmod($dir, 0777);
        //echo $dir."创建成功<br>";
    }else{
        $dirArr=explode('/',$dir);
        array_pop($dirArr);
        $newDir=implode('/',$dirArr);
        Directory($newDir,0777);
        if(@mkdir($dir)){
            chmod($dir, 0777);
            // echo $dir."创建成功<br>";
        }
    }
}
/* *
 * @$file_arr 从文件打开的数组
 * @$database_arr 从数据库查询出来的结果数组 二维
 * @$str 查询数据库数据的字段名
 * */
// function filter($file_arr,$database_arr,$str){
//     array_shift($file_arr);//去掉文件中的第一个无效的标题
//     $file_arr = array_unique($file_arr);//去重
//     if($database_arr){
//         $database_arr_0 = array();
//         //$database_arr = array_column($database_arr,$str);//查询数据库的结果改成一维数组PHP5.5+
//         foreach ($database_arr as $value) {
//             $database_arr_0[] = $value[$str];
//         }
//         $test_arr = array_flip($database_arr_0);//把数据库结果的值变成数组的键
//         foreach($file_arr as $k => $value){
//             $value = $value.'-'.date('Y-m');
//             if(isset($test_arr[$value])){
//                 unset($file_arr[$k]);
//             }
//         }
//         return $file_arr;
//     }else{
//         return $file_arr;
//     }
// }

