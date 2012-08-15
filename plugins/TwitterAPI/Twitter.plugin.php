<?php
class Twitter extends phpLive{
	protected $twitURL = 'http://api.twitter.com/1/';
	protected $xml;
	protected $tweets = array(), $twitterArr = array();
	protected $pversion = "1.0.0";
	public function __construct(){
		parent::__construct();
	}
	public function pversion(){
		return $this->pversion;
	}
	public function loadTimeline($user, $max = 20){
		$this->twitURL .= 'statuses/user_timeline.xml?screen_name='.$user.'&count='.$max;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->twitURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$this->xml = curl_exec($ch);
		return $this;
	}
	public function getTweets(){
		$this->twitterArr = $this->getTimelineArray();
		foreach($this->twitterArr->status as $status){
			$this->tweets[$this->toTimestamp($status->created_at)->toString()] = $status->text;
		}
		$this->list = $this->tweets;
		return $this;
	}
	public function getTimelineArray(){
		return simplexml_load_string($this->xml);
	}
	public function formatTweet($tweet){
		$tweet = preg_replace("/@(.+?)(\h|\W)/", "<a href=\"http://twitter.com/#!/$1\">@$1</a>$2", $tweet);
		return $tweet;
	}
}
?>