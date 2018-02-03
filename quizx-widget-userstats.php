<?php
/*
	Plugin Name: Q2APRO Quizx
*/

class quizx_widget_userstats 
{
	
	function allow_template($template)
	{
		return true;
	}
	
	function allow_region($region)
	{
		return true;
	}

	function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
	{
		// widget only on question page
		if($template!='question')
		{
			return;
		}
		
		$userid = qa_get_logged_in_userid();
		$cookieid = qa_cookie_get();
		
		if(isset($userid))
		{
			// get total stats
			/*
			$quser_stats_total = qa_db_read_one_assoc(
							qa_db_query_sub('SELECT questionid, SUM(correct=1) AS qcorrect, SUM(correct=0) AS qincorrect FROM `^quizx_gameplay`
								WHERE userid = #
								GROUP BY questionid
								ORDER BY timestamp', 
								$userid),
								true
							);
			*/
							
			// http://stackoverflow.com/questions/27355483/game-mysql-table-how-to-count-only-first-attempt-per-user-and-provide-total-sum
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

		}
		else
		{
			// anonymous - identify by cookie
			$ipaddress = qa_remote_ip_address();

			// get total stats, only consider first occurence: GROUP BY questionid and ORDER BY timestamp 
			/*$quser_stats_total = qa_db_read_one_assoc(
							qa_db_query_sub('SELECT questionid, SUM(correct=1) AS qcorrect, SUM(correct=0) AS qincorrect FROM `^quizx_gameplay`
								WHERE cookieid = #
								GROUP BY questionid
								ORDER BY timestamp', 
								$cookieid),
								true
							);*/
			$quser_stats_total = qa_db_read_one_assoc( 
							qa_db_query_sub('SELECT SUM(correct=1) AS qcorrect, SUM(correct=0) AS qincorrect FROM `^quizx_gameplay` 
												INNER JOIN
												(
													SELECT cookieid, questionid, MIN(timestamp) AS mintimestamp
													FROM `^quizx_gameplay` 
													WHERE cookieid = # 
													GROUP BY cookieid, questionid
												) sub0
												ON (
													^quizx_gameplay.cookieid IS NOT NULL
													AND ^quizx_gameplay.cookieid = sub0.cookieid
												)
												AND ^quizx_gameplay.questionid = sub0.questionid
												AND ^quizx_gameplay.timestamp =  sub0.mintimestamp
												WHERE ^quizx_gameplay.cookieid = #',
												$cookieid, $cookieid
												), true
											);
		}
		
		$qcorrect = $quser_stats_total['qcorrect'];
		$qincorrect = $quser_stats_total['qincorrect'];
		$lastquestions = '';
		$gradingline = '';
		$correctratio = -1;
		$gameplays = $qcorrect+$qincorrect;
		
		if($qcorrect+$qincorrect>0)
		{
			$correctratio = 100*$qcorrect / ($qcorrect+$qincorrect);
			$correctratio_out = round($correctratio);
			
			$correctratio_label = '<input type="text" value="'.$correctratio_out.'" class="usergrading-knob" data-readOnly=true >';
			
			// get last x questions
			$maxlqushow = qa_opt('quizx_lastqutoshow');
			
			// get total stats
			if(isset($userid))
			{
				$quser_lastactivities = qa_db_read_all_assoc(
								qa_db_query_sub('SELECT userid,timestamp,questionid,correct FROM `^quizx_gameplay`
									WHERE userid = #
									ORDER BY timestamp DESC 
									LIMIT #', 
									$userid, $maxlqushow)
								);
			}
			else
			{
				$quser_lastactivities = qa_db_read_all_assoc(
								qa_db_query_sub('SELECT userid,timestamp,questionid,correct FROM `^quizx_gameplay`
									WHERE cookieid = #
									ORDER BY timestamp DESC 
									LIMIT #', 
									$cookieid, $maxlqushow)
								);
			}
			
			if(count($quser_lastactivities))
			{
				$lastquestions .= '
					<div class="lastquestions">
					<p>
						<a target="_blank" href="'.qa_path('userstats').'#fullgp">'.qa_lang('quizx_lang/yourlastattempts').'</a>
					</p>';
				
				$lastquestions .= '<ul>';
				foreach($quser_lastactivities as $act)
				{
					// get data about question
					$qtitle = qa_db_read_one_value(qa_db_query_sub('SELECT title FROM ^posts 
																		WHERE postid = #', 
																		$act['questionid']));
					if(empty($qtitle))
					{
						continue; // $qtitle = '';
					}
					$qurl = qa_q_path($act['questionid'], $qtitle, true);
					$pitch_time = qa_opt('db_time')-strtotime($act['timestamp']);
					$correct_label = ($act['correct']==1) ? 
										'<span class="ustat_correct">'.qa_lang('quizx_lang/correct') : 
											'<span class="ustat_incorrect">'.qa_lang('quizx_lang/wrong');
					$lastquestions .= '
						<li>
							'.$correct_label.' 
							<span class="lastact_time">'.qa_lang_html_sub('main/x_ago', qa_html(qa_time_to_string($pitch_time))).' </span> </span>
							<a class="lastact_link" href="'.$qurl.'">'.$qtitle.'</a> <br />
						</li>';
				}
				$lastquestions .= '</ul> 
					</div> <!-- lastquestions -->';
			}
			
			// rating for user frontend
			$usergrade = '';
			$usergradedesc = '';
			if($correctratio==-1)
			{
				$usergrade = '';
			}
			else if($correctratio==100)
			{
				$usergrade = '1+';
				$usergradedesc = qa_lang('quizx_lang/rating_1a');
			}
			else if($correctratio>=92)
			{
				$usergrade = '1';
				$usergradedesc = qa_lang('quizx_lang/rating_1');
			}
			else if($correctratio>=81)
			{
				$usergrade = '2';
				$usergradedesc = qa_lang('quizx_lang/rating_2');
			}
			else if($correctratio>=67) {
				$usergrade = '3';
				$usergradedesc = qa_lang('quizx_lang/rating_3');
			}
			else if($correctratio>=50) {
				$usergrade = '4';
				$usergradedesc = qa_lang('quizx_lang/rating_4');
			}
			else if($correctratio>=30) {
				$usergrade = '5';
				$usergradedesc = qa_lang('quizx_lang/rating_5');
			}
			else {
				$usergrade = '6'; // 6 oh no…
				$usergradedesc = qa_lang('quizx_lang/rating_6');
			}
			
			// min 10 plays to show grade
			if($gameplays>10)
			{
				$gradingline = '
					<div class="ustat_grading">
						'.qa_lang('quizx_lang/grade').': '.$usergrade.' <span class="ustat_gradingdesc">'.$usergradedesc.'</span> 
					</div>
				';
			}
			
		} // end game results
		else 
		{
			$correctratio_label = qa_lang('quizx_lang/notplayedyet');
			$qcorrect = 0;
			$qincorrect = 0;
		}
		

		$output = ''; // init
		// Deine Spielstatistik
		$output .= '
		<div class="quizx_userstatsbox">'.
			'<div class="ustat_total">
				<p>
					'.qa_lang('quizx_lang/yourscore').':
				</p>
				<span class="ustat_percent">
					'.$correctratio_label.'
				</span>
				'.
				($gameplays>0?
				'<div class="ustat_scorecount">
					<span class="ustat_correct"><span>'.$qcorrect.'</span> '.qa_lang('quizx_lang/correct').'</span> 
					<span class="ustat_incorrect"><span>'.$qincorrect.'</span> '.qa_lang('quizx_lang/wrong').'</span> 
				</div>' : '').
			'</div>'.
			$gradingline.
			$lastquestions.
		'</div>
		';
		
		$themeobject->output(
			'<div class="quizx-userstats-widget">'.
			$output.
			'
			<div style="clear:both;"></div>
			<div class="ustat_buttonhold">
				<a class="qa-q-view-tag-item" href="'.qa_path('userstats').'">'.qa_lang('quizx_lang/yourstatsindetail').'</a>
			</div>
			</div> <!-- quizx-userstats-widget -->
			'
		);
	} // end function output_widget

} // end class quizx_widget_userstats



/*
	Omit PHP closing tag to help avoid accidental output
*/