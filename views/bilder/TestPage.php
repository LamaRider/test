<?php
use yii\helpers\Html;

?>
<h1>Reddit Übersicht</h1>
	<?php echo count($list);?>
<ul>
	<?php 
	$test="";
	foreach($list as $key){
		var_dump($key);
		echo '<br><br>';
		//$test .= implode(', ', $key);
		//$test .= '<br>';
	}
	//$test = $list;
	//echo $test;
	//echo implode(', ' $list);
	?>
</ul>