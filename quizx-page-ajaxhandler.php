<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_page_ajaxhandler 
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
					'title' => 'Quizx Page Ajaxhandler', // title of page
					'request' => 'ajaxhandler', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='ajaxhandler') 
			{
				return true;
			}
			return false;
		}

		function process_request($request)
		{
		
			// is plugin enabled
			if(qa_opt('quizx_enabled')!=1)
			{
				$qa_content = qa_content_prepare();
				$qa_content['error'] = '<p>'.qa_lang('quizx_lang/plugin_disabled').'</p>';
				return $qa_content;
			}
			
			// AJAX MAIN
			
			// only questionid: question is created and marked as ready (editdone) by creator
			$transferString = qa_post_text('questionid');
			if(isset($transferString))
			{
				$questionid = $transferString;
				// only numbers 
				$questionid = preg_replace("/[^0-9]/i", '', $questionid);
				qa_db_query_sub('UPDATE `^quizx_moderate` SET status=1 
									WHERE questionid = #',
									$questionid);
				echo 'qid_editdone';
				exit();
			}
			
			// redaktion unlocks a question, then public
			$transferString = qa_post_text('q_unlockid');
			if(isset($transferString))
			{
				$questionid = $transferString;
				// only numbers 
				$questionid = preg_replace("/[^0-9]/i", '', $questionid);
				qa_db_query_sub('UPDATE `^quizx_moderate` SET status=2 
									WHERE questionid = #',
									$questionid);
				// get question of next question to moderate "1-editdone" (will be a button for Redaktion)
				$nextqutomod = qa_db_read_one_value(
									qa_db_query_sub('SELECT questionid FROM `^quizx_moderate` 
										WHERE status = "1"
										LIMIT 1'),
									true);
				if(isset($nextqutomod))
				{
					echo $nextqutomod;
				}
				else
				{
					echo '';
				}
				exit();
			}
			
			// QUIZ: user submits an answer
			$transferString = qa_post_text('gamedata'); // holds qid and aid and time
			if(isset($transferString))
			{
				$newdata = json_decode($transferString,true);
				$newdata = str_replace('&quot;', '"', $newdata); // see stackoverflow.com/questions/3110487/
				
				$questionid = (int)$newdata['qid'];
				$answerid = (int)$newdata['aid'];
				$elapsed = (int)$newdata['time'];
				
				$userid = qa_get_logged_in_userid();
				
				/*dev: 
				$arrayBack = array(
					'correct' => $questionid,
					'nextqid' => $answerid
				);
				echo json_encode($arrayBack);
				exit();*/
				
				// could prevent empty userid -> anonymous can play with recent version
				/*if(empty($userid))
				{
					echo 'Userid is empty!';
					return;
				}*/
				
				
				// should return 0 (no match/wrong) or 1 (match/correct)
				$iscorrect = qa_db_read_one_value(
					qa_db_query_sub(
						'SELECT COUNT(postid) FROM ^posts 
							WHERE postid = #
							AND selchildid = #',
						$questionid, $answerid
					), true
				);
				
				// but u never know
				if(is_null($iscorrect))
				{
					$iscorrect = 0;
				}
				
				// get the correct answer to display frontend
				$qudata = qa_db_read_one_assoc(
					qa_db_query_sub(
						'SELECT selchildid, tags FROM ^posts 
							WHERE postid = #',
						$questionid
					), true
				);
				
				// this is the correct answer to the recent question
				$correctanswer = $qudata['selchildid'];
				
				// A: remember topic of our recent question to choose the next question of this topic
				$topic = $qudata['tags'];
				// error_log($questionid.'->'.$topic);

				// B: if gamemode "randomly" then set no topic - get gamemode from Cookie!
				if(isset($_COOKIE['quizx_gamemode']) && $_COOKIE['quizx_gamemode']=='randomly')
				{
					$topic = 'randomly';
				}
				
				// write user choice into gameplay database
				// make sure that gameplay entry (questionid) does not exist yet -> No, let users answer the same question as often as they want!
				// for anonoymous we would have userid==NULL and must save cookie and ipaddress
				// $cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create(); // cookie should be set by q2a already!
				$cookieid = qa_cookie_get();
				$ipaddress = qa_remote_ip_address();
				
				// registered users could post without ip or ipaddress, anonymous must have ipaddress and cookieid
				if( isset($userid) || (isset($ipaddress) && isset($cookieid)) )
				{
					qa_db_query_sub('INSERT INTO ^quizx_gameplay (userid, timestamp, questionid, answerid, correct, elapsed, cookieid, ipaddress) 
										VALUES(#, NOW(), #, #, #, #, #, INET_ATON(#))',
										$userid, $questionid, $answerid, $iscorrect, $elapsed, $cookieid, $ipaddress
									);
				}
				else
				{
					// problem, anonymous without ip or cookie
					return;
				}

				
				// gplaytime: this is the reset time so that former played question get ignored (time set by the player with button on quiz start page)
				$gplaytime = getplaytimestamp($userid,$cookieid);
					
				// get the next question in this topic for the user to answer
					// match all questionids the user has already answered TODAY against the q-postids to this topic in qa_posts
					// makes sure only to consider published question (status 2)
				$random = true;
				$ordermode = $random ? 'RAND()' : '^quizx_moderate.questionid ASC';
				
				// $repeat = false;
				
				if($topic=='randomly')
				{
					// no topic restrictions
					if(isset($userid))
					{
						$qleftx = qa_db_read_all_assoc(
							qa_db_query_sub(
								'SELECT questionid FROM ^quizx_moderate
									WHERE status=2
										AND questionid NOT IN (
											SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay 
											WHERE userid = # 
											AND timestamp > #
										)
										ORDER BY #',
								$userid, $gplaytime, $ordermode
							)
						); 
						// ORDER BY ^quizx_moderate.questionid ASC
						// AND DATE(`timestamp`) = CURDATE()
					}
					else
					{
						// anonymous
						$qleftx = qa_db_read_all_assoc(
							qa_db_query_sub(
								'SELECT questionid FROM ^quizx_moderate
									WHERE status=2
										AND questionid NOT IN (
											SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay 
											WHERE cookieid = #
											AND timestamp > #
										)
										ORDER BY #
										',
								$cookieid, $gplaytime, $ordermode
							)
						);
					}
				} // end randomly
				else
				{
					if(isset($userid))
					{
						$qleftx = qa_db_read_all_assoc(
							qa_db_query_sub(
								'SELECT questionid FROM ^quizx_moderate
									WHERE tags = #
										AND status=2
										AND questionid NOT IN (
											SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay 
											WHERE userid = # 
											AND timestamp > #
										)
										ORDER BY #',
								$topic, $userid, $gplaytime, $ordermode
							)
						);
						// ORDER BY ^quizx_moderate.questionid ASC
						// AND DATE(`timestamp`) = CURDATE()
					}
					else
					{
						// anonymous
						$qleftx = qa_db_read_all_assoc(
							qa_db_query_sub(
								'SELECT questionid FROM ^quizx_moderate
									WHERE tags = #
										AND status=2
										AND questionid NOT IN (
											SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay 
											WHERE cookieid = #
											AND timestamp > #
										)
										ORDER BY #
										',
								$topic, $cookieid, $gplaytime, $ordermode
							)
						);
					}
				} // end end next question for tag

				$qleft = count($qleftx);
				$nextqid = -1; // -1 is no next question
				if($qleft>0)
				{
					foreach ($qleftx as $q)
					{
						$nextqid = $q['questionid'];
						break;
					}
				}
				
				// UPDATE THE user's HIGHSCORE in table qa_quizx_highscores
				if(isset($userid))
				{
					// q2apro_updateUserHighscore($userid);
					
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
					
					$qcorrect = $quser_stats_total['qcorrect'];
					$qincorrect = $quser_stats_total['qincorrect'];
					$gameplays = $qcorrect+$qincorrect;
					
					if(isset($qcorrect) && isset($qincorrect))
					{
						$correctratio = 100*$qcorrect / ($qcorrect+$qincorrect);
						
						// insert-or-update query
						qa_db_query_sub('INSERT INTO `^quizx_highscores` (date, userid, rating, qcorrect, qincorrect) 
											VALUES(#, #, #, #, #)
											ON DUPLICATE KEY UPDATE 
											rating=VALUES(rating), qcorrect=VALUES(qcorrect), qincorrect=VALUES(qincorrect)
										', $recentmonth, $userid, $correctratio, $qcorrect, $qincorrect);
					}
				} // end isset($userid)
			
				// ajax return array data to write back into table
				$arrayBack = array(
					'correct' => $iscorrect,
					'correctanswer' => $correctanswer,
					'nextqid' => $nextqid,
					'qleft' => $qleft
				);
				echo json_encode($arrayBack);
				
				exit(); 
			} // END AJAX RETURN (gamedata)
				
			
			// DEFAULT PAGE
			$qa_content = qa_content_prepare();			
			return $qa_content;
		}
		
	}; // end class quizx_page_ajaxhandler
	
/*
	Omit PHP closing tag to help avoid accidental output
*/