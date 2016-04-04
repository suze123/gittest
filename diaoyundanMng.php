
<?php
session_start();
require_once('config.php');
if(!isset($_SESSION['userid'])){
echo json_encode(array('res'=>'Error','msg'=>'没有登录或已超时!'));
return;
}
$act=addslashes($_POST['act']);
if(!($_SESSION['permission']&0x8000)){
echo json_encode(array('res'=>'Error','msg'=>'无权使用人员管理功能!'));
return;
}
if($act=='get_slt')
select();
if($act=='get_cydw')
getDanwei();
if($act=='get_jsy')
select_jsy();
if($act=='get_mydwmc'){
echo $_SESSION['danweimc'];
exit();
}
if($act=='get_time')
timeNow();
if($act=='get_myck')
select_ck();	
if($act=='DYDH')
   dydh();
if($act=='get_dj')
   yancaodengji();
if($act=='get_xkc')
   xiankucun();  
if($act=='add')
   add();
if($act=='table_reset')
   tablereset();
if($act=='Fenyexianshi')
	xianshibio();

//下拉框选项查询函数 
function select(){
$dbh=dbconnect();
$biao=addslashes($_POST['table']);
$field=addslashes($_POST['field']);
$mydw=$_SESSION['danweimc'];

if($biao=='danweidm')
$sql="select ".$field." from ".$biao." where dwmc<>'".$mydw."' and dwjb<>4 order by ".$field;
else	
$sql="select ".$field." from ".$biao." where CPH not in(select CPH from diaoyundan where DYDH in(select YSDH from diaoyunxiangbiao where ZT=0)) order by ".$field;

$str="<option value='提示'>---->>请选择<<----</option>";
	foreach($dbh->query($sql) as $r){
		if($field=='dwmc')
		$str .= "<option value='{$r['dwmc']}'>{$r['dwmc']}</option>";
		elseif($field=='CPH')
		$str .= "<option value='{$r['CPH']}'>{$r['CPH']}</option>";
	}
	echo $str;
exit();
}
//查询单位名称
function getDanwei(){
$dbh=dbconnect();
$cph=addslashes($_POST['CPH']);	
$sql="select CYRID,CZZ from cheliang where CPH='".$cph."'" ;
	foreach($dbh->query($sql) as $r){
		$str .= $r['CYRID'];
		$str2 .= $r['CZZ'];
	}
$sql="select DWMC from chengyunren where id='".$str."'" ;
	foreach($dbh->query($sql) as $r2){
		$sr .= $r2['DWMC'];
	}
	$res=array('danwei'=>$sr,'CZZ'=>$str2);
    echo json_encode($res);
	//echo $sr;
	//echo $cph;
exit();
}

//获取驾驶员列表
function select_jsy(){
$dbh=dbconnect();
$biao=addslashes($_POST['table']);
$field=addslashes($_POST['field']);	
$cph=addslashes($_POST['CPH']);	
$sql="select CYRID from cheliang where CPH='".$cph."'" ;
 $cyrid=$dbh->query($sql)->fetchColumn(0);

$sql="select ".$field." from ".$biao." where CYRID=".$cyrid." and XM not in(select JSXM from diaoyundan where DYDH in(select YSDH from diaoyunxiangbiao where ZT=0))";


	foreach($dbh->query($sql) as $r){	
		$str .= "<option value='{$r['XM']}'>{$r['XM']}</option>";
	}
	echo $str;
exit();
}
//增加
function add(){
    $dbh=dbconnect();
    $danhao=addslashes($_POST['danhao']);
    $cph=addslashes($_POST['cph']);
    $jsyxm=addslashes($_POST['jsyxm']);
    $kdsj=addslashes($_POST['kdsj']);
    $cydw=addslashes($_POST['cydw']);
    $chck=addslashes($_POST['chck']);
    $ycdj=addslashes($_POST['ycdj']);
	$dhdd=addslashes($_POST['dhdd']);
    $username=$_SESSION['username'];
    $zhongliang=(int)addslashes($_POST['jianshu'])*50;
    $jianshu="";
    $jianshu=addslashes($_POST['jianshu']);
	$sqlc="select id from chengyunren where DWMC='".$cydw."'";
    $cyrid=$dbh->query($sqlc)->fetchColumn(0);
	
	$sqlj="select id from jiashiyuan where XM='".$jsyxm."'";
    $jsyid=$dbh->query($sqlj)->fetchColumn(0);
	
	$sqldj="select DJBH from dengjibiao where DJMC='".$ycdj."'";
    $djbh=$dbh->query($sqldj)->fetchColumn(0);
	
	$sqlckid="select id from cangku where CKMC='".$chck."'";
    $fhckid=$dbh->query($sqlckid)->fetchColumn(0);
	
     $sqldwbh="select dwbm from danweidm where dwmc='".$dhdd."'";
     $dhdwbh=$dbh->query($sqldwbh)->fetchColumn(0);
	
	$sqlc="select count(*) as recnum from diaoyundan where DYDH='".$danhao."'";
    $nc=$dbh->query($sqlc)->fetchColumn(0);
    
	if($nc==0){
		//echo $cyrid.$jsyid;
	$sql="insert into diaoyundan (CYRID,CYRDW,CPH,JSYID,JSXM,BGY,DYDH,CFSJ) values ('$cyrid','$cydw','$cph','$jsyid','$jsyxm','$username','$danhao','$kdsj')";
	$r=$dbh->exec($sql);
	}


    if($jianshu<>""){

            
            $sql="select FHJS from diaoyunxiangbiao where YSDH='".$danhao."' and DJMC='".$ycdj."' and fhckid=".$fhckid;
           (int)$yyjs=$dbh->query($sql)->fetchColumn(0);

            if($yyjs>0) 
            {
            $zjs=(int)$yyjs+(int)$jianshu;
            $zzl=$zjs*50;
            $sql2="update diaoyunxiangbiao set FHJS=".$zjs.",FHZL=".$zzl." where YSDH='".$danhao."' and DJMC='".$ycdj."' and fhckid=".$fhckid;
            
            }
            else
            $sql2="insert into diaoyunxiangbiao (YSDH,DJBH,DJMC,FHJS,FHZL,fhckid,ZT,ZTMS,dhdwbh) values ('$danhao','$djbh','$ycdj',$jianshu,$zhongliang,$fhckid,0,'调运在途','$dhdwbh')";
	   
           
            $r2=$dbh->exec($sql2);
		//echo $sql2;
	    }
		
	if($r==1||$r2==1){
        $res=array('res'=>'Ok','msg'=>'出库成功');
        }else
        $res=array('res'=>'Error','msg'=>'出库不成功！');
        echo json_encode($res);
    exit();
	
}

//显示表
function xianshibio(){
$dbh=dbconnect();
$zjs=0;
$zzl=0;
$j=0;
$dydh=addslashes($_POST['danhao']);
$sql="select DJBH,DJMC,FHJS,FHZL from diaoyunxiangbiao where YSDH='".$dydh."'" ;
$str="<table align='center' width='700'><thead><tr height='40'> <th colspan='2' rowspan='2' width='130' background='../images/biaotou.jpg'></th> <th colspan='2'>发货</th> <th colspan='3'>收货</th> <th colspan='2' rowspan='2' width='210'>备注</th></tr> <tr height='30'> <th width='50'>件数</th> <th width='80'>重量(kg)</th> <th width='50'>件数</th> <th width='80'>重量(kg)</th> <th width='100'>接受仓库</th></tr></thead><tbody>";

foreach($dbh->query($sql) as $r){
    $str .= "<tr height='30'><td width='50'>{$r['DJBH']}</td><td width='80'>{$r['DJMC']}</td><td>{$r['FHJS']}</td><td>{$r['FHZL']}</td><td></td><td></td><td></td><td></td></tr>";
    $zjs+=$r['FHJS'];
    $zzl+=$r['FHZL'];
      $j++;
	}
if($zjs<>0&&$zzl<>0){
$str .= "<tr height='30'><td width='50'></td><td width='80'></td><td>$zjs</td><td id='table_td_zzl'>$zzl</td><td></td><td></td><td></td><td></td></tr>";
$j++;
}
	
for($i=0;$i<15-$j;$i++){
	$str .= "<tr height='30'><td width='50'></td><td width='80'></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
   }
$str .= "</tbody></table>{$ncstr}";
echo $str;
exit();
}

//获取当前时间
function timeNow(){
    echo date("Y-m-d h:i:s");
    exit();
    //仅仅就只是获取当前的时间
    //有点多余的感觉啊
}

//生成调运单号和条形码
function dydh(){
	$dbh=dbconnect();
	$Y=date("Y");
	$dwmc=$_SESSION['danweimc'];
	$sql="select dwbm from danweidm where dwmc='$dwmc'";
	foreach($dbh->query($sql) as $r){
		$dwbm= $r['dwbm'];
	}
	$sql="select DYDH from diaoyundan where DYDH like '%$dwbm%' order by id desc limit 0,1";
	foreach($dbh->query($sql) as $r){
		$DYDH= $r['DYDH'];
	}
	$dydh1=substr($DYDH,-4);
	$dydh1=$dydh1+1;
	$n=strlen($dydh1);
	for($i=0;$i<4-$n;$i++){
		$buquan.=0;
	}
	$dydh1=$buquan.$dydh1;
	$dydh=$Y.$dwbm.$dydh1;
	$dydh2="<img src='../barcode/test.php?codebar=BCGcode39&text=$dydh'>";
	$res=array('txm'=>$dydh2,'dydh'=>$dydh);
    echo json_encode($res);
    exit();
}
//获取我的仓库列表
function select_ck(){
	$dbh=dbconnect();
	$danweibm=$_SESSION['danweibm'];//单位编码
	$sql="select CKMC from cangku where DWBM='".$danweibm."'";
	foreach($dbh->query($sql) as $r){	
		$str .= "<option value='{$r['CKMC']}'>{$r['CKMC']}</option>";
	}
	echo $str;
    exit();
}	
//获取烟草等级列表
function yancaodengji(){
	$dbh=dbconnect();
	$ckmc=addslashes($_POST['ckmc']);
	$sql="select id from cangku where CKMC='".$ckmc."'" ;
    foreach($dbh->query($sql) as $r){$str= $r['id'];}
	$sql="select dengjibiao.DJMC,dengjibiao.DJBH from dengjibiao,dengjikucun where dengjibiao.DJBH=dengjikucun.DJBH and dengjikucun.CKID='".$str."'";
	foreach($dbh->query($sql) as $r){	
		$str1 .= "<option value='{$r['DJBH']}'>{$r['DJMC']}</option>";
	}
	echo $str1;
    exit();
}	

//获取已知等级的现库存量
function xiankucun(){
	$dbh=dbconnect();
	$djmc=addslashes($_POST['dnegji']);	
	$ckmc=addslashes($_POST['ckmc']);
	$sql="select id from cangku where CKMC='".$ckmc."'" ;
    foreach($dbh->query($sql) as $r){$ckid .= $r['id'];}	
	$sql="select DJBH from dengjibiao where DJMC='".$djmc."'" ;
    foreach($dbh->query($sql) as $r){$str .= $r['DJBH'];}
	$sql="select FHJS from diaoyunxiangbiao where fhckid='".$ckid."' and DJBH='".$str."' and ZT=0";
	foreach($dbh->query($sql) as $r){$zjs+= $r['FHJS'];}
    $sql="select KC from dengjikucun where DJBH='".$str."' and CKID=".$ckid;
	foreach($dbh->query($sql) as $r){$str1= $r['KC'];}
	echo $str1-$zjs;
	
    exit();	
} 

//重置表单
function tablereset(){
	$dbh=dbconnect();
	$dydh=addslashes($_POST['danhao']);	
	$sql="DELETE FROM diaoyundan WHERE DYDH='".$dydh."'";
	$r1=$dbh->exec($sql);
	$sql2="DELETE FROM diaoyunxiangbiao WHERE YSDH='".$dydh."'";
    $r2=$dbh->exec($sql2);

	if($r1==1 and $r2>0){
        $res=array('res'=>'Ok','msg'=>'重置成功！');
        }else
        $res=array('res'=>'Error','msg'=>'重置无效！');
        echo json_encode($res);
    exit();	
} 


$res=array('res'=>'Error','msg'=>'未知原因的错误！');
echo json_encode($res);
?>
