<?php
// Downloads and caches latest tweets from particular username
// Requires PHP 5 (it's DOM parsing for the feeds)
// usage:
// $tweets = TwitterHelper::get_tweets('cnn', 5); /* Get latest 5 tweets from cnn user */
//
// $twitter_helper = new TwitterHelper('cnn'); /* Create a new TwitterHelper object with username cnn */
// $tweets = $twitter_helper->_get_tweets(5); /* Get latest 5 tweets from cnn user */
// $avatar = $twitter_helper->get_avatar(); /* Get twitter avatar for user cnn */

class TwitterHelper {
    public $username;
    public $cache_lifetime = 300; // 5 minutes

    function get_tweets($username, $limit) {
        $self = new self($username);
        return $self->_get_tweets($limit);
    }
    
    function __construct($username) {
        $this->username = $username;
    }
    
    function _download_xml($type, $xml_url) {
        $cache_key = "xml::$type::$this->username";
        
        $cached = get_option($cache_key, -1);
        if ($cached!==-1) {
            $expires = $cached['expires'];
            if ($expires > time()) {
                return $cached['data'];
            }
        }
        
        $data = wp_remote_get($xml_url);
        if (is_wp_error($data)) {
        	return "Cannot get latest tweets";
        }
        
        if (strstr($data['body'], '<error>')) {
        	$data = array();
        	$data['body'] = $cached['data'];
        }
        
        update_option($cache_key, array(
            'expires'=>time() + $this->cache_lifetime,
            'data'=>$data['body']
        ));
        
        return $data['body'];
    }
    function get_avatar() {
        $xml = $this->_download_xml('twitter-avatar', "http://twitter.com/users/show/$this->username.xml");;
        
        $doc = @DomDocument::loadXML($xml);
        
        $avatar = $doc->getElementsByTagName('profile_image_url')->item(0)->nodeValue;
        
        return $avatar;
    }
    
    function _get_tweets($limit) {
        $xml = $this->_download_xml('twitter-updates', "http://twitter.com/statuses/user_timeline/$this->username.atom");
        
        $doc = @DomDocument::loadXML($xml);
        
        if(!$doc) {
            return array();
        }
        $entries = $doc->getElementsByTagName('entry');
        
        $res = array();
        $loop_counter = 1;
        foreach ($entries as $entry) {
            $tweet = new StdClass();
            
            $tweet->tweet_text = $entry->getElementsByTagName('content')->item(0)->nodeValue;
            $tweet->tweet_text = $this->add_links(str_replace('&apos;', '\'', $tweet->tweet_text));
            
            $tweet->tweet_link = $entry->getElementsByTagName('link')->item(0)->getAttribute('href');
            $tweet->time = $entry->getElementsByTagName('updated')->item(0)->nodeValue;
            
            list($year, $month, $day, $hour, $minute, $second) = preg_split('~[^\d]~', $tweet->time);
            $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
            $tweet->timestamp = $timestamp;
            $tweet->time_distance = $this->distance_of_time_in_words(time(), $timestamp);
            
            $res[] = $tweet;
            if ($loop_counter==$limit) {
                break;
            }
            $loop_counter++;
        }
        
        if (count($res)==1 && $limit==1) {
            $return = $res[0];
        }
        $return = $res;
        
        
        return $return;
    }
    function distance_of_time_in_words($from_time, $to_time=0, $showLessThanAMinute=false) {
        $distanceInSeconds = round(abs($to_time - $from_time));
        
        if(function_exists('lang')) {
            $lang = 'lang';
        } else {
            //Empty function if we don't have translation function
            $lang = create_function('$arg', 'return $arg; ');
        }
        
        $distanceInMinutes = round($distanceInSeconds / 60);
        if ( $distanceInMinutes <= 1 ) {
            if ( !$showLessThanAMinute ) {
                return ($distanceInMinutes == 0) ? $lang('less than a minute') : $lang('1 minute');
            } else {
                if ( $distanceInSeconds < 5 ) {
                    return $lang('less than 5 seconds');
                }
                if ( $distanceInSeconds < 10 ) {
                    return $lang('less than 10 seconds');
                }
                if ( $distanceInSeconds < 20 ) {
                    return $lang('less than 20 seconds');
                }
                if ( $distanceInSeconds < 40 ) {
                    return $lang('about half a minute');
                }
                if ( $distanceInSeconds < 60 ) {
                    return $lang('less than a minute');
                }
               
                return $lang('1 minute');
            }
        }
        if ( $distanceInMinutes < 45 ) {
            return $distanceInMinutes . ' ' . $lang('minutes');
        }
        if ( $distanceInMinutes < 90 ) {
            return $lang('about 1 hour');
        }
        if ( $distanceInMinutes < 1440 ) {
            return $lang('about') . ' ' . round(floatval($distanceInMinutes) / 60.0) . ' ' . $lang('hours');
        }
        if ( $distanceInMinutes < 2880 ) {
            return '1 ' .  $lang('day');
        }
        if ( $distanceInMinutes < 43200 ) {
            return $lang('about'). ' ' . round(floatval($distanceInMinutes) / 1440) . ' ' . $lang('days');
        }
        if ( $distanceInMinutes < 86400 ) {
            return $lang('about') .' 1 ' . $lang('month');
        }
        if ( $distanceInMinutes < 525600 ) {
            return round(floatval($distanceInMinutes) / 43200) . ' ' . $lang('months');
        }
        if ( $distanceInMinutes < 1051199 ) {
            return $lang('about') . ' 1 ' . $lang('year');
        }
       
        return $lang('over') . ' ' . round(floatval($distanceInMinutes) / 525600) . ' ' . $lang('years');
    }
    /**
	 * Adds HTML links to tweet text
	 *
	 * Example:
	 */
	function add_links($tweet_text) {
       $tweet_text = str_replace(array(/*':', '/', */'%'), array(/*'<wbr></wbr>:', '<wbr></wbr>/', */'<wbr></wbr>%'), $tweet_text);
       $tweet_text = preg_replace('~(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)~', '<a href="$1" target="_blank">$1</a>', $tweet_text);
       $tweet_text = preg_replace('~[\s]+@([a-zA-Z0-9_]+)~', ' <a href="http://twitter.com/$1" rel="nofollow" target="_blank">@$1</a>', $tweet_text);
       $tweet_text = preg_replace('~[\s]+#([a-zA-Z0-9_]+)~', ' <a href="http://search.twitter.com/search?q=%23$1" rel="nofollow" target="_blank">#$1</a>', $tweet_text);
       return $tweet_text;
	}
}
?>