<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_page_start
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
					'title' => 'Quizx Game Page', // title of page
					'request' => 'start', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='start')
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
			
			// get cookie
			// anonymous - identify by cookie
			$userid = qa_get_logged_in_userid();
			$cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();

			
			// player resets his questions - we set a timestamp and ignore all formerly answered questions in gameplay
			$gplay_reset = qa_get('gplay_reset');
			if(isset($gplay_reset))
			{
				qa_db_query_sub('INSERT INTO `^quizx_stamps` (userid,timestamp,cookieid,actiontype) 
										VALUES (#, NOW(), #, "reset")',
										// ON DUPLICATE KEY UPDATE `timestamp`=NOW()',
									$userid, $cookieid);
				$urlparams = array(
					'hide' => 'reset',
				);
				qa_redirect('start', $urlparams);
				exit();
			}
			
			// get parameter from URL, if set, redirect to first question of this topic
			$topic = qa_get('topic');
			if(isset($topic))
			{
				// no topic restriction if topic==randomly, ignore already answered questions
				if($topic=='randomly')
				{
					// remember gamemode randomly in cookie
					setcookie('quizx_gamemode', 'randomly', time() + (7*86400)); // 86400 = 1 day
					$nextquest = qa_db_read_one_assoc(
								qa_db_query_sub(
									'SELECT questionid FROM ^quizx_moderate
										WHERE status=2
										AND questionid NOT IN (SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay WHERE userid = #)
										ORDER BY RAND()
										LIMIT 1
										',
									$userid
								), true
							);
				}
				else
				{
					// delete gamemode randomly in case it has been set
					setcookie('quizx_gamemode', 'nill', time() + (-10000));
				
					// get the next question in this topic for the user to answer
					// *ignore (for now) if user has answered already or not
					// makes sure only to consider published question (status 2)
					// random order (*user can set option later, if linear or random)
					$nextquest = qa_db_read_one_assoc(
								qa_db_query_sub(
									'SELECT questionid FROM ^quizx_moderate
										WHERE tags = #
											AND status=2
											ORDER BY RAND()
											LIMIT 1
											',
									$topic
								), true
							);
							// AND questionid NOT IN (SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay WHERE userid = #)
				}
				$questionid = $nextquest['questionid'];
				if(isset($questionid))
				{
					// get question title
					$qtitle = qa_db_read_one_value(
								qa_db_query_sub(
									'SELECT title FROM ^posts
										WHERE postid = #', $questionid), true);
					$redirectto = qa_q_request($questionid, $qtitle);
					qa_redirect($redirectto);
					exit();
				}
				else
				{
					$qa_content['title'] = qa_lang('quizx_lang/choosetopic'); // page title
					$qa_content['error'] = qa_lang('quizx_lang/notopicsfound');
					return $qa_content;
				}
				
				exit();
			}
			
			
			$hidereset = qa_get('hide');
			if(isset($hidereset))
			{
				$hidereset = ($hidereset=='reset') || isset($gplay_reset);
			}
			

			// DEFAULT GAME PAGE
			$qa_content = qa_content_prepare();
			
			qa_set_template('start');

			$qa_content['title'] = qa_lang('quizx_lang/choosetopic');
			
			// init 
			$qa_content['custom'] = ''; 
			
			// info 
			$qa_content['custom'] .= '
			<div style="background:#FFF;padding:15px 0 10px 20px;margin:0;">
				<p style="margin:0;">
					'.qa_lang('quizx_lang/goal_hint').'
				</p>
				<a href="'.qa_path('start').'?topic=randomly" id="quizx-playall-button" class="qa-form-wide-button qx_red" style="float:left;margin:20px 20px 0 0;cursor:pointer;">
					'.qa_lang('quizx_lang/playrandomly').'
				</a>'.
				($hidereset ? '' : 
				'<a href="'.qa_path('start').'?gplay_reset=1" id="quizx-reset-gameplay-button" class="qa-form-light-button qa-form-light-button-send" style="margin-top:20px;cursor:pointer;" title="'.qa_lang('quizx_lang/unlocksallquagain').'">
					'.qa_lang('quizx_lang/playanew').'
				</a>').'
				<div style="clear:both;"></div>
			</div>
			
			<script type="text/javascript">
				$(document).ready(function(){
					$("#quizx-playall-button").click( function() { 
						
					});
				});
			</script>			
			';
			// Ziel ist es, so viele Fragen wie möglich mit so wenig Fehlern wie möglich richtig zu beantworten.
			
			$qa_content['custom'] .= '
			';
			
			/* from tags */
			$start = qa_get_start();
			$userid = qa_get_logged_in_userid();
			$alphabetictags = quizx_qa_db_alphabetic_tags_selectspec($start, qa_opt_if_loaded('page_size_tags'));
			
			// items from query are not alphabetic, sort associative array
			ksort($alphabetictags);

			$tagcount = qa_opt('cache_tagcount');
			$pagesize = qa_opt('page_size_tags');

			// gplaytime: this is the reset time so that former played question get ignored (time set by the player with button on quiz start page)
			$gplaytime = getplaytimestamp($userid,$cookieid);
			
			$allcount = 0; // count all questions answered
			
			if(count($alphabetictags))
			{
				// $favoritemap = qa_get_favorite_non_qs_map();

				$qa_content['custom'] .= '
					<h3 style="margin:30px 0 0 20px;">
						'.qa_lang('quizx_lang/playbytopics').':
					</h3>
					<div class="qa-part-ranking" style="margin-top:0;">
				';
				$output = 0;
				
				foreach ($alphabetictags as $word)
				{
					/*$qa_content['ranking']['items'][] = array(
						'label' => qa_tag_html_quiz($word, false, @$favoritemap['tag'][qa_strtolower($word)]),
						'count' => number_format($count),
					);
					*/
					$tag = $word['tags'];
					$taglabel = $tag;
					if(strpos($taglabel, '-') !== false)
					{
						$taglabel = str_replace('-', ' ', $taglabel);
					}
					
					// count what questions the user has already answered (correct and incorrect)
					// *for performance: can be done in one query getting all tags, then reading the values here
					if(isset($userid))
					{
						$uplayed = qa_db_read_one_value(
										qa_db_query_sub('SELECT COUNT(postid) FROM `^posts`
											WHERE tags = #
											AND postid IN (
												SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay 
													WHERE userid = #
													AND timestamp > #)', 
											$tag, $userid, $gplaytime),
											true
										);
					}
					else
					{
						// anonymous - identify by cookie
						// $cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();

						$uplayed = qa_db_read_one_value(
										qa_db_query_sub('SELECT COUNT(postid) FROM `^posts`
											WHERE tags = #
											AND postid IN (
												SELECT ^quizx_gameplay.questionid FROM ^quizx_gameplay 
													WHERE cookieid = #
													AND timestamp > #)', 
											$tag, $cookieid, $gplaytime),
											true
										);
					}
					
					// total count
					$allcount += $uplayed; 
					
					$qlabel = $word['tagcount']==1 ? qa_lang('quizx_lang/quizquestion') : qa_lang('quizx_lang/quizquestions');
					$qlabel2 = $uplayed==1 ? qa_lang('quizx_lang/quizquestion') : qa_lang('quizx_lang/quizquestions');
					
					if($word['tagcount']!=$uplayed)
					{
						$xcss = '';
						$uplayed_label = $uplayed.' '.qa_lang('quizx_lang/xfromy').' '.$word['tagcount'];
						$tooltip = str_replace('~data~', $uplayed.' '.$qlabel2, qa_lang('quizx_lang/youplayedxfromyqu'));
					}
					else
					{
						$xcss = ' qa-user-topic-done';
						$uplayed_label = 'fertig';
						$tooltip = qa_lang('quizx_lang/youplayedallqu');
					}
					
					$qa_content['custom'] .= '
							<span class="qa-ranking-item qa-top-tags-item">
								<span class="qa-tag-link qa-user-achieved tooltipW'.$xcss.'" title="'.$tooltip.'">'.$uplayed_label.'</span>
								<span class="qa-top-tags-label"><a href="'.qa_path('start').'?topic='.$tag.'" class="qa-tag-link">'.$taglabel.'</a></span>
							</span>';
							// <span class="qa-tag-link qa-user-achieved">'.$qcorrect.' vs. '.$inqcorrect.'</span>
							
					if ((++$output) >= $pagesize)
					{
						break;							
					}
				} // end foreach

				$qa_content['custom'] .= '</div> <!-- end qa-part-ranking -->';
			}
			else
			{
				$qa_content['title'] = qa_lang_html('main/no_tags_found');
			}
			
			if($allcount==0)
			{
				// hide reset button as no questions were solved
				$qa_content['custom'] .= '
				<style type="text/css">
					#quizx-reset-gameplay-button {
						display:none;
					}
				</style>
				';
			}

			// *** personal statistic: Die persönliche Statistik je Mitglied steht demnächst zur Verfügung.
			// In der Liste der Fragen siehst du dann, welche Fragen du schon richtig gelöst hast. 

			$qa_content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $tagcount, qa_opt('pages_prev_next'));

			if (empty($qa_content['page_links']))
			{
				$qa_content['suggest_next'] = qa_html_suggest_ask();					
			}

			return $qa_content;
		} // end process
			
	}; // end class
	
	function quizx_qa_db_alphabetic_tags_selectspec($start, $count=null)
	{
		$count=isset($count) ? $count : QA_DB_RETRIEVE_TAGS;
		
		$validtags = qa_db_read_all_assoc(
						qa_db_query_sub(
							'SELECT tags, COUNT(tags) AS tagcount 
								FROM ^quizx_moderate
								WHERE status="2"
								GROUP BY tags
								LIMIT #,#
								',
							$start, $count
						)
					); 
		return $validtags;
		/*return array(
			'columns' => array('word', 'tagcount'),
			'source' => '^words JOIN (SELECT wordid FROM ^words WHERE tagcount>0 ORDER BY word ASC LIMIT #,#) y ON ^words.wordid=y.wordid',
			'arguments' => array($start, $count),
			'arraykey' => 'word',
			'arrayvalue' => 'tagcount',
			'sortdesc' => 'tagcount',
		);*/
	}

	function qa_tag_html_quiz($tag, $microformats=false, $favorited=false)
	{
		// return '<a href="'.qa_path_html('tag/'.$tag).'"'.($microformats ? ' rel="tag"' : '').' class="qa-tag-link'.
			// ($favorited ? ' qa-tag-favorited' : '').'">'.qa_html($tag).'</a>';
		return '<a href="'.qa_path_html('start').'/?topic='.$tag.'"'.($microformats ? ' rel="tag"' : '').' class="qa-tag-link'.
			($favorited ? ' qa-tag-favorited' : '').'">'.qa_html($tag).'</a>';
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/