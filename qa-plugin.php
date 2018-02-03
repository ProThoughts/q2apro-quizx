<?php
/*
	Plugin Name: Q2APRO Quizx
	Plugin URI: 
	Plugin Description: Quizx - Quiz system 3.0
	Plugin Version: 1.0
	Plugin Date: 2016-07-01
	Plugin Author: q2apro.com
	Plugin Author URI: http://www.q2apro.com/
	Plugin License: q2apro.com 
	Plugin Minimum Question2Answer Version: 1.7
	Plugin Update Check URI: 
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	// language file
	qa_register_plugin_phrases('quizx-lang-*.php', 'quizx_lang');
	
	// quiz page
	qa_register_plugin_module('page', 'quizx-page-start.php', 'quizx_page_start', 'Quizx Page Start');
	
	// quiz page for ajax communications
	qa_register_plugin_module('page', 'quizx-page-ajaxhandler.php', 'quizx_page_ajaxhandler', 'Quizx Page Ajaxhandler');
	
	// page for admin to accept new questions
	qa_register_plugin_module('page', 'quizx-page-desk.php', 'quizx_page_desk', 'Quizx Page Desk');
	
	// page for user to see his personal statistics
	qa_register_plugin_module('page', 'quizx-page-userstats.php', 'quizx_page_userstats', 'Quizx Page Userstats');
	
	// page for users
	qa_register_plugin_module('page', 'quizx-page-highscores.php', 'quizx_page_highscores', 'Quizx Page Highscores');
	
	// layer
	qa_register_plugin_layer('quizx-layer.php', 'quizx Layer');

	// admin
	qa_register_plugin_module('module', 'quizx-admin.php', 'quizx_admin', 'quizx Admin');
   
	// track events
	qa_register_plugin_module('event', 'quizx-events.php','quizx_events','quizx Events');

	// widget contributors
	qa_register_plugin_module('widget', 'quizx-widget-contributors.php', 'quizx_widget_contributors', 'Quizx Widget: Contributors');

	// widget activities
	qa_register_plugin_module('widget', 'quizx-widget-activities.php', 'quizx_widget_activities', 'Quizx Widget: Activities');

	// widget question statistics
	qa_register_plugin_module('widget', 'quizx-widget-questionstats.php', 'quizx_widget_questionstats', 'Quizx Widget: Question Statistics');

	// widget user statistics
	qa_register_plugin_module('widget', 'quizx-widget-userstats.php', 'quizx_widget_userstats', 'Quizx Widget: User Statistics');

	// widget skip question
	qa_register_plugin_module('widget', 'quizx-widget-skipquestion.php', 'quizx_widget_skipquestion', 'Quizx Widget: Skip Question');

	// overrides for allowing moderated question to be accessible to owner to be able to add answers
	// qa_register_plugin_overrides('quizx-overrides.php');
	
	
	function getplaytimestamp($userid,$cookieid)
	{ 
		// gplaytime: this is the reset time so that former played question get ignored (time set by the player with button on quiz start page)
		// check if reset has been set in db
		if(isset($userid)) 
		{
			$gplaytime = qa_db_read_one_value(
						qa_db_query_sub('SELECT timestamp FROM `^quizx_stamps`
							WHERE userid = #
							AND actiontype = "reset"
							ORDER BY timestamp DESC 
							LIMIT 1
						',
						$userid), 
						true
					);
		}
		else 
		{
			// anonymous guest, check for cookie
			$gplaytime = qa_db_read_one_value(
						qa_db_query_sub('SELECT timestamp FROM `^quizx_stamps`
							WHERE cookieid = #
							AND actiontype = "reset"
							ORDER BY timestamp DESC 
							LIMIT 1
						',
						$cookieid), 
						true
					);
		}
		// no time set
		if(is_null($gplaytime)) 
		{
			$date = date_create('1981-03-31'); // past
			$gplaytime = date_format($date, 'Y-m-d H:i:s');
		}
		
		return $gplaytime;
	}
	
	function getshorterguestid($guestid)
	{ 
		return substr($guestid, 0, 8); 
	}
	
	function getguestid_bycookie($cookieid=null)
	{
		if(is_null($cookieid))
		{
			$cookieid = qa_cookie_get();
		}
		// $guestid = round($cookieid/pow(2,20));
		// $guestid = base_convert($guestid, 10, 36); // 26

		// $guestid = base_convert($cookieid, 10, 36);
		
		// memo: if an integer gets too large in PHP, it becomes a float, accuracy is lost, and operations start behaving differently
		$fromcharset = '0123456789';
		$tocharset = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';				
		$guestid = charset_base_convert($cookieid, $fromcharset, $tocharset); // e.g. // b4JlnAoHP37

		return $guestid;
	}
	
	function getcookie_byguestid($guestid=null)
	{
		if(is_null($guestid))
		{
			$guestid = qa_cookie_get();
		}
		// $cookieid = $guestid*pow(2,20); // round from above changed the value, cannot be reverted
		// $cookieid = base_convert($cookieid, 36, 10); // 26
		
		// $cookieid = base_convert($guestid, 36, 10);
		
		$fromcharset = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';				
		$tocharset = '0123456789';
		$cookieid = charset_base_convert($guestid, $fromcharset, $tocharset);

		return $cookieid;
	}
	
	function getguestscore($cookieid=null)
	{
		if(is_null($cookieid))
		{
			$cookieid = qa_cookie_get();
		}
		
		// get all players from last 24 hours
		$scoredata = qa_db_read_one_assoc(
						qa_db_query_sub('SELECT COUNT(*) as attempts, SUM(elapsed) as elapsedtotal, userid, timestamp, questionid, answerid, SUM(correct) as correct, elapsed, ipaddress FROM `^quizx_gameplay`
							WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
							AND cookieid = #
							',
							$cookieid), true
						);
		$userscore = 0;
		if(!is_null($scoredata))
		{
			if($scoredata['attempts']>0 && $scoredata['elapsedtotal']>0)
			{
				$correctper = $scoredata['correct']/$scoredata['attempts'];
				$answerspersec = $scoredata['attempts']/$scoredata['elapsedtotal'];
				$rating = $answerspersec * $scoredata['correct'] * 60 * $correctper;
				$userscore = number_format((float)$rating, 3, ',', '');
			}
		}
		
		if(stripos(strrev($userscore), '000,') === 0)
		{
			$userscore = substr($userscore,0,-4);
		}
		
		return $userscore;
	}
	
	function getguestplaytime($cookieid=null)
	{
		if(is_null($cookieid))
		{
			$cookieid = qa_cookie_get();
		}
		
		// get all players from last 24 hours
		$playtimedata = qa_db_read_one_assoc(
						qa_db_query_sub('SELECT SUM(elapsed) as elapsedtotal FROM `^quizx_gameplay`
							WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
							AND cookieid = #
							',
							$cookieid), true
						);
		$userplaytime = 0;
		if(isset($playtimedata['elapsedtotal']))
		{
			$userplaytime = $playtimedata['elapsedtotal'];
		}
		
		if($userplaytime<60)
		{
			$userplaytime_str = $userplaytime.' '.qa_lang('quizx_lang/seconds');
		}
		else
		{
			$minutes = floor($userplaytime/60);
			$userplaytime -= $minutes*60;
			$seconds = $userplaytime;
			$userplaytime_str = $minutes.' '.qa_lang('quizx_lang/minutes_abbr').' '.$seconds.' '.qa_lang('quizx_lang/seconds_abbr');
		}
		return $userplaytime_str;
	} // end getguestplaytime
	
	
	function getuserscore($userid=null)
	{
		if(is_null($userid))
		{
			$userid = qa_get_logged_in_userid();
		}
		
		// get all players from last 24 hours
		$scoredata = qa_db_read_one_assoc(
						qa_db_query_sub('SELECT COUNT(*) as attempts, SUM(elapsed) as elapsedtotal, userid, timestamp, questionid, answerid, SUM(correct) as correct, elapsed, ipaddress FROM `^quizx_gameplay`
							WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
							AND userid = #
							',
							$userid), true
						);
		$userscore = 0;
		if(!is_null($scoredata))
		{
			if($scoredata['attempts']>0 && $scoredata['elapsedtotal']>0)
			{
				$correctper = $scoredata['correct']/$scoredata['attempts'];
				$answerspersec = $scoredata['attempts']/$scoredata['elapsedtotal'];
				$rating = $answerspersec * $scoredata['correct'] * 60 * $correctper;
				$userscore = number_format((float)$rating, 3, ',', '');
			}
		}
		
		if(stripos(strrev($userscore), '000,') === 0)
		{
			$userscore = substr($userscore,0,-4);
		}
		
		return $userscore;
	}
	
	
	function getuserplaytime($userid=null)
	{
		if(is_null($userid))
		{
			$userid = qa_get_logged_in_userid();
		}
		
		// get all players from last 24 hours
		$playtimedata = qa_db_read_one_assoc(
						qa_db_query_sub('SELECT SUM(elapsed) as elapsedtotal FROM `^quizx_gameplay`
							WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
							AND userid = #
							',
							$userid), true
						);
		$userplaytime = 0;
		if(isset($playtimedata['elapsedtotal']))
		{
			$userplaytime = $playtimedata['elapsedtotal'];
		}
		
		if($userplaytime<60)
		{
			$userplaytime_str = $userplaytime.' Sekunden';
		}
		else
		{
			$minutes = floor($userplaytime/60);
			$userplaytime -= $minutes*60;
			$seconds = $userplaytime;
			$userplaytime_str = $minutes.' min '.$seconds.' s';
		}
		return $userplaytime_str;
	} // end getguestplaytime
	
	
	/**
	 * Translates a number to a short alhanumeric version
	 *
	 * Translated any number up to 9007199254740992
	 * to a shorter version in letters e.g.:
	 * 9007199254740989 --> PpQXn7COf
	 *
	 * specifiying the second argument true, it will
	 * translate back e.g.:
	 * PpQXn7COf --> 9007199254740989
	 *
	 * this function is based on any2dec && dec2any by
	 * fragmer[at]mail[dot]ru
	 * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
	 *
	 * If you want the alphaID to be at least 3 letter long, use the
	 * $pad_up = 3 argument
	 *
	 * In most cases this is better than totally random ID generators
	 * because this can easily avoid duplicate ID's.
	 * For example if you correlate the alpha ID to an auto incrementing ID
	 * in your database, you're done.
	 *
	 * The reverse is done because it makes it slightly more cryptic,
	 * but it also makes it easier to spread lots of IDs in different
	 * directories on your filesystem. Example:
	 * $part1 = substr($alpha_id,0,1);
	 * $part2 = substr($alpha_id,1,1);
	 * $part3 = substr($alpha_id,2,strlen($alpha_id));
	 * $destindir = "/".$part1."/".$part2."/".$part3;
	 * // by reversing, directories are more evenly spread out. The
	 * // first 26 directories already occupy 26 main levels
	 *
	 * // Show //
	 * echo $number_out." => ".$alpha_out."\n";
	 * echo $alpha_in." => ".$number_out."\n";
	 * echo alphaID(238328, false)." => ".alphaID(alphaID(238328, false), true)."\n";
	 *
	 * // expects:
	 * // 2188847690240 => SpQXn7Cb
	 * // SpQXn7Cb => 2188847690240
	 * // aaab => 238328
	 *
	 * </code>
	 *
	 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version   SVN: Release: $Id: alphaID.inc.php 344 2009-06-10 17:43:59Z kevin $
	 *
	 * @param mixed   $in	  String or long input to translate
	 * @param boolean $to_num  Reverses translation when true
	 * @param mixed   $pad_up  Number or boolean padds the result up to a specified length
	 * @param string  $pass_key Supplying a password makes it harder to calculate the original ID
	 *
	 * @return mixed string or long
	 */
	function alphaID($in, $to_num = false, $pad_up = false, $pass_key = null)
	{
		$out   =   '';
		$index = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$base  = strlen($index);

		if ($pass_key !== null) {
			// Although this function's purpose is to just make the
			// ID short - and not so much secure,
			// with this patch by Simon Franz (http://blog.snaky.org/)
			// you can optionally supply a password to make it harder
			// to calculate the corresponding numeric ID

			for ($n = 0; $n < strlen($index); $n++) {
				$i[] = substr($index, $n, 1);
			}

			$pass_hash = hash('sha256',$pass_key);
			$pass_hash = (strlen($pass_hash) < strlen($index) ? hash('sha512', $pass_key) : $pass_hash);

			for ($n = 0; $n < strlen($index); $n++) {
				$p[] =  substr($pass_hash, $n, 1);
			}

			array_multisort($p, SORT_DESC, $i);
			$index = implode($i);
		}

		if ($to_num) {
			// Digital number  <<--  alphabet letter code
			$len = strlen($in) - 1;

			for ($t = $len; $t >= 0; $t--) {
				$bcp = bcpow($base, $len - $t);
				$out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
			}

			if (is_numeric($pad_up)) {
				$pad_up--;

				if ($pad_up > 0) {
					$out -= pow($base, $pad_up);
				}
			}
		} else {
			// Digital number  -->>  alphabet letter code
			if (is_numeric($pad_up)) {
				$pad_up--;

				if ($pad_up > 0) {
					$in += pow($base, $pad_up);
				}
			}

			for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
				$bcp = bcpow($base, $t);
				$a   = floor($in / $bcp) % $base;
				$out = $out . substr($index, $a, 1);
				$in  = $in - ($a * $bcp);
			}
		}

		return $out;
	}

	// http://stackoverflow.com/questions/1938029/php-how-to-base-convert-up-to-base-62
	/*
	$fromcharset = '0123456789';
	$tocharset = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';				
	$guestid = charset_base_convert($cookieid, $fromcharset, $tocharset);
	*/
	function charset_base_convert($numstring, $fromcharset, $tocharset) 
	{
     $frombase=strlen($fromcharset);
     $tobase=strlen($tocharset);
     $chars = $fromcharset;
     $tostring = $tocharset;

     $length = strlen($numstring);
     $result = '';
     for ($i = 0; $i < $length; $i++) {
         $number[$i] = strpos($chars, $numstring{$i});
     }
     do {
         $divide = 0;
         $newlen = 0;
         for ($i = 0; $i < $length; $i++) {
             $divide = $divide * $frombase + $number[$i];
             if ($divide >= $tobase) {
                 $number[$newlen++] = (int)($divide / $tobase);
                 $divide = $divide % $tobase;
             } elseif ($newlen > 0) {
                 $number[$newlen++] = 0;
             }
         }
         $length = $newlen;
         $result = $tostring{$divide} . $result;
     }
     while ($newlen != 0);
     return $result;
	}
  

	// http://stackoverflow.com/questions/20160413/how-to-compress-a-very-large-number-into-alphanumeric-in-php
	// $num - the number we want to convert
	// $symbols - the chars you want to use e.g. '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
	// &$out is a pointer to your $result
	function intToBase($num, $symbols, &$out)
	{

		// get the radix that we are working with
		$radix = strlen($symbols);

		$pos = strlen($out)-1;

		if($num==0){
			// if our number is zero then we can just use the first character of our symbols and we are done.
			$out[$pos] = $symbols[0];
		}
		else
		{
			// otherwise we have to loop through and rebase the integer one character at a time.
			while ($num > 0) {
				// split off one digit
			   $r = $num % $radix;
				// convert it and add it to the char array
				$out[pos] = $symbols[r];
				// subtract what we have added to the compressed string
				$num = ($num - $r) / $radix;
				$pos--;
			}
		}
	};

	function baseToInt($base, $symbols, &$out)
	{
		//get length of the char map, so you can change according to your needs
		$radix = strlen($symbols);

		//split the chars into an array and initialize variables
		$arr = str_split($base,1);
		$i = 0;
		$out = 0;

		//loop through each char assigning values
		//chars to the left are the least significant
		foreach($arr as $char) 
		{
			$pos = strpos($symbols, $char);
			$partialSum = $pos * pow($radix, $i);
			$out += $partialSum;
			$i++;
		}
	}



/*
	Omit PHP closing tag to help avoid accidental output
*/