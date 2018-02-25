<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Bilder von <?php echo Html::encode("{$redditsearch->Name}");?></h1>
	<?php 
	foreach ($bilders as $bilder):
	?>
			
			<div style="margin:50px;">
			<p><?php if(!empty($bilder->Title)){echo Html::encode($bilder->Title);} ?></p>
			<a href="<?= Html::encode("{$bilder->Thread}") ?>" target="_blank">Link zum Post</a>
			<br>
			<a href="<?= Html::encode("{$bilder->Bild}") ?>" target="_blank">Link zum Original</a>
			<p>Upload-Datum: 
				<?php 
					$date = new DateTime($bilder->Datum); 
					echo Html::encode($date->format('d.m.Y H:i:s'));
				?>
			</p>
		
		
		<?php		
		if(preg_match('/imgur\.com/',$bilder->Bild)==1):		
		?>
			<?php  
				$data_id = preg_replace('/https?:\/\/((i|m)\.)?imgur\.com\/(gallery\/)?/','',$bilder->Bild);
				$data_id = trim(preg_replace('/\.jpg.*|\.jpeg.*|\.png.*|\.gif.*|\.mp4.*|#.*/','',$data_id));
				
				$imgur_link = preg_replace('/https?:\/\/((i|m)\.)?/','//',$bilder->Bild);
				$imgur_link = preg_replace('/gallery\//','',$imgur_link);
				$imgur_link = trim(preg_replace('/\.jpg.*|\.jpeg.*|\.png.*|\.gif.*|\.mp4.*|#.*/','',$imgur_link));
				
			?>
				
			<!--Link aus DB: < ?= //Html::encode("{$bilder->Bild}") ? >			
			<br>
			DataID: < ?php //echo Html::encode($data_id); ? >
			<br>
			ImgurLink: < ?php //echo Html::encode($imgur_link); ? >
			<br>-->
			
			<!--hier beginnt der eigentliche Code-->
			<?php 
			if(preg_match('/maxwidth/', $bilder->Bild)==1):
			?>
				<img src="<?= Html::encode("{$bilder->Bild}") ?>" alt="Leer: <?= Html::encode("{$bilder->Bild}") ?>" style="width:600px">			
				<br>
				
			<?php		
			else:		
			?>
				<blockquote class="imgur-embed-pub" lang="en" data-id="<?php echo Html::encode($data_id); ?>">
					<a href="<?php echo Html::encode($imgur_link); ?>">Titel</a>
				</blockquote>
				<script async src="//s.imgur.com/min/embed.js" charset="utf-8"></script>
			<?php
			endif;
			?>
		
		<?php		
		elseif (preg_match('/gfycat\.com/', $bilder->Bild) == 1):	
		?>
			<!--Link aus DB:< ?= Html::encode("{$bilder->Bild}") ?>
			<br>
			Link umgebaut:
			< ?php/*
				$tmp = preg_replace('/gifs\/detail/', 'ifr', $bilder->Bild);
				if(preg_match('/gfycat\.com\/ifr/', $tmp) == 0){
					$tmp = substr_replace($tmp, '/ifr', strpos($tmp,'.com')+4, 0);
				}				
				$tmp = preg_replace('/giant\.|fat\.|thumbs\./','',$tmp);
				echo Html::encode(trim(preg_replace('/\.webm|\.mp4|\.gif/','',$tmp)));*/
			?>-->
			
			<!--hier beginnt der eigentliche Code-->
			<div style='position:relative;padding-bottom:calc(100% / 1.85)'>
				<iframe src='<?php				
					$link = preg_replace('/gifs\/detail/', 'ifr', $bilder->Bild);
					
					if(preg_match('/gfycat\.com\/ifr/', $link) == 0){
						$link = substr_replace($link, '/ifr', strpos($link,'.com')+4, 0);
					}	
					
					$link = preg_replace('/giant\.|fat\.|thumbs\./','',$link);
					$link = trim(preg_replace('/(-size_restricted)?(\.webm|\.mp4|\.gif)/','',$link));
					echo Html::encode($link);
				?>' 
				frameborder='0' scrolling='no' width='100%' height='100%' style='position:absolute;top:0;left:0;' allowfullscreen>
				</iframe>
			</div>
			<br>		
		
		<?php
		elseif (preg_match('/\.webm/', $bilder->Bild) == 1 && preg_match('/gfycat\.com/', $bilder->Bild) == 0):	
		?>
			<video width='800px' controls="true">
				<source src="<?= Html::encode("{$bilder->Bild}") ?>" type="video/webm">
			</video>
			<br>
		
		
		<?php 
		elseif (preg_match('/\.mp4/', $bilder->Bild) == 1 && preg_match('/gfycat\.com/', $bilder->Bild) == 0):	
		?>
			<video width='800px' controls="true">
				<source src="<?= Html::encode("{$bilder->Bild}") ?>" type="video/mp4">
			</video>
			<br>
		<?php
		elseif(preg_match('/\.jpg|\.png|\.gif/',$bilder->Bild)==1):		
		?>	
			<img src="<?= Html::encode("{$bilder->Bild}") ?>" alt="Leer: <?= Html::encode("{$bilder->Bild}") ?>" style="width:600px">			
			<br>		
			
		<?php 
		else:	
		?>
			<div>
				Kein Video oder Bild
				<br>
				Hier die Url zum Bild/Video: <?= Html::encode("{$bilder->Bild}")?>
				<br>
				Ergebnis PregMatch mit JPG/PNG/GIF: <?php echo preg_match('/\.jpg|\.png|\.gif/', $bilder->Thread); ?>				
			</div>		
		<?php
		endif;
		?>
        </div>
	<?php endforeach; ?>	
	
<?= LinkPager::widget(['pagination' => $pagination]) ?>