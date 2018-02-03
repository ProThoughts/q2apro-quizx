<?php
/*
	Plugin Name: Q2APRO Quizx
*/

class quizx_widget_activities
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
		// max users to show
		$maxentries = 4;
		
		// get all recent activites from qa_quizx_gameplay
		$lastActivities = qa_db_read_all_assoc( 
							qa_db_query_sub('SELECT userid,timestamp,questionid,correct,cookieid FROM `^quizx_gameplay` 
												ORDER BY timestamp DESC 
												LIMIT #',
												$maxentries
												)
											);
											//GROUP BY userid 
		
		// initiate output string
		$output = '<div class="quizx_activities">';
		$output .= '<ul>';
		foreach($lastActivities as $act)
		{
			// get userdata
			if(isset($act['userid']))
			{
				$user = qa_db_read_one_assoc(qa_db_query_sub('SELECT handle, avatarblobid FROM ^users 
																WHERE userid = #', 
																$act['userid']));
				$imgsize = 20;
				if(isset($user['avatarblobid']))
				{
					$avatar = './?qa=image&qa_blobid='.$user['avatarblobid'].'&qa_size='.$imgsize;
				}
				else 
				{
					$avatar = './?qa=image&qa_blobid='.qa_opt('avatar_default_blobid').'&qa_size='.$imgsize;
				}
				// $userprofilelink = qa_path('user/'.$user['handle']);
				$userprofilelink = qa_path('userstats').'?userid='.$act['userid'].'&t';
				$handledisplay = qa_html($user['handle']);
			}
			else 
			{
				// anonymous
				// $handledisplay = 'Ein Gast';
				$guestid = getguestid_bycookie($act['cookieid']);
				$handledisplay = 'Gast '.getshorterguestid($guestid);
				$userprofilelink = qa_path('userstats').'?guestid='.$guestid.'&t';
			}
			
			// user item
			/*$output .= '<a class="xavatar81" style="background:url(\''.$avatar.'\') no-repeat top;background-size:cover;" href="'.$userprofilelink.'">
			<br />
			<span class="quizx-user-link">'.$handledisplay.'</span></a>';
			*/
			// get data about question
			$qtitle = qa_db_read_one_value(qa_db_query_sub('SELECT title FROM ^posts 
																WHERE postid = #', 
																$act['questionid']));
			if(empty($qtitle))
			{
				$qtitle = '';
			}
			$qurl = qa_q_path($act['questionid'], $qtitle, true);
			$pitch_time = qa_opt('db_time')-strtotime($act['timestamp']);
			$correct_label = ($act['correct']==1) ? qa_lang('quizx_lang/correct') : qa_lang('quizx_lang/wrong');
			$output .= '<li style="padding:5px 0;">'.qa_lang_html_sub('main/x_ago', qa_html(qa_time_to_string($pitch_time))).' - ';
			
			$output .= '<a target="_blank" style="color:#34495E;" href="'.$userprofilelink.'">'.$handledisplay.'</a>';
			$output .= ' '.qa_lang('quizx_lang/hasthequestion').': <a href="'.$qurl.'">'.$qtitle.'</a> '.$correct_label.' '.qa_lang('quizx_lang/answered').'.</li>';
		}
		$output .= '
			</ul>
		</div> <!-- end quizx_activities -->
		';

		
		// SHOULD BE STATISTICS WIDGET LATER ON
		// count all available questions
		$questioncount = qa_db_read_one_value( 
							qa_db_query_sub('SELECT COUNT(questionid) FROM `^quizx_moderate` 
												WHERE `status` = "2"
												'),
												true
											);
		// output widget into theme
		$themeobject->output('
			<div class="quizx-activities-widget">
				<p style="font-size:14px;">
					'.qa_lang('quizx_lang/whoplaysnow').':
				</p>
				'.$output.'
			</div>
			<div style="clear:both;"></div>
		');

	} // end output_widget

} // end class quizx_widget_activities

/*
	Omit PHP closing tag to help avoid accidental output
*/