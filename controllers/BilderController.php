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
        //$crawler = new RedditCrawler;
		//$list = $crawler->get_List();
		
		$query = Reddit::find();
		
        $reddits = $query->orderBy('name')
            ->all();
		
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
		
		/*
		return $this->render('TestPage', [
            'list' => $list,           
        ]);*/		
    }
	
    public function actionShowpics($reddit = 4)
    {
        $query = Bilder::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
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