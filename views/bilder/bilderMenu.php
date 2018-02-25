<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

?>
<h1>Reddit Übersicht</h1>
	Anzahl Reddits: <?php echo count($reddits);?>
<ul>
	<?php 
	foreach ($reddits as $reddit):
		?>
			<li style="margin:5px;">
				<a href="<?php echo Url::to(['bilder/showpics', 'reddit' => $reddit->ID]) ?>" target="_blank"><b><?= Html::encode("{$reddit->Name}") ?></b></a>
				<a href="<?php echo Url::to(['bilder/reloadsubreddit', 'reddit' => $reddit->ID]) ?>">Aktualisieren</a>
				<br>
				Zuletzt Aktualisiert: 
				<?php 
					$date = new DateTime($reddit->LAST_TIME); 
					echo Html::encode($date->format('d.m.Y H:i:s'));
				?>
				<br>
				Neuester Eintrag:
				<?php 
					$date = new DateTime($reddit->Last_Image); 
					echo Html::encode($date->format('d.m.Y H:i:s'));
				?>
				<br>
				<a href="<?= Html::encode("{$reddit->URL}") ?> " target="_blank">Link zum Reddit</a>				
			</li>        
	<?php endforeach; ?>
</ul>
<a href="<?php echo Url::to(['bilder/reloadall']) ?>">Alle Aktualisieren</a>

<div class="row">
	<div class="col-lg-5">

		<?php $form = ActiveForm::begin(['id' => 'addreddit-form']); ?>

			<?= $form->field($model, 'url')->textInput(['autofocus' => false]) ?>                    

			<div class="form-group">
				<?= Html::submitButton('Hinzuf&uuml;gen', ['class' => 'btn btn-primary', 'name' => 'addreddit-button']) ?>
			</div>

		<?php ActiveForm::end(); ?>

	</div>
</div>