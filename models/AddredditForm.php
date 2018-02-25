<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\Reddit;
use app\scripts\RedditCrawler;


class AddredditForm extends Model
{
    public $url;
	private $is_ok=true;

    public function rules()
    {
        return [
            // define validation rules here
			[['url'], 'required'],
			//[['url'], function($attrtibute){
				//if(preg_match('/https:\/\/www\.reddit\.com\/r\/\.*/', $this->$attribute)==1){
					//$this->is_ok = true;
				//	$this->url='sack1';
				//}
				//else{
				//	$this->addError($attribute, 'Falsche Reddit-Url');
				//}
				//$this->url='sack';
			//}],
        ];
    }
		
	public function addreddit()
    {
        if($this->is_ok == false){
			return;
		}
		
		if(substr($this->url, -1) != '/'){
			$this->url .= '/';
		}
		
		$query = Reddit::find();
		$reddit = $query->where(['URL'=>$this->url])
			->one();
			
		if(empty($reddit)){
			$neu = new Reddit();
			$neu->URL = $this->url;
			$neu->Name = mb_strcut($this->url, 22);			
			$neu->save();

			$query = Reddit::find();       
			$reddits = $query
			->where(['URL' => $this->url])
			->one();
			
			$redditCrawler = new RedditCrawler;
			$redditCrawler->startOne($reddits->ID);
			
			return true;
		}
		return false;
    }
}