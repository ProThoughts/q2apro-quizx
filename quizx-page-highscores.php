<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_page_highscores
	{
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory = $directory;
			$this->urltoroot = $urltoroot;
		}
		
		// for display in admin interface under admin/pages
		function suggest_requests() 
		{	
			return array(
				array(
					'title' => 'Quizx Page Highscores', // title of page
					'request' => 'highscores', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='highscores')
			{
				return true;
			}
			return false;
		}

		function process_request($request)
		{
		
			
			/* DEV: manual assigning highscores */ 
			/*
			$usercounter = qa_db_read_one_value(
							qa_db_query_sub(
								'SELECT COUNT(*) FROM ^users'
						 ));
			for($i=1;$i<$usercounter;$i++)
			{
				$userid = $i;
				// all dates get saved to db on 1st day of month for recent month m
				$recentmonth = date('Y-m-01');
				$quser_stats_total = qa_db_read_one_assoc( 
					qa_db_query_sub('SELECT SUM(correct=1) AS qcorrect, SUM(correct=0) AS qincorrect FROM `^quizx_gameplay` 
										INNER JOIN
										(
											SELECT userid, questionid, MIN(timestamp) AS mintimestamp
											FROM `^quizx_gameplay` 
											WHERE userid = # 
											GROUP BY userid, questionid
										) sub0
										ON (
											^quizx_gameplay.userid IS NOT NULL
											AND ^quizx_gameplay.userid = sub0.userid
										)
										AND ^quizx_gameplay.questionid = sub0.questionid
										AND ^quizx_gameplay.timestamp =  sub0.mintimestamp
										WHERE ^quizx_gameplay.userid = #',
										$userid, $userid
										), true
									);
				if(isset($userid))
				{
					$qcorrect = $quser_stats_total['qcorrect'];
					$qincorrect = $quser_stats_total['qincorrect'];
					$gameplays = $qcorrect+$qincorrect;
					if(isset($qcorrect) && isset($qincorrect))
					{
						$correctratio = 100*$qcorrect / ($qcorrect+$qincorrect);
					
						// error_log($recentmonth.' | '.$userid.' | '.$correctratio.' | '.$qcorrect.' | '.$qincorrect);
					
						// insert-or-update query
						qa_db_query_sub('INSERT INTO `^quizx_highscores` (date, userid, rating, qcorrect, qincorrect) 
											VALUES(#, #, #, #, #)
											ON DUPLICATE KEY UPDATE 
											rating=VALUES(rating), qcorrect=VALUES(qcorrect), qincorrect=VALUES(qincorrect)
										', $recentmonth, $userid, $correctratio, $qcorrect, $qincorrect);
					}
				} // end isset($userid)
			} // END DEV (manual assignment of highscores)
			*/

			if(qa_opt('quizx_enabled')!=1) 
			{
				$qa_content=qa_content_prepare();
				$qa_content['error'] = '<div>'.qa_lang('quizx_lang/plugin_disabled').'</div>';
				return $qa_content;
			}
			
			/* start */
			$qa_content = qa_content_prepare();
			qa_set_template('quizx-highscores-page');
			$qa_content['title'] = qa_lang('quizx_lang/pagetitle_highscores');

			// identify user
			$userid = qa_get_logged_in_userid();
			$cookieid = qa_cookie_get();
		
			setlocale(LC_ALL, qa_opt('quizx_localization').'.UTF-8'); // consider LC_TIME
			$lastmonth = strftime('%m/%G', strtotime('last month'));
			$timerange = qa_lang('quizx_lang/last24hours');
			
			// basic settings for datepicker
			$firstListDate = '20.10.2015'; // qa_opt('quizx_timeformat')
			
			$month_name = date('F', mktime(0, 0, 0, $i));
			
			$monthsString = '';
			for($i=1; $i<=12; $i++)
			{
				// $monthsString .= '"'.date('M', mktime(0, 0, 0, $i)).'"'.($i!=12 ? ', ' : '');
				$monthsString .= '"'.strftime('%b', mktime(0, 0, 0, $i)).'"'.($i!=12 ? ', ' : '');
				// '"Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"';
			}
			
			$daysString = '';
			$daysabbrString = '';
			for($i=0; $i<7; $i++)
			{
				$daysString .= '"'.strftime('%A', strtotime("last sunday +$i day")).'"'.($i!=7 ? ', ' : '');
				// '"Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"';
				
				$daysabbrString .= '"'.strftime('%a', strtotime("last sunday +$i day")).'"'.($i!=7 ? ', ' : '');
				// '"So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"';
			}

			$chosenDate = date( qa_opt('quizx_timeformat') );
			
			if(qa_post_text('request'))
			{
				$chosenDate = qa_post_text('request');
				// sanitize string, keep only 0-9 and minus
				$chosenDate = preg_replace("/[^0-9\-\.]/i", '', $chosenDate);
				// $timerange = 'vom '.substr($chosenDate,8,2).'.'.substr($chosenDate,5,2).'.'.substr($chosenDate,0,4);
				$timerange = qa_lang('quizx_lang/fromday').' '.$chosenDate;
			}
			
			$chosenDateISO = substr($chosenDate,6,4).'-'.substr($chosenDate,3,2).'-'.substr($chosenDate,0,2);
			
			// init
			$qa_content['custom'] = '';
			
			$qa_content['custom'] .= '
				<h2>Highscores '.$timerange.'</h2>
			';
			
			// datepicker
			$qa_content['custom'] .= '<link rel="stylesheet" type="text/css" href="'.$this->urltoroot.'zebra_datepicker/default.css">';
			$qa_content['custom'] .= '<script type="text/javascript" src="'.$this->urltoroot.'zebra_datepicker/zebra_datepicker.js"></script>';
			$qa_content['custom'] .= '<script type="text/javascript">
				$(document).ready(function()
				{
				
					$("#datepicker").Zebra_DatePicker({
						direction: [false, "'.$firstListDate.'"], // until today
						format: "'.qa_opt('quizx_timeformat').'", 
						lang_clear_date: "", 
						days: ['.$daysString.'],
						days_abbr: ['.$daysabbrString.'],
						months: ['.$monthsString.'],
						offset: [-180,250], 
						onSelect: function(view, elements) {
							$("form#datepick").submit();
						}
					});
				});
			</script>';

			// date picker input field
			$qa_content['custom'] .= '<form method="post" action="'.qa_self_html().'" id="datepick">
											<span>'.qa_lang('quizx_lang/chooseday').': &nbsp;</span>
											<input value="'.$chosenDate.'" id="datepicker" name="request" type="text">
										</form>
									 ';


			$chosenDateNext = date('Y-m-d', strtotime($chosenDate .' +1 day'));
			
			// query for last 24 hours
			if($chosenDate == date(qa_opt('quizx_timeformat')))
			{
				// get all plays from last 24 hours
				$quser_qplayed = qa_db_read_all_assoc(
								qa_db_query_sub('SELECT userid, timestamp, questionid, answerid, correct, elapsed, cookieid, ipaddress FROM `^quizx_gameplay`
									WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
									ORDER BY userid DESC, cookieid DESC
									')
								);
							// yesterday: timestamp >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND timestamp < CURDATE()
			}
			else
			{
				// get all players from given date
				$quser_qplayed = qa_db_read_all_assoc(
								qa_db_query_sub('SELECT userid, timestamp, questionid, answerid, correct, elapsed, cookieid, ipaddress FROM `^quizx_gameplay`
									WHERE timestamp BETWEEN # AND #
									ORDER BY userid DESC, cookieid DESC',
									$chosenDateISO, $chosenDateNext)
								);
			}
			
			// digest data
			$collector = array();
			foreach($quser_qplayed as $game)
			{
				$usercheckid = '';
				// registered users
				if(isset($game['userid']))
				{
					$usercheckid = $game['userid'];
				}
				// anonymous
				else
				{
					$usercheckid = $game['cookieid'];					
				}
				
				if(!isset($collector[$usercheckid]))
				{
					$collector[$usercheckid] = array();
					$collector[$usercheckid]['attempts']  = 0;
					$collector[$usercheckid]['correct'] = 0;
					$collector[$usercheckid]['elapsed'] = 0;
					$collector[$usercheckid]['registered'] = isset($game['userid']);
				}
				
				// add play data
				$collector[$usercheckid]['attempts'] += 1;
				$collector[$usercheckid]['correct'] += $game['correct'];
				$collector[$usercheckid]['elapsed'] += $game['elapsed'];
				
			} // end foreach

			// do ratings percentage
			foreach($collector as &$datachunk)
			{
				$datachunk['elapsed_average'] = $datachunk['elapsed']/$datachunk['attempts'];
				$datachunk['answerspersec'] = $datachunk['attempts']/$datachunk['elapsed'];
				$datachunk['correctper'] = $datachunk['correct']/$datachunk['attempts'];
				$datachunk['rating'] = $datachunk['correct'] + $datachunk['answerspersec'] * $datachunk['correct'] * 60 * $datachunk['correctper'];
			}
			
			// sort data by rating
			// http://stackoverflow.com/questions/7983822/sort-a-multi-dimensional-associative-array
			uasort($collector, function ($i, $j)
			{
				$a = $i['rating'];
				$b = $j['rating'];
				if($a == $b) return 0;
				elseif($a > $b) return -1;
				else return 1;
			});

			// init 
			$playertable = '';

			$playertable .= '
			<div class="playertable"> 
				<table class="highscoretable"> 
					<thead>
					<tr> 
						<th>Mitglied</th> 
						<th>Statistik</th> 
						<th>Bewertung</th> 
					</tr>
					</thead>
			';
			
			$ucorrectcnt = 0;
			$uincorrectcnt = 0;
			
			$usercounter = 0;
			foreach($collector as $key=>$topuser)
			{
				$topu_userid_cookieid = $key;
				$topu_attempts = $topuser['attempts'];
				$topu_correct = $topuser['correct'];
				$topu_elapsed = $topuser['elapsed'];
				$topu_elapsed_av = $topuser['elapsed_average'];
				$topu_answerspersec = $topuser['answerspersec'];
				$topu_correctper = 100*$topuser['correctper'];
				$topu_rating = $topuser['rating'];
				
				$avatarsize = 75;
				$avatar = './?qa=image&qa_blobid='.qa_opt('avatar_default_blobid').'&qa_size='.$avatarsize;
				
				// get userdata
				if($topuser['registered'])
				{
					$thisuserid = $key;
					$userdata = qa_db_read_one_assoc(qa_db_query_sub('SELECT handle,avatarblobid FROM ^users 
																	WHERE userid = #', 
																	$thisuserid), true);
					if(isset($userdata['avatarblobid']))
					{
						$avatar = './?qa=image&qa_blobid='.$userdata['avatarblobid'].'&qa_size='.$avatarsize;
					}
					// $userprofilelink = qa_path('user/'.$userdata['handle']);
					$userprofilelink = qa_path('userstats').'?userid='.$thisuserid.'&t';
					$handledisplay = qa_html($userdata['handle']);
					$profilefulllink = '<a class="q2apro_hs_link" href="'.$userprofilelink.'">'.$handledisplay.'</a>';
				}
				else
				{
					$thisguestid = getguestid_bycookie($topu_userid_cookieid);
					$userprofilelink = qa_path('userstats').'?guestid='.$thisguestid.'&t';
					$handledisplay = 'Gast '.getshorterguestid($thisguestid);
					$profilefulllink = '<a class="q2apro_hs_link_guest" href="'.$userprofilelink.'">'.$handledisplay.'</a>';
				}
				
				$userscore = number_format((float)$topu_correct+(float)$topu_rating, 3, ',', '');
				// if($userscore=='x,000')
				if(stripos(strrev($userscore), '000,') === 0)
				{
					$userscore = substr($userscore,0,-4);
				}
				
				$answerspermin = number_format((float)$topu_answerspersec*60, 3, ',', '');
				// if($answerspermin=='x,000')
				if(stripos(strrev($answerspermin), '000,') === 0)
				{
					$answerspermin = substr($answerspermin,0,-4);
				}
				
				$correctdisplay = str_replace('~ccount~', $topu_correct, qa_lang('quizx_lang/xfromycorrect'));
				$correctdisplay = str_replace('~attempts~', $topu_attempts, $correctdisplay);
				
				$playertable .= '
					<tr>
						<td style="position:relative;">
							<span class="highscorecount">
								'.++$usercounter.'
								<img src="'.$this->urltoroot.'images/gold-medal.png" />
							</span>
							<a class="q2apro_hs_avatar" '.$userprofilelink.'>
								<img src="'.$avatar.'" alt="'.$handledisplay.'" />
							</a>
							<br />'.
							$profilefulllink.
						'</td>
						<td>
							<span class="ustat_correct" style="margin-right:20px;">
								'.$correctdisplay.'
							</span> 
							<br />
							<span class="ustat_correct" style="margin-right:20px;">
								'.qa_lang('quizx_lang/success').': '.number_format((float)$topu_correctper, 2, ',', '').' %
							</span>
							<br />
							∅ '.qa_lang('quizx_lang/time').': '.number_format((float)$topu_elapsed_av, 1, ',', '').' '.qa_lang('quizx_lang/secperqu').'<br />
						</td>
						<td style="line-height:150%;">
							<span style="font-size:17px;">
								'.$userscore.' '.qa_lang('quizx_lang/points').'
							</span> 
							<br />
							<span class="highcalc">
								'.$topu_correct.' '.qa_lang('quizx_lang/correctansw').' + <br />'.$answerspermin.' '.qa_lang('quizx_lang/answpermin').' · '.$topu_correct.' '.qa_lang('quizx_lang/correctansw').' · '.number_format((float)$topu_correctper, 2, ',', '').' %
							</span>
						</td>
					</tr>';
			} // end foreach quser_qplayed
			
			$playertable .= '</table>';
			
			$qa_content['custom'] .= $playertable;
			
			// css styles
			$qa_content['custom'] .= '
			<style type="text/css">
				.playertable {
					margin-top:30px;
				}
				#datepicker {
					width:110px;
				}
					/*
					.qa-sidepanel {
						display:none;
					}
					.qa-main {
						width:100%;
					}
					*/
					
					.highscorecount 
					{
						position:absolute;
						left:5px;
						top:5px;
						color:#555;
						font-size:12px;
						width:20px;
						height:20px;
						text-align:center;	
						line-height:17px;
						padding:0;
						margin:0;
					}
					.highcalc {
						color:#777;
						font-size:12px;
					}
					
					.highscoretable {
						width:100%;
						max-width:640px;
						background:#FFF;
						margin:30px 0 15px;
						text-align:left;
						border-collapse:collapse;
						font-size:14px;
					}
					.highscoretable th {
						text-align:center;
						padding:4px 4px 4px 10px;
						border:1px solid #CCC;
						background:#FFD;
						font-weight:normal;
					}
					.highscoretable td {
						border:1px solid #CCC;
						padding:8px 10px;
						line-height:110%;
					}
					.highscoretable td:nth-child(1) { 
						width:25%;
						text-align:center !important;
					}
					.highscoretable td:nth-child(2) { 
						width:35%;
						line-height:150%;
						text-align:left;
					}
					.highscoretable td:nth-child(3) { 
						width:40%;
						text-align:right;
					}
					.highscoretable tr:hover { 
						background:#FFA;
					}
					.highscoretable .q2apro_hs_avatar img { 
						border:1px solid #EEE;
						border-radius:50%;
					}
					.highscoretable .q2apro_hs_link { 
					}
					.highscoretable .q2apro_hs_link_guest { 
						font-size:12px;
						color:#999;
					}
					.highscoretable .ustat_correct:before {
						color:#0A0;
					}
					.highscoretable .ustat_incorrect:before {
						color:#F00;
					}
				</style>				
			';

			return $qa_content;
		}
		
		
		function bcdechex($dec)
		{
			$hex = '';
			do
			{    
				$last = bcmod($dec, 16);
				$hex = dechex($last).$hex;
				$dec = bcdiv(bcsub($dec, $last), 16);
			} while($dec>0);
			return $hex;
		}
		
		// http://stackoverflow.com/questions/4964197/converting-a-number-base-10-to-base-62-a-za-z0-9
		function toChars($number)
		{
		   $res = base_convert($number, 10, 36);
		   // $res = strtr($res,'0123456789','qrstuvxwyz');
		   return $res;
		}

	}; // end class
	

/*
	Omit PHP closing tag to help avoid accidental output
*/