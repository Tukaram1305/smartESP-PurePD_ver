<?php
//$f = fopen("savedSettings.json","w");
//fwrite($f,json_decode($_POST["sJson"]));
if ($_POST['MODE']=='save')
{
	$path = '../cfg_files/'.$_POST['CANID']."_config.json";
	file_put_contents($path,$_POST['sJson'],LOCK_EX);
	echo "ZAPISALEM: ".$_POST['sJson'];	
}
else if ($_POST['MODE']=='load')
{
	$path = '../cfg_files/'.$_POST['CANID']."_config.json";
	if (file_exists($path))
	{
		$setJson = file_get_contents($path);
		echo $setJson;
	}
	else echo "no_file";
	
}


?>