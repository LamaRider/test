<?php

namespace app\commands;

use yii\console\Controller;
use app\scripts\RedditCrawler;
use app\scripts\TitleCrawler;

class CronController extends Controller
{
	/*public function actionLoadreddit()
    {
        $crawler = new \app\scripts\RedditCrawler;
		$crawler->startMultiple();			
	}*/
	
	public function actionLoadredditall()
    {
        $crawler = new \app\scripts\RedditCrawler;
		$crawler->startAll();		
	}
	
	public function actionLoadredditthree()
    {
        $crawler = new \app\scripts\RedditCrawler;
		$crawler->startMultiple(3);		
	}
	
	public function actionLoadredditten()
    {
        $crawler = new \app\scripts\RedditCrawler;
		$crawler->startMultiple(10);		
	}
	
    public function actionLoadtitles(){
		$titleCrawler = new \app\scripts\TitleCrawler;
		$titleCrawler->startTitleSearch();
	}
	
	
}