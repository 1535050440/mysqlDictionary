<?php
    /**
        * php生成mysql数据字典类          GenerationDbTable
        * @param public str $db           数据库名称
        * @param public str $dbpass       数据库密码默认为空
        * @param public str $dbusr        用户名默认值为root
        * @param public str $dbserver     默认为127.0.0.1
        * @param public str $copy         默认为鹰眼.益云 iyiyun.com
        * @author          Hey Jun <dinghaijun@dinghaijun.com>
        * @version         V1.0
        * @copyright       Copyright (c) 2012. iyiyun.com
        * @modifier        Hey Jun <dinghaijun@dinghaijun.com>
        * @lastmodified    Hey Jun   2012-11-22 21:41
        *此类改自向阳群共享的php文件，改成面向对象的写法,做了数据处理和部分样式处理;
        *解决了内存溢出问题和超时问题，测试通过了usdo数据测试;
        *public参数为须赋值参数(不赋值则为默认值).赋值请按以上参数顺序,最少一个参数那就是数据库了,哈哈哈
        *
     */
    /*---实例化示例START---*/
        $m=new GenerationDbTable("thinkcmf",'kai95926','root','115.28.227.171');
        $m->ShowTables();
    /*---实例化示例END---*/
     class GenerationDbTable{
            public $database;
            public $dbusername;
            public $dbpassword;
            public $dbserver;
            public $copy;
            private $mysql_conn;
            private $table_result;
         function __construct($db,$dbpass='',$dbusr='root',$dbserver='',$copy='<a href="http://iyiyun.com">鹰眼.益云</a>'){
            $this->database   =$db;
            $this->dbusername =empty($dbusr)?'root':$dbusr;
            $this->dbpassword =empty($dbpass)?'':$dbpass;
            $this->dbserver   =$dbserver;
            $this->copy       =$copy;          
        }
       //配置数据库
        public function ShowTables(){
            //临时设置超时时间不限;
            set_time_limit(0);
            //内存限制
            ini_set('memory_limit', '250M'); 
                //配置
                $title =$this->database.'数据字典';
                try {
                    $c=@mysql_connect($this->dbserver,$this->dbusername,$this->dbpassword);
                } catch (Exception $e) {               
                }
                if(!$c){      
                    //die(mysql_error());//调试使用
                    //错误输出
                   /* echo "
                    <body style='text-align:center;'>
                        <div style='width:960px;height:auto;min-height:200px;border:1px solid green;margin:0 auto;'>
                        <p style='margin-top:100px;'>人品太差，连接数据库失败<span style='color:red'>OX_XO</span></p>
                        </div>
                    </body>";
                    die;*/
                }
                $this->mysql_conn = $c;
                mysql_select_db($this->database,$this->mysql_conn);
                mysql_query('SET NAMES utf8',$this->mysql_conn);
                $this->table_result = mysql_query('show tables', $this->mysql_conn);
                //取得所有的表名
                while ($row = mysql_fetch_array($this->table_result)) {
                $tables[]['TABLE_NAME'] = $row[0];
                }
                //循环取得所有表的备注及表中列消息
                foreach ($tables AS $k=>$v) {
                $sql = 'SELECT * FROM ';
                $sql .= 'INFORMATION_SCHEMA.TABLES ';
                $sql .= 'WHERE ';
                $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$this->database}'";
                $this->table_result = mysql_query($sql, $this->mysql_conn);
                while ($t = mysql_fetch_array($this->table_result) ) {
                $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
                }
                $sql = 'SELECT * FROM ';
                $sql .= 'INFORMATION_SCHEMA.COLUMNS ';
                $sql .= 'WHERE ';
                $sql .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$this->database}'";
                $fields = array();
                $field_result = mysql_query($sql, $this->mysql_conn);
                while ($t = mysql_fetch_array($field_result) ) {
                $fields[] = $t;
                }
                $tables[$k]['COLUMN'] = $fields;
                }
                mysql_close($this->mysql_conn);
                
                $html = '';
                //循环所有表
                foreach ($tables AS $k=>$v) {
                //$html .= '<p><h2>'. $v['TABLE_COMMENT'] . '&nbsp;</h2>';
                $html .= '<table border="1" cellspacing="0" cellpadding="0" align="center">';
                $html .= '<caption>' . $v['TABLE_NAME'] .' '. $v['TABLE_COMMENT']. '</caption>';
                $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th>
                <th>允许非空</th>
                <th>自动递增</th><th>备注</th></tr>';
                $html .= '';
                foreach ($v['COLUMN'] AS $f) {
                $html .= '<tr><td class="c1">' . $f['COLUMN_NAME'] . '</td>';
                $html .= '<td class="c2">' . $f['COLUMN_TYPE'] . '</td>';
                $html .= '<td class="c3">&nbsp;' . $f['COLUMN_DEFAULT'] . '</td>';
                $html .= '<td class="c4">&nbsp;' . $f['IS_NULLABLE'] . '</td>';
                $html .= '<td class="c5">' . ($f['EXTRA']=='auto_increment'?'是':'&nbsp;') . '</td>';
                $html .= '<td class="c6">&nbsp;' . $f['COLUMN_COMMENT'] . '</td>';
                $html .= '</tr>';
                }
                $html .= '</tbody></table></p>';
                }
                //输出
                echo '<html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>'.$title.'</title>
                <style>
                body,td,th {font-family:"宋体"; font-size:12px;}
                table{border-collapse:collapse;border:1px solid #CCC;background:#efefef;min-width:800px;width:auto;}
                table caption{text-align:left; background-color:#92C5FF; line-height:2em; font-size:14px; font-weight:bold; }
                table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;text-align:center;}
                table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;text-align:center;}
                h1,h2,h3,h4,h5{text-align:center;}
                .c1{ width: 120px;text-align:left;}
                .c2{ width: 120px;}
                .c3{ width: 70px;}
                .c4{ width: 80px;}
                .c5{ width: 80px;}
                .c6{ width: 270px;}
                a{color:#3E6DB3;}
                </style>
                </head>
                <body>';
                echo '<h1>'.$title.'</h1>';
                echo $html;
                echo "<h2>copyright&copy by {$this->copy}</h2>";
                echo '
                </body>
                </html>';  
        }
    } 
?>
