<?php

namespace app\scripts;

use yii;
use app\models\Bilder;
use app\models\Reddit;
use app\scripts\RedditCrawler;

class TitleCrawler{
	
	public function startTitleSearch(){
		$posts = $this->get_urls_from_db();
		
		if(!empty($posts)){
			foreach($posts as $element){
				$title = $this->get_title($element->Thread);
				$element->Title = $title;
				$this->update_post($element);
			}
		}
	}
	
	
	function get_urls_from_db(){
		$query = Bilder::find();       
        return $bilder = $query
			->where(['Title' => NULL])
			->orderBy(['Datum'=> SORT_DESC])
			->limit(5)
			->all();
	}
	
	function get_title($url){
		$redditCrawler = new RedditCrawler;
		$html = $redditCrawler->load_http_site($url, $reddit=true);
		if(!$html){$y = 2/0;}
		
		$dom = new \DOMDocument();
		// The @ before the method call suppresses any warnings that
		// loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);
		
		foreach($dom->getElementsByTagName('p') as $paragraph){
			
			//$newdoc = new \DOMDocument();
			//$cloned = $paragraph->cloneNode(TRUE);
			//$newdoc->appendChild($newdoc->importNode($cloned,TRUE));
			//echo $newdoc->saveHTML();
			
			if($paragraph->hasAttribute('class')){
				if($paragraph->getAttribute('class') == 'title'){
					$anchor = $paragraph->getElementsByTagName('a');
					if($anchor){
						return $anchor->item(0)->nodeValue;
					}
					else{
						return null;
					}
				}
			}			
		}		
	}
	
	function update_post($post){
		if ($post->update() !== false) {
			// update successful
		} else {
			// update failed
		}
	}
}
?>