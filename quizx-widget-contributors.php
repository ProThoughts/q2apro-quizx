<?php
/*
	Plugin Name: Q2APRO Quizx
*/

class quizx_widget_contributors 
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
		// this also gets the unpublished questions
		/*
		$lastContributors = qa_db_read_all_assoc( 
							qa_db_query_sub('SELECT userid, postid, created, upvotes, title, acount, views FROM `^posts` 
												WHERE `userid` IS NOT NULL 
												AND `type` = "Q"
												AND `closedbyid` IS NULL
												GROUP BY userid
												ORDER BY created DESC
												LIMIT 5'
												)
											);
		*/
		
		// max users to show
		$maxusers = 6;
		
		// only users with published questions are considered
		$lastContributors = qa_db_read_all_assoc( 
							qa_db_query_sub('SELECT userid FROM `^quizx_moderate` 
												WHERE `userid` IS NOT NULL 
												AND `status` = "2"
												GROUP BY userid 
												ORDER BY questionid DESC 
												LIMIT #',
												$maxusers
												)
											);
		
		// initiate output string
		$output = '
			<div class="quizx_userlisting">
			<p>
				'.qa_lang('quizx_lang/lastcontributors').'
			</p>
		';
		foreach($lastContributors as $con) 
		{
			// get userdata
			$user = qa_db_read_one_assoc(qa_db_query_sub('SELECT handle, avatarblobid FROM ^users 
															WHERE userid = #', 
															$con['userid']));
			$imgsize = 100;
			if(isset($user['avatarblobid']))
			{
				$avatar = './?qa=image&qa_blobid='.$user['avatarblobid'].'&qa_size='.$imgsize;
			}
			else
			{
				$avatar = './?qa=image&qa_blobid='.qa_opt('avatar_default_blobid').'&qa_size='.$imgsize;
			}
			$userprofilelink = qa_path_html('user/'.$user['handle']);
			$handledisplay = qa_html($user['handle']);
			
			// user item
			$output .= '
				<a class="xavatar81" style="background:url(\''.$avatar.'\') no-repeat top;background-size:cover;" href="'.$userprofilelink.'">
					<span class="quizx-user-link">'.$handledisplay.'</span>
				</a>
				';
		}
		
		$output .= '</div> <!-- end quizx_userlisting -->';

		// extra buttons
		$themeobject->output($output);

	} // end function output_widget

} // end class quizx_widget_contributors


/*
	Omit PHP closing tag to help avoid accidental output
*/