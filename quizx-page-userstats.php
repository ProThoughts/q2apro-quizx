<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_page_userstats
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
					'title' => 'Quizx Page Userstats', // title of page
					'request' => 'userstats', // request name
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}
		
		// for url query
		function match_request($request)
		{
			if ($request=='userstats')
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
				
			/* start */
			$qa_content=qa_content_prepare();
			qa_set_template('quizx-userstats-page');
			$qa_content['title'] = qa_lang('quizx_lang/yourstats');

			// do pagination
			/*
			$start = (int)qa_get('start'); // gets start value from URL
			$count = qa_opt('cache_qcount'); // items total
			$pagesize = 500; // items per page
			$qa_content['page_links'] = qa_html_page_links(qa_request(), $start, $pagesize, $count, true); // last parameter is prevnext
			*/
			
			// identify user
			$userid = qa_get_logged_in_userid();
			$cookieid = qa_cookie_get();
		
			// display other players statistics
			$otheruser = false;
			$userid_in = qa_get('userid');
			$guestid_in = qa_get('guestid');
			
			if(!empty($userid_in))
			{
				$userid = (int)$userid_in;
				$cookieid = null;
				$otheruser = true;
				// looking to his own profile
				if($userid == qa_get_logged_in_userid())
				{
					$otheruser = false;					
				}
			}
			else if(!empty($guestid_in))
			{
				$cookieid = getcookie_byguestid($guestid_in); // e.g. 29mebwd4f
				$userid = null;
				$otheruser = true;
			}
			
			// get last x questions
			$maxlqushow = 1000;
			
			// only show last 24 hours
			$today = qa_get('t');
			$onlytoday = isset($today);
			$mysqltoday = '';
			if($onlytoday)
			{
				$mysqltoday = 'AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY) ';
			}
			
			// get total stats
			if(isset($userid))
			{
				$quser_qplayed = qa_db_read_all_assoc(
									qa_db_query_sub('SELECT userid,timestamp,questionid,correct,elapsed FROM `^quizx_gameplay`
										WHERE userid = # 
										'.$mysqltoday.'
										ORDER BY timestamp DESC 
										LIMIT #', 
										$userid, $maxlqushow)
									);
			}
			else
			{
				$quser_qplayed = qa_db_read_all_assoc(
									qa_db_query_sub('SELECT userid,timestamp,questionid,answerid,correct,elapsed FROM `^quizx_gameplay`
										WHERE cookieid = # 
										'.$mysqltoday.'
										ORDER BY timestamp DESC 
										LIMIT #', 
										$cookieid, $maxlqushow)
									);
			}

			// init
			$qa_content['custom'] = '';
			
			$lastquestions = '';
			$gameplays = count($quser_qplayed);
			
			// memo: http://stackoverflow.com/questions/27355483/game-mysql-table-how-to-count-only-first-attempt-per-user-and-provide-total-sum
			
			if($gameplays>0)
			{

				$userhandle = qa_userid_to_handle($userid);
				if(is_null($userhandle)) 
				{
					$userhandle = qa_lang('quizx_lang/guest').' '.getshorterguestid($guestid_in); // with id
					$userhandle_url = qa_lang('quizx_lang/guest').' '.getshorterguestid($guestid_in);
				}
				else 
				{
					$userhandle_url = '<a href="'.qa_path('user/'.$userhandle).'">'.$userhandle.'</a>';
				}
			
				$gpstring = '';
				if($onlytoday)
				{
					$gpstring = qa_lang('quizx_lang/24hour');					
				}
				
				if($otheruser)
				{
					// change page title
					$qa_content['title'] = $gpstring.qa_lang('quizx_lang/gameplayfrom').' '.$userhandle;					
					// user identifier frontend
					$qa_content['custom'] .= '
						<p style="margin:20px 0 0 0;">
							'.qa_lang('quizx_lang/thatsthe').' '.$gpstring.qa_lang('quizx_lang/gameplayfrom').' '.$userhandle_url.'. 
							'.qa_lang('quizx_lang/playquizqu').': '.$gameplays.'
						</p>
						';
				}
				else 
				{
					// user identifier frontend
					$qa_content['custom'] .= '
						<p style="margin:20px 0 0 0;">
							'.str_replace('~name~', $userhandle_url, qa_lang('quizx_lang/yourstats_hint')).'
							'.str_replace('~x~', $gameplays, qa_lang('quizx_lang/yourstats_played')).'
						</p>
						';
				}
				
				$urlparam = '';
				if($otheruser)
				{
					if(isset($userid))
					{
						$urlparam = '?userid='.$userid.($onlytoday ? '' : '&t');
					}
					else
					{
						$urlparam = '?guestid='.getguestid_bycookie($cookieid).($onlytoday ? '' : '&t');
					}
				}
				else
				{
					if($onlytoday)
					{
						$urlparam = '';
					}
					else
					{
						$urlparam = '?t';						
					}
				}
				
				if($onlytoday)
				{
					$qa_content['custom'] .= '
						<p style="font-size:12px;margin-bottom:20px;">
							<a href="'.qa_path('userstats').$urlparam.'">
								'.qa_lang('quizx_lang/showfullgameplay').'
							</a>
						</p>
					';
				}
				else 
				{
					$qa_content['custom'] .= '
						<p style="font-size:12px;margin-bottom:20px;">
							<a href="'.qa_path('userstats').$urlparam.'">
								'.qa_lang('quizx_lang/showfullgameplay24').'
							</a>
						</p>
					';
				}
				
				
					
				// init 
				$questiontable = '';

				// initiate output string
				$questiontable .= '
				<h2 style="margin-top:100px;" id="fullgp">
					'.qa_lang('quizx_lang/gameplay').'
				</h2>
				<p>
					'.qa_lang('quizx_lang/yourfullgameplay').'
				</p>
				
				<table class="ratingtable"> 
				<thead> 
					<tr> 
						<th>'.qa_lang('quizx_lang/topic').'</th> 
						<th>'.qa_lang('quizx_lang/firstattempt').'</th> 
						<th>'.qa_lang('quizx_lang/questiontitle').'</th> 
						<th>'.qa_lang('quizx_lang/time').'</th> 
					</tr>
				</thead>
				';
				
				// <th>'.qa_lang('quizx_lang/th_postid').'</th> 
				$maxlength = qa_opt('mouseover_content_max_len'); // 480
				// $text=qa_viewer_text($row['content'], $row['format'], array('blockwordspreg' => $blockwordspreg));
				// $contentPreview = qa_html(qa_shorten_string_line($text, $maxlength));
				
				$qstatus_label = '<span class="ustat_correct">'.qa_lang('quizx_lang/correct').'</span>';
				$trclass = '';
				
				$ucorrectcnt = 0;
				$uincorrectcnt = 0;
				$elapsedaverage = 0;
				$elapsedcount = 0;

				$overall = array();
				
				foreach($quser_qplayed as $row)
				{
				
					// get question data
					$qudata = qa_db_read_one_assoc( qa_db_query_sub('SELECT created,title,tags FROM `^posts`
														WHERE postid = #', 
														$row['questionid']), 
													true);
					
					if(!isset($overall[$qudata['tags']]))
					{
						$overall[$qudata['tags']] = array(
							'correct' => 0, 
							'wrong' => 0							
						);
					}
					// assign score
					if($row['correct'])
					{
						$overall[$qudata['tags']]['correct']++;							
					}
					else 
					{
						$overall[$qudata['tags']]['wrong']++;
					}
					
					// format topic
					$topiclabel = implode('-', array_map('ucfirst', explode('-', $qudata['tags'])));
					$topiclabel = str_replace('-', ' ', $topiclabel);
					
					$qtime = qa_opt('db_time')-strtotime($row['timestamp']);
					$qcreated = qa_lang_html_sub('main/x_ago', qa_html(qa_time_to_string($qtime)));

					if($row['correct']==1)
					{
						$ucorrectcnt++;
						$qstatus_label = '<span class="ustat_correct before_correct">'.qa_lang('quizx_lang/correct').'</span>';
						$trclass = 'td-correct';
					}
					else if($row['correct']==0)
					{
						$uincorrectcnt++;
						$qstatus_label = '<span class="ustat_incorrect before_incorrect">'.qa_lang('quizx_lang/wrong').'</span>';
						$trclass = 'td-incorrect';
					}
					
					$timeneeded = isset($row['elapsed']) ? $row['elapsed'].' '.qa_lang('quizx_lang/seconds_abbr') : '-'; 
					if(isset($row['elapsed']))
					{
						$elapsedaverage += (int)$row['elapsed'];
						$elapsedcount++;
					}
					
					$questiontable .= '
						<tr data-original="'.$row['questionid'].'" class="'.$trclass.'">
							<td>
								<a class="qa-tag-link" href="./tag/'.$qudata['tags'].'">'.$topiclabel.'</a>
							</td>
							<td>'.$qstatus_label.'<br />
								<span style="font-size:12px;color:#999;">'.$qcreated.'</span>
							</td>
							<td>
								<a href="./'.$row['questionid'].'">'.$qudata['title'].'</a>
							</td> 
							<td>'.
								$timeneeded.
							'</td>
						</tr>';
						// title="'.$contentPreview.'" class="tooltip"
						// <td>'.$row['questionid'].'</td>
				} // end foreach quser_qplayed
				
				$uratingtotal = 100*$ucorrectcnt/($ucorrectcnt+$uincorrectcnt);
				$uratingtotal = str_replace(',0', '', number_format((float)$uratingtotal, 1, ',', '')); // round to 1 digit
				
				$questiontable .= '</table>'; // end table
				
				$bytopictable = '
					<div id="bytopictable">
					<h2 style="padding:20px 0 0 0;">
						'.qa_lang('quizx_lang/gameplaybytopic').'
					</h2>
					
					<table class="topicratingtable"> 
					<tr>
						<th>
							'.qa_lang('quizx_lang/topic').'
						</th>
						<th>
							% '.qa_lang('quizx_lang/correct').'
						</th>
						<th>
							'.qa_lang('quizx_lang/correct').'
						</th>
						<th>
							'.qa_lang('quizx_lang/wrong').'
						</th>
					</tr>
				';
					
				// sort $overall alphabetically
				ksort($overall);
				
				$correct_count = 0;
				$wrong_count = 0;
				
				foreach($overall as $key => $val)
				{
					$total = $val['correct'] + $val['wrong'];
					// $correctperc = number_format((float)$val['correct']/$total, 1, ',', '');
					$correctperc = str_replace(',0', '', number_format((100*(float)$val['correct']/$total), 1, ',', ''));
					
					// format topic
					$topiclabel = implode('-', array_map('ucfirst', explode('-', $key)));
					$topiclabel = str_replace('-', ' ', $topiclabel);

					$bytopictable .= '<tr>
						<td>
							'.$topiclabel.'
						</td>
						<td>'.
							$correctperc.' % 
						</td>
						<td>
							<span class="ustat_correct before_correct">'.$val['correct'].'</span>
						</td>
						<td>
							<span class="ustat_incorrect before_incorrect">'.$val['wrong'].'</span> 
						</td>
					</tr>';
					
					$correct_count += (int)$val['correct'];
					$wrong_count += (int)$val['wrong'];
				}
				
				$correctperc = str_replace(',0', '', number_format(100*(float)$correct_count / ($correct_count+$wrong_count), 1, ',', '.'));
				
				// last row
				$bytopictable .= '<tr>
						<td>
							<b>'.qa_lang('quizx_lang/total').'</b>
						</td>
						<td style="font-weight:bold;">'.
							$correctperc.' % 
						</td>
						<td>
							<span class="ustat_correct before_correct">'.$correct_count.'</span>
						</td>
						<td>
							<span class="ustat_incorrect before_incorrect">'.$wrong_count.'</span> 
						</td>
					</tr> 
				';
				
				$bytopictable .= '
					</table>
				</div> <!-- bytopictable -->
				';
				
				
				$qa_content['custom'] .= $bytopictable;
				
				$qa_content['custom'] .= $questiontable;

				// catch elapsedcount = 0;
				if($elapsedcount==0)
				{
					$elapsedcount = 1;
				}
				$qa_content['custom'] .= '
					<div class="totalrating">
						<p>
							'.qa_lang('quizx_lang/totalrating').': 
							<span class="ustat_correct before_correct">'.$ucorrectcnt.'</span> 
							<span class="ustat_incorrect before_incorrect">'.$uincorrectcnt.'</span>
						</p>
						<p>
							'.$ucorrectcnt.' '.qa_lang('quizx_lang/xfromy').' '.($ucorrectcnt+$uincorrectcnt).' '.qa_lang('quizx_lang/quansweredcorrect').'
						</p> 
						<p>
							'.qa_lang('quizx_lang/playtime').': '.$elapsedaverage.' s
						</p>
						<p>
							'.qa_lang('quizx_lang/average').': '.str_replace(',0', '', number_format((float)$elapsedaverage/$elapsedcount, 1, ',', '')).' '.qa_lang('quizx_lang/seconds_abbr').' '.qa_lang('quizx_lang/perquestion').'
						</p>
						<p>
							<span class="ustat_table_rating">Gesamt: '.$uratingtotal.' % '.qa_lang('quizx_lang/correct').'</span>
						</p>
					</div> <!-- totalrating -->
					<div style="clear:both;"></div>
				';

				// assign to content
				$qa_content['custom'] .= $lastquestions;
				

				
				// make newest users list bigger on page
				$qa_content['custom'] .= '
				<style type="text/css">
					.qa-sidepanel {
						display:none;
					}
					.qa-main {
						width:100%;
					}
					.td-correct {
						background:#CFC;
					}
					.td-incorrect {
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
						padding:4px 4px 4px 10px;
						border:1px solid #CCC;
						background:#FFD;
						font-weight:normal;
					}
					.qa-main table tr td:nth-child(1) {
						text-align:left !important;
					}
					.qa-main td {
						border:1px solid #CCC;
						padding:8px 10px;
						line-height:110%;
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
					
					.totalrating {
						float:right;
						height:180px;
						margin:20px 0;
					}
					.trtotalscore {
						background:#FF8;
					}
					.ustat_table_rating {
						font-size:20px;
						color:#005;
					}
					.ratingtable {
						font-size:15px;
					}
					.ratingtable tr td:nth-child(4) {
						text-align:right;
						padding:0 5px 0 0;
					}
					.ratingtable tr td:nth-child(1) {
						width:15%;
					}
					.ratingtable tr td:nth-child(2) {
						width:11%;
					}
					.ratingtable tr td:nth-child(3) {
						width:55%;
					}
					.ratingtable tr td:nth-child(4) {
						width:6%;
					}
					.ratingtable tr td:nth-child(5) {
						width:12%;
					}
					.topicratingtable {
						font-size:15px;
						width:100%;
						max-width:480px;						
					}
					.topicratingtable tr td:nth-child(2) {
						text-align:right;
					}
				</style>';
			} // end gameplays>0
			else
			{
				$qa_content['custom'] = qa_lang('quizx_lang/notplayedyet');
			}
			
			
			return $qa_content;
		}
		
	}; // END quizx_page_userstats
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
