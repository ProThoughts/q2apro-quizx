<?php
/*
	Plugin Name: Q2APRO Quizx
*/

class quizx_widget_skipquestion
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
		// $cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();
		$cookieid = qa_cookie_get();

		$topic = $qa_content['q_view']['raw']['tags'];
		$recentquid = $qa_content['q_view']['raw']['postid'];
		
		// check if recent question is in edit mode, if so, return
		$ineditmode = qa_db_read_one_value(
						qa_db_query_sub(
							'SELECT status FROM ^quizx_moderate
								WHERE questionid = #',
							$recentquid
						),
						true
					);
		if($ineditmode!='2')
		{
			// no output to skip the question
			return;
		}
			
		$nextquestion = null;
		// $nextquestion = quizx_get_next_question($userid, $cookieid, $topic, $recentquid);
		
		if(is_null($topic) || is_null($recentquid))
		{
			return null;
		}
		
		$gplaytime = getplaytimestamp($userid,$cookieid);
		
		// get the next question in this topic for the user to answer
			// match all questionids the user has already answered TODAY against the q-postids to this topic in qa_posts
			// makes sure only to consider published question (status 2)
		$random = false;
		$ordermode = $random ? 'RAND()' : '^quizx_moderate.questionid ASC';
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
							ORDER BY #
							',
					$topic, $userid, $gplaytime, $ordermode
								// AND DATE(`timestamp`) = CURDATE()) 
				)
			);
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
		
		$qleft = count($qleftx)-1;
		$nextqid = -1; // -1 is no next question
		if($qleft>0)
		{
			// check if our recent question is last in index, get last value
			$lastelement = end($qleftx);
			if($recentquid == $lastelement['questionid'])
			{
				// take the first question id
				foreach ($qleftx as $q)
				{
					$nextqid = $q['questionid'];
					break;
				}
			}
			else
			{
				// take the question id after the $recentquid
				foreach ($qleftx as $q)
				{
					$nextqid = $q['questionid'];
					if($nextqid > $recentquid)
					{
						break;					
					}
				}
			}
		}

		if($nextqid!=-1)
		{
			$nextquestionurl = qa_path($nextqid);
			$nextquestiontitle = '';
			$qucountstring = '';
			if($qleft==1)
			{
				$qucountstring = '1 '.qa_lang('quizx_lang/quizquestion');
			}
			else
			{
				$qucountstring = $qleft.' '.qa_lang('quizx_lang/quizquestions');
			}
			$nextquestion = '
				<a href="'.$nextquestionurl.'" title="'.$nextquestiontitle.'" class="qa-form-light-button-inform">
					'.qa_lang('quizx_lang/skipquestion').'
				</a>
				<p style="font-size:12px;margin-top:10px;">
					'.$qucountstring.' '.qa_lang('quizx_lang/quleft').'
					<br />
					'.qa_lang('quizx_lang/fortopic').' '.implode('-', array_map('ucfirst', explode('-', $topic))).'
				</p>
				';
		}
		// 5 von 10 Fragen zum Thema beantwortet
			
		if(isset($nextquestion))
		{
			// skip question to next one
			$themeobject->output('
				<div class="quizx-skip-question">
					'.$nextquestion.'
				</div>
				<div style="clear:both;"></div>
			');
		}
		
		
		// special: contact button to redaktion
		if($template=='question')
		{
			$themeobject->output('
			<div class="quizx-question-feedback">
				<a href="'.qa_path('feedback').'" class="qa-form-wide-button quizx-feedback-button tooltip" title="'.qa_lang('quizx_lang/hinttoquestion_tooltip').'">
					'.qa_lang('quizx_lang/hinttoquestion').'
				</a>
			</div>
			');
		}
		
	}

}



/*
	Omit PHP closing tag to help avoid accidental output
*/