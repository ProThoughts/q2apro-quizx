<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	function qa_page_q_post_rules($post, $parentpost=null, $siblingposts=null, $childposts=null) 
	{
		// fake post type Q for non-posting-blocks
		// $post['type'] = 'Q';
		
		// call default
		$rules = qa_page_q_post_rules_base($post, $parentpost, $siblingposts, $childposts);

		return $rules;
	}
	
/*							  
		Omit PHP closing tag to help avoid accidental output
*/