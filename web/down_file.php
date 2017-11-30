<?php
$filename = @$_GET['filename'];
function down_file($filename)
{
$fileinfo = pathinfo($filename);
header('Content-type: application/x-'.$fileinfo['extension']);
header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
header('Content-Length: '.filesize($filename));
readfile($filename);
}
down_file($filename);
?>