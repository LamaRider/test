<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\data\Pagination;
use app\models\Reddit;
use app\models\Bilder;
use app\models\AddredditForm;
use app\scripts\RedditCrawler;

class BilderController extends Controller
{
	public function actionShowmenu()
    {
        $query = Reddit::find();
		
        //$reddits = $query->orderBy(['name' => 'LOWER'])
        //    ->all();
				
		$result = Yii::$app->db->createCommand('SELECT * FROM reddit ORDER BY LOWER(name)')
            ->queryAll();
		
		foreach($result as $post){
			$reddit = new Reddit;
			$reddit->ID = $post['ID'];
			$reddit->Name = $post['Name'];
			$reddit->URL = $post['URL'];
			$reddit->LAST_TIME = $post['LAST_TIME'];
			$reddit->Last_Image = $post['Last_Image'];
			
			$reddits[] = $reddit;
		}
		
		
		$addRedditForm = new AddredditForm();
		
		if ($addRedditForm->load(Yii::$app->request->post()) && $addRedditForm->addReddit()) {
            if ($addRedditForm->validate()) {
				// all inputs are valid
				return $this->refresh();
			} else {
				// validation failed: $errors is an array containing error messages
				$errors = $addRedditForm->errors;
			}			
        }
		
        return $this->render('bilderMenu', [
            'reddits' => $reddits,
			'model' => $addRedditForm
        ]);
    }
	
    public function actionShowpics($reddit = 4)
    {
        $query = Bilder::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count()/5,
        ]);

        $bilders = $query->where(['Subreddit'=>$reddit])
			->orderBy(['Datum' => SORT_DESC])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();			
			
		$query = Reddit::find();		
		$redditSearch = $query->where(['ID'=>$reddit])
            ->one();

        return $this->render('bilderView', [
            'redditsearch' => $redditSearch,
			'bilders' => $bilders,
            'pagination' => $pagination			
        ]);
    }
	
	public function actionReloadsubreddit($reddit = 1)
    {
        $crawler = new RedditCrawler();	
		$crawler->startOne($reddit);
		$view = $this->actionShowmenu();				
		return $view;
	}
	
	public function actionReloadall($reddit = 1)
    {
        $crawler = new RedditCrawler();	
		$crawler->startAll();
		$view = $this->actionShowmenu();				
		return $view;
    }
		
}