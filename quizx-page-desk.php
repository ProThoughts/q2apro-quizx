<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_page_desk
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
					'request' => 'desk', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='desk')
			{
				return true;
			}
			return false;
		}

		function process_request($request)
		{
		
			if(qa_opt('quizx_enabled')!=1)
			{
				$qa_content=qa_content_prepare();
				$qa_content['error'] = '<div>'.qa_lang('quizx_lang/plugin_disabled').'</div>';
				return $qa_content;
			}
			// return if permission level is not sufficient
			if(qa_user_permit_error('quizx_permission'))
			{
				$qa_content=qa_content_prepare();
				$qa_content['error'] = qa_lang('quizx_lang/access_forbidden');
				return $qa_content;
			}
				
			/* AJAX */
			$transferString = qa_post_text('questionid');
			if(isset($transferString)) 
			{
				$questionid = (int)$transferString;
				qa_db_query_sub('UPDATE `^quizx_moderate` SET status = "0"
													WHERE `questionid` = #
												 ', $questionid);
				
				echo 'question status now 0';
				exit();
			} // END AJAX
		
			/* start */
			$qa_content=qa_content_prepare();
			qa_set_template('quizx-deskpage');
			$qa_content['title'] = qa_lang('quizx_lang/page_editordesk_title');

			// do pagination
			$start = (int)qa_get('start'); // gets start value from URL
			$pagesize = 50; // items per page
			
			// query to get all posts according to pagination, ignore closed questions
			$queryAllPosts = qa_db_query_sub('SELECT postid,userid,createip,tags,title,content,created,format FROM `^posts`
												WHERE `type` = "Q"
												AND `closedbyid` IS NULL
												ORDER BY postid DESC
												LIMIT #,#
											', $start, $pagesize);

			// initiate output string
			$tagtable = '
			<table class="tagtable"> 
				<thead> <tr> 
					<th>'.qa_lang('quizx_lang/th_status').'</th> 
					<th>'.qa_lang('quizx_lang/th_created').'</th> 
					<th>'.qa_lang('quizx_lang/th_creator').'</th> 
					<th>'.qa_lang('quizx_lang/th_questiontitle').'</th> 
					<th>'.qa_lang('quizx_lang/th_posttags').'</th> 
				</tr></thead>
			';
			// <th>'.qa_lang('quizx_lang/th_postid').'</th> 
			$maxlength = qa_opt('mouseover_content_max_len'); // 480
			
			// require_once QA_INCLUDE_DIR.'qa-util-string.php'; // for qa_shorten_string_line()
			// $blockwordspreg = qa_get_block_words_preg();
			
			// get all questions with their statuses
			$statusesQ = qa_db_query_sub('SELECT questionid,userid,status 
								FROM `^quizx_moderate`
								;'); 
			$statuses = array();
			while( ($q = qa_db_read_one_assoc($statusesQ,true)) !== null )
			{
				$statuses[$q['questionid']] = $q['status'];
			}
			
			$count = 0;
			while( ($row = qa_db_read_one_assoc($queryAllPosts,true)) !== null )
			{
				$count++;
				// $text=qa_viewer_text($row['content'], $row['format'], array('blockwordspreg' => $blockwordspreg));
				// $contentPreview = qa_html(qa_shorten_string_line($text, $maxlength));
				$qstatus = $statuses[$row['postid']];
				$qstatus_label = 'none';
				$trclass = '';
				if($qstatus==0)
				{
					$qstatus_label = qa_lang('quizx_lang/status_getscreated');
					$trclass = 'topen';
				}
				else if($qstatus==1)
				{
					$qstatus_label = qa_lang('quizx_lang/status_checkrelease');
					$trclass = 'teditdone';
				}
				else if($qstatus==2)
				{
					$qstatus_label = qa_lang('quizx_lang/published');
					$trclass = 'tpublic';
					// do not show, no need for moderation
					continue;
				}
				$userhandle = qa_userid_to_handle($row['userid']);
				if(is_null($userhandle))
				{
					$userhandle = qa_lang('quizx_lang/guest');
					$userhandle_url = qa_ip_anchor_html( long2ip($row['createip']) );
				}
				else
				{
					$userhandle_url = '<a href="'.qa_path('user/'.$userhandle).'">'.$userhandle.'</a>';
				}
				
				$qtime = qa_opt('db_time')-strtotime($row['created']);
				$qcreated = qa_lang_html_sub('main/x_ago', qa_html(qa_time_to_string($qtime)));
				
				$unlockedit = '';
				if($qstatus==1)
				{
					// add link to reset again (so user can edit again)
					$unlockedit = '<a class="unlockedit" id="'.$row['postid'].'">'.qa_lang('quizx_lang/unlock').'?</a>';
				}
				$tagtable .= '
					<tr data-original="'.$row['postid'].'" class="'.$trclass.'">
						<td>'.$qstatus_label.$unlockedit.'</td>
						<td>'.$qcreated.'</td>
						<td>'.$userhandle_url.'</td>
						<td><a href="./'.$row['postid'].'">'.$row['title'].'</a></td> 
						<td><a href="./tag/'.$row['tags'].'">'.$row['tags'].'</a></td>
					</tr>';
			}
			$tagtable .= '</table>';
			
			// pagination
			// $count = qa_opt('cache_qcount'); // items total
			$qa_content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $count, true); // last parameter is prevnext

			// init custom content
			$qa_content['custom'] = '';
			
			$qa_content['custom'] .= $tagtable;

			$qa_content['custom'] .= "
			<script type=\"text/javascript\">
				$(document).ready(function()
				{
					$('.unlockedit').click( function(e) {
						e.preventDefault();
						var qid = $(this).attr('id')
						var clicked = $(this);
						$.ajax({
							 type: 'POST',
							 url: '".qa_self_html()."',
							 data: { questionid: qid },
							 cache: false,
							 success: function(data) {
								console.log('received: '+data);
								// inform on success
								$(this).hide();
							 },
							 error: function(data) {
								alert('problem: '+data);
							 }
						});
					});
					
				});
			</script>";
			
			// make newest users list bigger on page
			$qa_content['custom'] .= '
			<style type="text/css">
				.qa-sidepanel {
					display:none;
				}
				.qa-main {
					width:100%;
				}
				.topen {
					background:#FFF;
				}
				.teditdone {
					background:#CFC;
				}
				.tpublic {
					background:#FFF;
				}
				.qa-main table {
					width:100%;
					background:#F5F5F5;
					margin:30px 0 15px;
					text-align:left;
					border-collapse:collapse;
				}
				.qa-main table th {
					padding:4px;
					border:1px solid #CCC;
					text-align:center;
					background:#FFD;
					font-weight:normal;
				}
				.qa-main table tr td:nth-child(1) {
					text-align:left !important;
				}
				.qa-main table tr:hover {
					background:#FFD;
				}
				.qa-main table th:nth-child(1), table td:nth-child(1) {
					width:60px;
					text-align:center;
				}
				.qa-main td {
					border:1px solid #CCC;
					padding:1px 10px;
					line-height:25px;
				}
				.qa-main table.tagtable td a { 
				}
			    input.post_title, input.post_tags, .inputdefault {
					width:100%;
					border:1px solid transparent;
					padding:3px;
					background:transparent;
				}
				input.post_title:focus, input.post_tags:focus, .inputactive {
					background:#FFF !important;
					box-shadow:0 0 2px #7AF
				}
				.post_title_td, .post_tags_td {
					position:relative;
				}
				.sendr,.sendrOff {
					padding:3px 10px;
					background:#FC0;
					border:1px solid #FEE;
					border-radius:2px;
					position:absolute;
					right:-77px;
					top:-5px;
					color:#123;
					cursor:pointer;
				}
				.sendrOff {
					text-decoration:none !important;
				}
				.tagtable {
				  font-size:14px;
				}
				.tagtable tr td:nth-child(1) {
				  width:9%;
				}
				.tagtable tr td:nth-child(2) {
				  width:11%;
				}
				.tagtable tr td:nth-child(3) {
				  width:11%;
				}
				.tagtable tr td:nth-child(4) {
				  width:50%;
				}
				.tagtable tr td:nth-child(5) {
				  width:10%;
				}
				.unlockedit {
					font-size:12px;
					display:block;
					cursor:pointer;
				}
			</style>';
			

			return $qa_content;
		}
		
	}; // end class quizx_page_desk
	

/*
	Omit PHP closing tag to help avoid accidental output
*/