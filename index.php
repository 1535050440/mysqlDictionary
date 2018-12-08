<?php
/**
 * 生成mysql数据字典
 */
header ( "Content-type: text/html; charset=utf-8" );
// 配置数据库
$dbserver = !empty($_POST['dbserver'])?$_POST['dbserver']:"localhost";
$dbusername = !empty($_POST['dbusername'])?$_POST['dbusername']:"root";
$dbpassword = !empty($_POST['dbpassword'])?$_POST['dbpassword']:"root";
$database = !empty($_POST['databases'])?$_POST['databases']:"test";
if (empty($database)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <link rel="shortcut icon" href="/favicon.ico" />
        <title>MySQL数据字典生成</title>
        <style type="text/css">
            body {
                background:-moz-linear-gradient(top,#262822,#1c456e);   /*火狐*/
                background:-ms-linear-gradient(top, #262822,  #1c456e); /*IE10以下没效果*/
                background:-webkit-gradient(linear, 0% 0%, 0% 100%,from(#262822), to(#1c456e)); /*谷歌,360*/
                height:720px;
            }
            h1 {
                font-size:50pt;
                color:#ffffff;
                font-family:楷体,Arial,Courier;
                position:relative;
                text-align:center;  
                left:16px;
                top:50px;
            }

            #submits table{
                color:#ffffff;
                position:relative;
                left:600px;
                top:70px;
                line-height:50px;
            }
            #image input {
                width:80px;
                height:40px;
                font-size:25px;
                border-radius:5px;
                font-family:楷体;
            }
            #image input:hover {
                background:#ffffff;
                color:#dcb106;
            }
            th {
                font-family:楷体,Arial,Courier;
                font-size:20pt;
                width:200px;
            }

            td {
                text-indent:10px;
            }
        </style>
    </head>
    <body>
    <div>
        <form id='submits' action="/test/Mysql_Dictionary.php" method="post" >
            <h1>MySQL数据字典生成</h1>
            <table>
                <tr>
                    <th>服务器:</th>
                    <td>
                       <input type="text" name='dbserver' id='dbserver' value=''>
                    </td>
                </tr>
                <tr>
                    <th>用户名:</th>
                    <td>
                       <input type="text" name='dbusername' id='dbusername' value=''>
                    </td>
                </tr>
                <tr>
                    <th>密码:</th>
                    <td>
                       <input type="password" name='dbpassword' id='dbpassword' value=''>
                    </td>
                </tr>
                <tr>
                    <th>数据库名:</th>
                    <td>
                       <input type="text" name='databases' id='databases' value=''>
                    </td>
                </tr>
                <tr>
                    <th colspan="2" align="center" id="image">
                        <input type="button" style="width:70px;height:40px;border-radius:5px;" value='生成' onclick="submit_show()">
                    </th>
                </tr>
            </table>
        </form>
    </div>
    </body>
    </html>
    <script type="text/javascript" src="http://www.xcn.ren/Public/js/jquery.min.js"></script>
    <script type="text/javascript">

    function submit_show(){
        databases = $('#databases').val();
        if (databases=="") {
            alert("请填写数据库名！");
            return false;
        }
        $('#submits').submit();
    }

    </script>
    <?php
    exit;
}
 
$mysql_conn = @mysqli_connect ( "$dbserver", "$dbusername", "$dbpassword" ) or die ( "Mysql connect is error." );
mysqli_select_db ( $mysql_conn, $database );
mysqli_query (  $mysql_conn, 'SET NAMES utf8');
$table_result = mysqli_query ( $mysql_conn ,'show tables' );
// 取得所有的表名
while ( $row = mysqli_fetch_array ( $table_result ) ) {
    $tables [] ['TABLE_NAME'] = $row [0];
}
 
// 循环取得所有表的备注及表中列消息
foreach ( $tables as $k => $v ) {
    $sql = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.TABLES ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
    $table_result = mysqli_query ( $mysql_conn, $sql );
    while ( $t = mysqli_fetch_array ( $table_result ) ) {
        $tables [$k] ['TABLE_COMMENT'] = $t ['TABLE_COMMENT'];
    }
     
    $sql = 'SELECT * FROM ';
    $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
    $sql .= 'WHERE ';
    $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";
     
    $fields = array ();
    $field_result = mysqli_query ( $mysql_conn, $sql );
    while ( $t = mysqli_fetch_array ( $field_result ) ) {
        $fields [] = $t;
    }
    $tables [$k] ['COLUMN'] = $fields;
}
mysqli_close ( $mysql_conn );
 
$html = '';
$header_index = '<div id="floatTips"><ul>';
// 循环所有表
foreach ( $tables as $k => $v ) {
    // $html .= '<p><h2>'. $v['TABLE_COMMENT'] . '&nbsp;</h2>';
    $header_index .= '<li><a href="#' . $v ['TABLE_NAME'] . '" title="购买意向表">' . $v ['TABLE_NAME'] . '</a>(' . $v ['TABLE_COMMENT'].' )</li>';
    $html .= '<div style="page-break-before: always;">';
    $html .= '<h2><a name="' . $v ['TABLE_NAME'] . '"></a>' . $v ['TABLE_NAME'] . ' &nbsp;&nbsp;&nbsp; ' . $v ['TABLE_COMMENT'] . '</h2>';
    $html .= '<table class="print" width="100%"><tbody><tr><th width="50">字段名</th><th width="80">数据类型</th><th width="70">默认值</th> <th width="60">允许非空</th><th width="50">自动递增</th><th>备注</th></tr>';
    $html .= '';
     
    foreach ( $v ['COLUMN'] as $f ) {
        $html .= '<tr class="even"><td nowrap="nowrap">' . $f ['COLUMN_NAME'] . '</td>';
        $html .= '<td xml:lang="en" dir="ltr" nowrap="nowrap">' . $f ['COLUMN_TYPE'] . '</td>';
        $html .= '<td>&nbsp;' . $f ['COLUMN_DEFAULT'] . '</td>';
        $html .= '<td nowrap="nowrap">&nbsp;' . $f ['IS_NULLABLE'] . '</td>';
        $html .= '<td>' . ($f ['EXTRA'] == 'auto_increment' ? '是' : '&nbsp;') . '</td>';
        $html .= '<td>&nbsp;' . $f ['COLUMN_COMMENT'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table></p>';
}
$header_index .='</ul><span><a href="#top">返回顶部↑</a></span></div>';
 
// 输出
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" xml:lang="zh" xmlns="http://www.w3.org/1999/xhtml" lang="zh">
<head>
<title>'.$database.'</title>
<link rel="shortcut icon" href="./favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript">
//<![CDATA[
var tips; var theTop = 50; var old = theTop;
function initFloatTips() {
  tips = document.getElementById("floatTips");
  moveTips();
};
function moveTips() {
  var tt=50;
  if (window.innerHeight) {
    pos = window.pageYOffset
  }
  else if (document.documentElement && document.documentElement.scrollTop) {
    pos = document.documentElement.scrollTop
  }
  else if (document.body) {
    pos = document.body.scrollTop;
  }
  pos=pos-tips.offsetTop+theTop;
  pos=tips.offsetTop+pos/10;
  if (pos < theTop) pos = theTop;
  if (pos != old) {
    tips.style.top = pos+"px";
    tt=10;
  }
  old = pos;
  setTimeout(moveTips,tt);
}
//!]]>
</script>
<style type="text/css">
<!--html {font-size: 82%;}body {padding:0;margin:0.5em;color:#000000;background:#F5F5F5;}body,td,th{font-family:Microsoft YaHei,Arial,Helvetica,sans-serif,Simsun;}div#floatTips ::-webkit-scrollbar {width: 7px;height: 7px}div#floatTips ::-webkit-scrollbar-track-piece {background-color:#000;-webkit-border-radius: 6px;border: 1px solid #111;border-bottom-color: #555;border-right-color: #555}div#floatTips ::-webkit-scrollbar-thumb:vertical {background-color: #eee;-webkit-border-radius: 6px}div#floatTips ::-webkit-scrollbar-thumb:vertical:hover,div#floatTips ::-webkit-scrollbar-thumb:horizontal:hover {background-color: #fff}div#floatTips ::-webkit-scrollbar-thumb:vertical:active,,div#floatTips ::-webkit-scrollbar-thumb:horizontal:active {background-color: #aaa}div#floatTips ::-webkit-scrollbar-thumb:horizontal {background-color: #eee;-webkit-border-radius: 6px}div#floatTips ::-webkit-scrollbar-button:start:decrement,div#floatTips ::-webkit-scrollbar-button:end:increment  {display: block;background-color: transparent}div#floatTips ::-webkit-scrollbar-corner {background-color: transparent}h2 {font-size:120%;font-weight:bold;}table td {padding:3px}table tr.odd th,.odd {background: #E5E5E5;}table tr.even th,.even {background: #D5D5D5;}table tr.odd th,table tr.odd,table tr.even th,table tr.even {text-align:left;}.odd:hover,.even:hover,.hover {background: #CCFFCC;color: #000000;}table tr.odd:hover th,table tr.even:hover th,table tr.hover th {background:#CCFFCC;color:#000000;}div#floatTips{ position:absolute;border:solid 1px #777;padding:3px;top:50px;right:15px;width:200px;background:#666;color:white;opacity: 0.8;filter:alpha(opacity=80);color:#fff;-webkit-box-shadow:0 0 20px rgba(0,0,0,0.5);-webkit-border-radius: 5px;text-shadow: 0 1px 0 #111;-moz-box-shadow:0 0 20px rgba(0,0,0,0.5);-moz-border-radius: 10px;border-radius: 5px;box-shadow:0 0 20px rgba(0,0,0,0.5);}div#floatTips ul{padding:0px;margin:3px;height:400px; overflow-y:auto}div#floatTips ul li{ list-style:none; height:20px; width:100%; line-height:20px; text-overflow:ellipsis;overflow:hidden; white-space:nowrap;}div#floatTips a:link,div#floatTips a:hover,div#floatTips a:visited,div#floatTips a:active{color:#fff; text-decoration:none}-->
</style>
<head>
<body onload="initFloatTips()">
<a name="top"></a>';
echo $html;
echo $header_index;
echo '</body></html>';
 
?>