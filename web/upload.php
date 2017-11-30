<?php
require_once('comm.php');
$dbconf = new Zend_Config_Ini(DB_CONFIG, 'MERMINING');
$db = new PDO($dbconf->db->host.$dbconf->db->name, $dbconf->db->user, $dbconf->db->pass);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// $dsn = "mysql:host=localhost;dbname=mermining";
// $db = new PDO($dsn,'mermining','9/SKkNfysTU');
// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$page_now = @$_GET['page'];
if(empty($page_now)||$page_now<0){
    $page_now = 1;
}else {
    $page=$page_now;
}
$user = $_SERVER['PHP_AUTH_USER'];

$sql = "select count(*) AS cut from files_up_down WHERE user='".$user."'";
$c = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$cnt = $c[0]['cut'];
if(!$cnt){
    $page = 1;
    $page_num = 0;
    $data =array();
}else{
    $page_size = 20;
    $page_num = ceil($cnt/$page_size);
    if($page_now>=$page_num){
        $page=$page_num;
    }else {
        $page=$page_now;
    }
    $offset = ($page-1)*$page_size;
    $sql_sel = "select * from files_up_down where user = '".$user."' order by id desc limit $offset , $page_size";
    $pdosel = $db->query($sql_sel,PDO::FETCH_ASSOC);
    while($row = $pdosel->fetch()){
        $data[] = $row;
    }
}

//var_dump($data);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Merchant Mining</title>
    <link href="./css/style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="./js/jquery.js"></script>
</head>
<body>

<div class="place">
    <span>File up</span>
</div>

<div class="mainindex">

<!--    <div class="xline"></div>-->

    <ul class="iconlist">

        <li>
            <img src="./images/ico04.png" />
            <form action="test.php" method="post" enctype="multipart/form-data" id="frm">
                类型 : <select name="type" style="height: 25px;background: burlywood;margin-bottom: 10px" onchange="loca_hid(this)">
                        <option value="keywords">Search Vol</option>
                        <option value="similarweb">Similarweb</option>
                        <option value="shoponline">shoponline</option>
                        <option value="bingsearch">bingsearch</option>
                        <option value="semrushD">Semrush Domain</option>
                        <option value="semrushK">Semrush Keywords</option>
                        <!-- <option value="google_index">Google Indexs</option> -->
                    </select>

                <script>
                    function loca_hid(obj){
                        switch (obj.value){
                            case 'keywords':
                                $('#locations').css('display','inline-block');
                                $('#terms').css('display','block');
                                break;
                            case 'semrush':
                                $('#locations').css('display','inline-block');
                                $('#terms').css('display','none');
                                break;
                            default:
                                $('#locations').css('display','none');
                                $('#terms').css('display','none');
                        }
                    }
                </script>
                <span style="display: inline-block" id="locations">地区 : <select name="location" style="height: 25px;background: burlywood;margin-bottom: 10px">
                    <option value="0">ALL</option>
                    <option value="2840">US</option>
                    <option value="2124">CA</option>
                    <option value="2276">DE</option>
                    <option value="2826">UK</option>
                    <option value="2036">AU</option>
                    <option value="2040">AT</option>
                    <option value="2756">CH</option>
                    <option value="2250">FR</option>
                    <option value="2392">JP</option>
                </select></span>

                <p id="terms">terms : <input type="text" name="terms" style="height: 25px;width: 300px;background: burlywood;margin-bottom: 10px"/></p>
                <a style="margin-left: 42.5%" href="javascript:void(0);" class="ibtn" onclick="uploadfile()">select a file(csv).....</a>
                <input type="file" name="uploadfile" id="uploadfile" value="" style="display: none;">
                <span> <a style="margin-left: 20px" class="ibtn" id="subfile">upload</a></span>
            </form>

        </li>

    </ul>

    <div class="welinfo" style="height: 100%; width: 100%; margin: 0 auto;">
        <div class="rightinfo">
            <div class="tools">

<!--                <ul class="toolbar">-->
<!--                    <li class="click"><span><img src="images/t01.png" /></span>添加</li>-->
<!--                    <li class="click"><span><img src="images/t02.png" /></span>修改</li>-->
<!--                    <li><span><img src="images/t03.png" /></span>删除</li>-->
<!--                    <li><span><img src="images/t04.png" /></span>统计</li>-->
<!--                </ul>-->
<!---->
<!---->
<!--                <ul class="toolbar1">-->
<!--                    <li><span><img src="images/t05.png" /></span>设置</li>-->
<!--                </ul>-->

            </div>


            <table class="tablelist">
                <thead>
                    <tr>
                        <th>uptime</th>
                        <th>upfile</th>
                        <th>location</th>
                        <th>status</th>
                        <th>success num</th>
                        <th>error num</th>
                        <th>createdate</th>
                        <th>download</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                function country($num){
                    switch($num){
                        case '2840':
                            $local = 'US';
                            break;
                        case '2124':
                            $local = 'CA';
                            break;
                        case '2276':
                            $local = 'DE';
                            break;
                        case '2826':
                            $local = 'UK';
                            break;
                        case '2036':
                            $local = 'AU';
                            break;
                        case '2040':
                            $local = 'AT';
                            break;
                        case '2756':
                            $local = 'CH';
                            break;
                        case '2250':
                            $local = 'FR';
                            break;
                        case '2392':
                            $local = 'JP';
                            break;
                        case '0':
                            $local = 'ALL';
                            break;
                    }
                    return $local;
                }
                foreach ($data as $key => $value) {
                    $value['location'] = country($value['location']);
                    if($value['type']=='keywords'){
                        $value['type'] = 'search vol';
                    }
                    $value['upfilename'] = $value['type'].'____'.$value['upfilename'];
                    if($value['status']=='DOWN'){
                        echo <<<ccc
<tr>
<td>{$value['uptime']}</td>
<td>{$value['upfilename']}</td>
<td>{$value['location']}</td>
<td>{$value['status']}</td>
<td>{$value['senum']}</td>
<td>{$value['ernum']}</td>
<td>{$value['createdate']}</td>
<td><a href="http://reporting.soarinfotech.com/mermining/down_file.php?filename={$value['downfilename']}">download</a></td>
</tr>
ccc;
                    }else if($value['status']=='FAILD'){
                        echo <<<ccc
<tr>
<td>{$value['uptime']}</td>
<td>{$value['upfilename']}</td>
<td>{$value['location']}</td>
<td>{$value['status']}</td>
<td>{$value['senum']}</td>
<td>{$value['ernum']}</td>
<td>{$value['createdate']}</td>
<td>error</a></td>
</tr>
ccc;
                    }else{
                        echo <<<ccc
<tr>
<td>{$value['uptime']}</td>
<td>{$value['upfilename']}</td>
<td>{$value['location']}</td>
<td>{$value['status']}</td>
<td>{$value['senum']}</td>
<td>{$value['ernum']}</td>
<td>{$value['createdate']}</td>
<td> wait down</a></td>
</tr>
ccc;
                    }

                }
                ?>
                </tbody>
            </table>


            <div class="pagin">
                <div class="message">共<i class="blue"><?php echo $cnt ?></i>条记录，当前显示第&nbsp;<i class="blue"><?php echo $page; ?>&nbsp;</i>页,总共有&nbsp;<i class="blue"><?php echo $page_num; ?>&nbsp;</i>页</div>
                <ul class="paginList">
                    <?php
                    $prev = $page-1;
                    if($prev<=0){
                        $prev = 1;
                    }
                    $next = $page+1;
                    if($next>=$page_num){
                        $next = $page_num;
                    }
                    echo <<<ppp
<li class="paginItem"><a href="?page=1">首页</a></li>
<li class="paginItem"><a href="?page={$prev}"><span class="pagepre"></span></a></li>
<li class="paginItem"><a href="?page={$next}"><span class="pagenxt"></span></a></li>
<li class="paginItem"><a href="?page={$page_num}">尾页</a></li>
ppp;
                    ?>


            <div class="tip">
                <div class="tiptop"><span>提示信息</span><a></a></div>

                <div class="tipinfo">
                    <span><img src="images/ticon.png" /></span>
                    <div class="tipright">
                        <p>是否确认对信息的修改 ？</p>
                        <cite>如果是请点击确定按钮 ，否则请点取消。</cite>
                    </div>
                </div>

                <div class="tipbtn">
                    <input name="" type="button"  class="sure" value="确定" />&nbsp;
                    <input name="" type="button"  class="cancel" value="取消" />
                </div>

            </div>




        </div>
    </div>
    <div class="xline"></div>
    <div class="box"></div>
    <script type="text/javascript">
        function uploadfile(){
            $("#uploadfile").click();
        }

        $("#subfile").click(function(){
            if($("#uploadfile").val()==""){
                alert('please select a csv file!');
                return false;
            }
            $("#frm").submit();
        });

    </script>
</body>
</html>
