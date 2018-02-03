<?php
/*
	Plugin Name: Q2APRO Quizx
*/

class quizx_widget_questionstats 
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
		// x % haben die Frage richtig beantwortet (z richtig vs. y falsch)
		// opt. zuletzt richtig beantwortet
		
		// widget only on question page
		if($template!='question')
		{
			return;
		}
		$questionid = $qa_content['q_view']['raw']['postid'];
		
		// check if post is by user
		// $isbyuser = qa_post_is_by_user($qa_content['q_view']['raw'], qa_get_logged_in_userid(), qa_cookie_get());
		
		// get status of question (0-open, 1-editdone, 2-published)
		$qstatus = quizx_check_q_status($qa_content['q_view']['raw']['postid']);
		if($qstatus!='published')
		{
			// unset widget questionstats and return
			// unset($qa_content['widgets']['side']['top']);
			$themeobject->output('
			<style type="text/css">
				.qa-widget-side.qa-widget-side-top {
					display:none;
				}
				.quizx-userstats-widget {
					display:none;
				}
				.qa-sidepanel {
					display:none;
				}
			</style>');

			return;
		}
		
		// get all game plays to this question from qa_quizx_gameplay
		/*
		$qstats = qa_db_read_one_assoc( 
							qa_db_query_sub('SELECT SUM(correct=1) AS correct, SUM(correct=0) AS incorrect,timestamp FROM `^quizx_gameplay` 
												WHERE questionid = #',
												$questionid
												), true
											);
		*/
		
		// only counting users, not anonymous
		/* 
		$qstats = qa_db_read_one_assoc( 
							qa_db_query_sub('SELECT SUM(correct=1) AS correct, SUM(correct=0) AS incorrect
												FROM `qa_quizx_gameplay` 
												INNER JOIN
												(
													SELECT userid, questionid, MIN(timestamp)  AS mintimestamp
													FROM `qa_quizx_gameplay` 
													WHERE questionid = #
													GROUP BY userid, questionid
												) sub0
												ON qa_quizx_gameplay.userid = sub0.userid
												AND qa_quizx_gameplay.questionid = sub0.questionid
												AND qa_quizx_gameplay.timestamp =  sub0.mintimestamp
												WHERE qa_quizx_gameplay.questionid = #',
												$questionid, $questionid
												), true
											);
		*/
		
		// http://stackoverflow.com/questions/27355483/game-mysql-table-how-to-count-only-first-attempt-per-user-and-provide-total-sum
		$qstats = qa_db_read_one_assoc( 
							qa_db_query_sub('SELECT SUM(correct=1) AS correct, SUM(correct=0) AS incorrect FROM `qa_quizx_gameplay` 
												INNER JOIN
												(
													SELECT userid, if(userid IS NULL, cookieid, NULL) AS cookieid, questionid, MIN(timestamp) AS mintimestamp
													FROM `qa_quizx_gameplay` 
													WHERE questionid = #
													GROUP BY userid, cookieid, questionid
												) sub0
												ON ((qa_quizx_gameplay.userid IS NULL
												AND qa_quizx_gameplay.cookieid = sub0.cookieid)
												OR (qa_quizx_gameplay.userid IS NOT NULL
												AND qa_quizx_gameplay.userid = sub0.userid))
												AND qa_quizx_gameplay.questionid = sub0.questionid
												AND qa_quizx_gameplay.timestamp =  sub0.mintimestamp
												WHERE qa_quizx_gameplay.questionid = #',
												$questionid, $questionid
												), true
											);
		
		$output = '<div class="quizx_questionstats">';
		if(($qstats['correct']+$qstats['incorrect'])>0)
		{
			$correctratio = 100*$qstats['correct'] / ($qstats['correct']+$qstats['incorrect']);
			$correctratio_string = number_format((float)$correctratio, 1, ',', '');
			$barcolor = $this->q2apro_getColorForPercentage($correctratio/100);
			$percentcolor = '#F5F5F5';
			if($correctratio==0)
			{
				$percentcolor = '#111111';				
			}
			// initiate output string
			$output .= '<div class="progress-bar shine">
							<span style="width:'.floor($correctratio).'%;background:'.$barcolor.';"></span>
							<span style="position:absolute;left:0;top:0;color:'.$percentcolor.';" title="('.$qstats['correct'].' '.qa_lang('quizx_lang/correct').' '.qa_lang('quizx_lang/versus').' '.$qstats['incorrect'].' '.qa_lang('quizx_lang/wrong').')">
								&ensp;'.$correctratio_string.' %
							</span>								
						</div>';
			$output .= '
				<p style="font-size:12px;">
					&ensp;'.qa_lang('quizx_lang/haveansweredcorrect').'
				</p>
			';
			// $output .= '<p><span style="font-size:20px;font-weight:bold;" title="('.$qstats['correct'].' richtig vs. '.$qstats['incorrect'].' falsch)">'.$correctratio_string.' %</span> <span style="font-size:12px;">haben die Frage beim ersten Versuch richtig beantwortet.</span></p>';
		}
		else
		{
			// $output .= '<p>Diese neue Quiz-Aufgabe hat noch keiner gelöst. Du bist der erste Spieler!</p>';
			$output .= '
				<p>
					'.qa_lang('quizx_lang/newqu_urfirst').'
				</p>
			';
		}
		
		// get userdata
		/*
		if(isset($act['userid'])) {
			$user = qa_db_read_one_assoc(qa_db_query_sub('SELECT handle, avatarblobid FROM ^users 
															WHERE userid = #', 
															$act['userid']));
			$imgsize = 20;
			if(isset($user['avatarblobid'])) {
				$avatar = './?qa=image&qa_blobid='.$user['avatarblobid'].'&qa_size='.$imgsize;
			}
			else {
				$avatar = './?qa=image&qa_blobid='.qa_opt('avatar_default_blobid').'&qa_size='.$imgsize;
			}
			$userprofilelink = qa_path_html('user/'.$user['handle']);
			$handledisplay = qa_html($user['handle']);
		}
		else {
			// anonymous
			$handledisplay = 'Ein Gast';
		}
		*/
		// get data about question
		/*$qtitle = qa_db_read_one_value(qa_db_query_sub('SELECT title FROM ^posts 
															WHERE postid = #', 
															$act['questionid']));
		if(is_null($qtitle)) {
			$qtitle = '';
		}
		$qurl = qa_q_path($act['questionid'], $qtitle, true);
		$pitch_time = qa_opt('db_time')-strtotime($act['timestamp']);
		$correct_label = ($act['correct']==1) ? 'richtig' : 'falsch';
		$output .= '<li style="padding:5px 0;">'.qa_lang_html_sub('main/x_ago', qa_html(qa_time_to_string($pitch_time))).' - '.$handledisplay.' hat die Testfrage: <a href="'.$qurl.'">'.$qtitle.'</a> '.$correct_label.' beantwortet.</li>';
		*/

		$output .= '</div> <!-- end quizx_questionstats -->';

		
		// SHOULD BE STATISTICS WIDGET LATER ON
		// count all available questions
		$questioncount = qa_db_read_one_value( 
							qa_db_query_sub('SELECT COUNT(questionid) FROM `^quizx_moderate` 
												WHERE `status` = "2"
												'),
												true
											);
		// output widget into theme
		// '<p style="font-size:14px;">Statistik zur Frage:<p>'.
		$themeobject->output('
			<div class="quizx-questionstats-widget">
				'.$output.'
			</div>
			<div style="clear:both;"></div>'
		);

	} // end function output_widget

	function q2apro_getColorForPercentage($pct) 
	{
		$percentColors[] = array(
			"pct" => 0.0, 
			"color" => array( "r" => 0xff, "g" => 0x00, "b" => 0 ),
		);
		$percentColors[] = array(
			"pct" => 0.5, 
			"color" => array( "r" => 0xcc, "g" => 0xbb, "b" => 0 ),
		);
		$percentColors[] = array(
			"pct" => 1.0, 
			"color" => array( "r" => 0x00, "g" => 0xaa, "b" => 0 ),
		);
	
		for($i=1; $i<count($percentColors)-1; $i++) 
		{
			if($pct < $percentColors[$i]['pct']) 
			{
				break;
			}
		}
		$lower = $percentColors[$i-1];
		$upper = $percentColors[$i];
		$range = $upper['pct'] - $lower['pct'];
		$rangePct = ($pct - $lower['pct']) / $range;
		$pctLower = 1 - $rangePct;
		$pctUpper = $rangePct;
		$color = array(
			"r" => floor($lower['color']['r'] * $pctLower + $upper['color']['r'] * $pctUpper),
			"g" => floor($lower['color']['g'] * $pctLower + $upper['color']['g'] * $pctUpper),
			"b" => floor($lower['color']['b'] * $pctLower + $upper['color']['b'] * $pctUpper)
		);
		
		// return array( $color['r'], $color['g'], $color['b'] );
		// to hex
		return $this->rgb2hex( array( $color['r'], $color['g'], $color['b'] ) );
	} // end q2apro_getColorForPercentage($pct)
	
	// http://bavotasan.com/2011/convert-hex-color-to-rgb-using-php/
	function rgb2hex($rgb) 
	{
		$hex = "#";
		$hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

		return $hex;
	}
	
} // end class quizx_widget_questionstats 

/*
	Omit PHP closing tag to help avoid accidental output
*/