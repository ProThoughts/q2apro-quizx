<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_events
	{
	
		function process_event($event, $userid, $handle, $cookieid, $params)
		{
			
			if(!qa_opt('quizx_enabled'))
			{
				return;
			}
			
			// if question gets posted, add it to the moderate stack
			if($event=='q_post' || $event=='q_edit')
			{
				$postid = $params['postid'];
				// if more than 1 tag make sure to take only first tag as topic
				$tagsArray = explode(',', $params['tags']);
				$tags = reset($tagsArray);
				
				// memo: "tags" in db should actually be called "topic"
				qa_db_query_sub(
					'INSERT INTO ^quizx_moderate (questionid,userid,status,tags) 
										VALUES (#,#,0,#) 
										ON DUPLICATE KEY UPDATE tags=VALUES(tags)',
					$postid, $userid, $tags
				);	
			} // end q_post
			
			// if question gets deleted, remove it also from table quizx_moderate
			if($event=='q_delete')
			{
				$postid = $params['postid'];
				qa_db_query_sub(
					'DELETE FROM ^quizx_moderate 
							WHERE questionid = #',
					$postid
				);
			} // end q_delete
			
		} // end process_event

	} // end class


/*
	Omit PHP closing tag to help avoid accidental output
*/