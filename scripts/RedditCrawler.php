<?php

namespace app\scripts;

use app\models\Bilder;
use app\models\Reddit;
use app\models\Fehler;

class RedditCrawler 
{
   	public function startAll(){
		
		$subreddits = $this->get_all_subreddits();
		foreach($subreddits as $subreddit){
			$secondPage = $this->crawl_one_subreddit($subreddit);
			
			$subreddit->URL = $secondPage;
			$thirdPage = $this->crawl_one_subreddit($subreddit);
			
			$this->update_timestamp($subreddit);
		}		
	}
	
	public function startMultiple($i=10){
		$subreddits = $this->get_multiple_subreddits($i);
		foreach($subreddits as $subreddit){
			$nextPage = $this->crawl_one_subreddit($subreddit);
			
			$subreddit->URL = $nextPage;
			$nextPage = $this->crawl_one_subreddit($subreddit);
			
			$this->update_timestamp($subreddit);
		}
	}
	
	public function startOne($redditID){
		$subreddit = $this->get_one_subreddit($redditID);
		
		$nextPage = $this->crawl_one_subreddit($subreddit);
		
		$subreddit->URL = $nextPage;		
		$nextPage = $this->crawl_one_subreddit($subreddit);
						
		$this->update_timestamp($subreddit);
	}
		
	
	function get_all_subreddits(){
		$query = Reddit::find();       
        return $reddits = $query
			->orderBy(['LAST_TIME'=> SORT_ASC])
			->limit(25)
			->all();
	}
	
	function get_multiple_subreddits($i){
		$query = Reddit::find();       
        return $reddits = $query
			->orderBy(['LAST_TIME'=> SORT_ASC])
			->limit($i)
			->all();
	}
	
	function get_one_subreddit($id){
		$query = Reddit::find();       
        return $reddits = $query
			->where(['ID' => $id])
			->one();
	}
	
	
	/*
		Startet Aufrufe für das Durchsuchen eines Subreddits und Speichern der Ergebnisse
		return nextPage Gibt nächste Seite des Subreddit zurück
	*/
	function crawl_one_subreddit($reddit){
		$htmlString = $this->load_http_site($reddit->URL, true);
		$postList = $this-> crawl_reddit_html($htmlString);
		$postList = $this->remove_non_pic_posts($postList);
		$nextPage = $this->get_next_site_link($htmlString);
		
		$this->save_pictures($postList/*Finished*/, $reddit->ID);
		return $nextPage;
	}
	
	
	/*
		Lädt den Html-Inhalt einer Http-Seite und gibt ihn als String zurück
		Wenn die Seite eine Reddit-Seite ist, kann man einen Header anhängen der das Alter über 18 bestätigt,
		um so auch nicht jugendfreie Reddits zu crawlen
	*/
	function load_http_site($url, $reddit=false){
		try{
			
			$curl = curl_init();		
			curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, $url);
			
			//Wenn Reddit -> ü18 Header
			if($reddit==true){
				$header = array ('Cookie: over18=1');
				curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
			}
						
			$htmlSite = curl_exec($curl);			
			curl_close($curl);
			
			return $htmlSite;
		}
		catch(Exception $e){
			return null;
		}
		
	}
	
	
	
	/*
		sucht aus einem HTML-String einer Reddit-Seite alle Posts mit dazugehöriger Bild-URL und Upload-Datum heraus, Stand Dez.2017
		gibt ein Array zurück, das für jeden Post ein Array mit Bild-Url, Thread-Url und Upload-Datum enthält
		wenn HTMl-String leer ist -> return null
	*/
	function crawl_reddit_html($html=''){
		
		if(empty($html)){
			return null;
		}		
		
		$dom = new \DOMDocument();
		// The @ before the method call suppresses any warnings that
		// loadHTML might throw because of invalid HTML in the page.
		@$dom->loadHTML($html);
		
		//in diesem HTML-Tag sind alle Posts enthalten
		$content = $dom->getElementById('siteTable');
		
		$postList = [];
		
		//jeder Post befindet sich in einem divElement
		foreach($content->getElementsByTagName('div') as $divElement){			
			
			//wenn keine angehefteten Threads oder Div für Link von nächster Seite dann weiter
			if(strpos($divElement->getAttribute('class'),'stickied')==false && strpos($divElement->getAttribute('class'),'link')==true){
				$picUrl = $divElement->getAttribute('data-url');
				//filter vor Content den ich nicht magNur zulassen was ich mag
				
				if(preg_match('/gfycat\.com/', $picUrl)==1 || preg_match('/imgur/',$picUrl)==1 || preg_match('/redd\.it/',$picUrl)==1 || preg_match('/reddit\.com/',$picUrl)==1){
					$time = '';
					foreach($divElement->getElementsByTagName('time') as $uploadTime){
						if(preg_match('/live-timestamp/', $uploadTime->getAttribute('class'))==1){
							$time = $uploadTime->getAttribute('datetime');
							$time = preg_filter('/T/',' ', $time);
							$time = preg_filter('/\+00:00/','', $time);							
						}
					}

					$title = '';
					foreach($divElement->getElementsByTagName('p') as $paragraph){
						if($paragraph->hasAttribute('class')){
							if($paragraph->getAttribute('class') == 'title'){
								$anchor = $paragraph->getElementsByTagName('a');
								if($anchor){
									$title = $anchor->item(0)->nodeValue;
								}
								else{
									$title = null;
								}
							}
						}			
					}

					$postList[] = array($picUrl, $divElement->getAttribute('data-permalink'), $time, $title);					
				}							
			}			
		}		
		return $postList;
	}
	
	
	
	
	/*
		entfernt alle Posts die keinen weiterführenden Link enthalten, also keine Daten haben
		und somit auch keine Bilder/Videos
		Wenn Bild-Url und Thread-Url gleich sind -> Post entfernen
	*/
	function remove_non_pic_posts($postList = array()){
		if(empty($postList)){
			return null;
		}
		
		$remainingPosts = array();
		foreach($postList as $post){
			if($post[0] != $post[1]){
				$remainingPosts[] = array($post[0], 'https://www.reddit.com'.$post[1], $post[2], $post[3]);
			}
		}
		return $remainingPosts;
	}
	
	
	
	
	/*
		gibt den Link für die nachfolgende Seite zurück	
	*/
	function get_next_site_link($html=''){
		if(empty($html)){
			return null;
		}
		
		$dom = new \DOMDocument();		
		@$dom->loadHTML($html);
		
		foreach($dom->getElementsByTagName('a') as $tag){
			if(preg_match('/(nofollow next)/', $tag->getAttribute('rel'))==1){
				return $tag->getAttribute('href');
			}						
		}		
		return null;
	}
		
		
		
		
	/*
		Speichert die gesuchten Bilder nur wenn diese noch nicht vorhanden sind
	*/
	function save_pictures($postList, $subredditId){
		
		/*foreach($postList as &$post){
			$post[0]=utf8_decode($post[0]);
			$post[1]=utf8_decode($post[1]);
			$post[2]=utf8_decode($post[2]);
		}*/
		
		foreach($postList as $post){
			$query = Bilder::find();       
			$result = $query->where(['Thread' => $post[1]])
				->one();
			
			
			if(empty($result)){
								
				//gifv entfernen
				if(preg_match('/\.gifv/', $post[0]) == 1){
					$post[0] = str_replace("gifv","gif",$post[0]);
				}
				
				$bilder = new Bilder();
				$bilder->Thread = $post[1];
				$bilder->Bild = $post[0];
				$bilder->Datum = $post[2];
				$bilder->Title = $post[3];
				$bilder->Subreddit = $subredditId;
				
				try{
					$bilder->save();
				}
				catch (\yii\db\Exception $e)
				{
					$fehler = new Fehler();
					$fehler->Thread = $post[1];
					$fehler->Bild = $post[0];
					$fehler->Subreddit = $subredditId;
				}
				
			}			 
		}		
	}
	
	
	function update_timestamp($reddit){
		
		$query = Bilder::find();       
		$result = $query
					->where(['Subreddit'=>$reddit->ID])
					->orderBy(['Datum'=> SORT_DESC])
					->one();
			
		$reddit = Reddit::findOne($reddit->ID);
		
		date_default_timezone_set('Europe/Berlin');
		$date = time();
		
		$reddit->LAST_TIME = date('Y-m-d H:i:s', $date);
		$reddit->Last_Image = $result->Datum; 
		$reddit->update();
	}
	
}
