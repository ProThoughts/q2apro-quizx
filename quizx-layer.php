<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class qa_html_theme_layer extends qa_html_theme_base 
	{

		private $quizx_version = '0.013';

		private $isbyuser = false;
		private $cookieid = '';
		private $qstatus = '';
		private $iseditor = false;
		private $gamemode = false;

		// first called method
		function initialize() 
		{ 
		}
		
		public function doctype()
		{
			$userid = qa_get_logged_in_userid();
			$this->cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();
			qa_html_theme_base::doctype();
		}
		
		function head_script()
		{

			qa_html_theme_base::head_script();

			// check for admin who is allowed to edit and submit open questions too
			$this->iseditor = qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN;

			// quiz page with spans and not td
			if($this->request=='start') 
			{
				$this->ranking_block_layout = true;
			}

			// gobal scripts
			$this->output('<script type="text/javascript" src="'.QA_HTML_THEME_LAYER_URLTOROOT.'script.js?'.$this->quizx_version.'"></script>');
			$this->output('<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'styles.css?'.$this->quizx_version.'">');

			// change navigation: unanswered becomes Test-starten button
			$this->content['navigation']['main']['unanswered']['url'] = qa_path('start');
			$this->content['navigation']['main']['unanswered']['label'] = qa_lang('quizx_lang/starttest');
			
			// change navigation: users becomes highscores
			$this->content['navigation']['main']['user']['url'] = qa_path('highscores');
			$this->content['navigation']['main']['user']['label'] = qa_lang('quizx_lang/highscores');

			// change order, bring highscores to front (2nd element)
			/*$helparr = $this->content['navigation']['main']['unanswered'];
			unset($this->content['navigation']['main']['unanswered']);
			$this->content['navigation']['main']['unanswered'] = $helparr;
			*/
			
			// if admin, add navigation item for q-moderate - by changing the TAG nav item
			if($this->iseditor)
			{
				$qcheck_count = qa_db_read_one_value(
									qa_db_query_sub('SELECT COUNT(questionid) FROM `^quizx_moderate`
										WHERE status = "1"'),
										true);
				if(is_null($qcheck_count)) 
				{
					$qcheck_count = 0;
				}
				// $this->content['navigation']['main']['tag']['url'] = qa_path('desk');
				// $this->content['navigation']['main']['tag']['label'] = 'Redaktion <span class="qcheck_count">'.$qcheck_count.'</span>';
				$this->content['navigation']['main']['redaktion'] = array(
					'url' => qa_path('desk'),
					'label' => qa_lang('quizx_lang/editordesk').' <span class="qcheck_count">'.$qcheck_count.'</span>'
				);
			}
			
			// we do not need the tags in menu as these can be find in /quiz/start/ (button exists)
			// new: can be disabled in admin/pages
			/*else {
				unset($this->content['navigation']['main']['tag']);
			}*/
			
			// change logo link from '<a href="./" class="qa-logo-link">QuizX</a>' to:
			// $this->content['logo'] = '<a href="'.qa_path('start').'" class="qa-logo-link">QuizX</a>';


			// optional: remove feedback link from footer
			unset($this->content['navigation']['footer']['feedback']);

			// user nav
			// remove "meine updates"
			unset($this->content['navigation']['user']['updates']);
			
			// add "meine Statistik"
			// add "meine Testfragen"
			// change "Hallo username" to "Mein Profil"
			if(isset($this->content['loggedin']))
			{
				$this->content['loggedin']['prefix'] = ''; // delete "Hello"
				$this->content['loggedin']['data'] = '<a href="'.qa_path('user/'.qa_get_logged_in_handle()).'" class="qa-user-link">'.qa_lang('quizx_lang/profile').'</a>';
			}

			// changes of ask page
			if($this->template=='ask') 
			{
				// remove email notify from ask page
				unset($this->content['form']['fields']['notify']);
			}

			// remove "alle antworten" from user pages
			unset($this->content['navigation']['sub']['answers']);
			// remove "letzte aktivitäten" from user pages
			unset($this->content['navigation']['sub']['activity']);
			
			// questions list
			if($this->template=='questions')
			{
				// remove "meiste antworten" from subnav
				unset($this->content['navigation']['sub']['answers']);
			}
			
			if($this->template=='users' || $this->request=='highscores')
			{
				// add "Fleißigste Testersteller" to subnav
				$this->content['navigation']['sub']['users$'] = array(
					'url' => './users', 
					'label' => qa_lang('main/highest_users'),
					'selected' => ($this->template=='users')
				);
			
				// add "Beste Spieler" to subnav
				$this->content['navigation']['sub']['highscores'] = array(
					'url' => './highscores', 
					'label' => qa_lang('quizx_lang/bestplayers'),
					'selected' => ($this->request=='highscores') // false
				);
				
				// change order, bring highscores to front
				$helparr = $this->content['navigation']['sub']['users$'];
				unset($this->content['navigation']['sub']['users$']);
				array_push($this->content['navigation']['sub'], $helparr);
			}
			
			// edit state, remove 'edit silent' option
			if($this->template=='question' && strpos(qa_get_state(),'edit')!==false)
			{
				unset($this->content['form_q_edit']['fields']['silent']);
				// remove email notify from q and a
				unset($this->content['form_q_edit']['fields']['notify']);
				unset($this->content['a_form']['fields']['silent']);
				// and convert to comment options (edit answer)
				unset( $this->content['a_form']['fields']['tocomment'] );
				
				// count answers to question
				$answercount = count($this->content['a_list']['as']);
				// go over all comments to remove the edit silent notices
				for($i=0;$i<$answercount;$i++)
				{
					if(isset($this->content['a_list']['as'][$i]['c_form']))
					{
						unset($this->content['a_list']['as'][$i]['c_form']['fields']['silent']);
					}
				}
			}

			// QUESTION page
			if($this->template=='question' && isset($this->content['q_view'])) 
			{
				// check if post is by user
				$this->isbyuser = qa_post_is_by_user($this->content['q_view']['raw'], qa_get_logged_in_userid(), qa_cookie_get());
				// get status of question (0-open, 1-editdone, 2-published)
				$this->qstatus = quizx_check_q_status($this->content['q_view']['raw']['postid']);
				// get questionid
				$questionid = $this->content['q_view']['raw']['postid'];
				// get userid of question owner
				$userid = qa_get_logged_in_userid();
				
				// count answers to question
				$answercount = count($this->content['a_list']['as']);
				
				// remove favorite star
				unset($this->content['favorite']);

				// remove line "inform per email"
				if(isset($this->content['a_form']['fields']['notify'])) 
				{
					unset($this->content['a_form']['fields']['notify']);
				}

				// ALWAYS remove buttons comment, hide, close, follow from QUESTION
				unset($this->content['q_view']['form']['buttons']['flag']);
				unset($this->content['q_view']['form']['buttons']['comment']);
				unset($this->content['q_view']['form']['buttons']['close']);
				// admin can hide all questions in open and editdone mode if needed
				if(qa_get_logged_in_level()<QA_USER_LEVEL_ADMIN || $this->qstatus=='published') 
				{
					unset($this->content['q_view']['form']['buttons']['hide']);
				}

				// hide all comments to question
				/*if(isset($this->content['q_view']['c_list'])) {
					// go over all
					var_dump($this->content['q_view']['c_list']['cs']);
				}*/
				
				// go over all comments to answers to remove buttons (comments itself are hidden by CSS)
				/* unset not working ...
				for($i=0;$i<$answercount;$i++) {
					// get all comments to this answer
					$commentcount = count($this->content['a_list']['as'][$i]['c_list']);
					if($commentcount>0) {
						$allcomments = $this->content['a_list']['as'][$i]['c_list']['cs'];
						foreach($allcomments as $c) {
							// remove buttons
							unset($c['form']['buttons']);
						}
					}
				}
				*/
				
				// prevent more than 1 comment per answer, i.e. remove comment button from answer
				for($i=0;$i<$answercount;$i++) 
				{
					// get all comments to this answer
					$commentcount = count($this->content['a_list']['as'][$i]['c_list']['cs']);
					if($commentcount>0)
					{
						unset($this->content['a_list']['as'][$i]['form']['buttons']['comment']);
					}
				}
				
				// if post is by user himself and not published (CREATE-EDIT MODE)
				if($this->qstatus!='published' && ($this->iseditor || $this->isbyuser))
				{
					// remove notice who posted
					// unset($this->content['q_view']['who']);

					// display notice if no text in content - nope, could be only img tag
					// if( trim(strip_tags($this->content['q_view']['raw']['content'])) == 0) { ... }

					// jquery
					$this->output("
					<script type=\"text/javascript\">
					
						$(document).ready(function()
						{
							// memo: a-un/select-buttons get ajax loaded 1.7+prior
							// if correct answer selected, show send button
							$(document).on('click', '.qa-a-select-button', function() {
								// no unselect button in case another answer is chosen as correct (would reload the page though): $('.qa-a-unselect-button').length==0 &&
								// inform btn is displayed
								if($('#quizx_inform').is(':visible')) {
									$('#quizx_inform').hide();
									$('.qa-form-light-button-send').show();
								}
								
								$('.tipsy').remove();
							});
							
							// if correct answer is unselected, show inform button
							$(document).on('click', '.qa-a-unselect-button', function() {
								// no unselect button and inform btn displayed
								if($('.qa-form-light-button-send').is(':visible')) {
									$('#quizx_inform').show();
									$('.qa-form-light-button-send').hide();
								}
								$('.tipsy').remove();
							});

							// question with answers is released by user (goes to Redaktion queue)
							$('.qa-form-light-button-send').click( function(e) {
								e.preventDefault();
								// send qid to server
								var qid = $(this).attr('data-original');
								console.log('sending: '+qid);
								$.ajax({
									 type: 'POST',
									 url: '".qa_path('ajaxhandler')."',
									 data: {questionid:qid},
									 cache: false,
									 success: function(data) {
										console.log('received: '+data);
										$('.qa-waiting').hide();
										$('.qa-form-light-button-send, .qa-form-light-button-answer, .qa-part-a-list, qa-part-a-form, .qa-form-light-button-edit').hide();
										$('#quizx_inform').show();
										$('#quizx_inform').attr('value', '".qa_lang('quizx_lang/thxforquizquestion')."');
										$('#quizx_inform').attr('title', '');
										// insert link to insert next question
										$('#quizx_inform').after('<a href=\"".qa_path('ask')."\" class=\"qa-form-wide-button quizx-ask-new-button\">".qa_lang('quizx_lang/createanotherquestion')."</a>');
										// hide q-info field
										$('.qa-error').hide();
									 }
								});
							}); // end click
							
							// if we have a selected answer and the user clicks the solution comment button
							// not always working as page gets loaded completely anew
							$('.qa-a-item-buttons .qa-form-light-button-comment').click( function() {
								// display answer in full size for comment-editor to be fully visible
								$(this).closest('.qa-a-list-item').css('width', '100%');
							});
							
							// workaround check URL for state=comment or state=edit
							if(window.location.href.indexOf('state=comment') > -1 || window.location.href.indexOf('state=edit') > -1 ) {
								// find visible c_form that holds the editor and display parent (answer field) in full size for comment-editor to be fully visible
								$('.qa-c-form:visible').closest('.qa-a-list-item').css('width', '100%');
							}
							
							if($('.quizx_send_trigger').length>0) {
								// hide einreichen button on page load (.hide is failing since we have two class attributes in HTML)
								// if best answer not yet selected
								if( $('.qa-a-unselect-button').length==0) {
									$('.quizx_send_trigger').hide();									
								}
								
								// move duplicate send button to end of page
								var elem = $('.quizx_send_trigger').detach();
								$('.qa-main').append(elem);
							}
							
							// only show bottom-page-button to submit question if solution comment has been given
							$('.qa-form-tall-button-comment').click( function() {
								$('.quizx_send_trigger').show();
							});

							// solve problem that after submitting the answer per answer-button the HTML is thrown back from the server, revealing a Lösung-eingeben-Button
							$('.qa-form-tall-button-answer').click( function() { 
								$(document).ajaxSuccess(function() {
									// hide ajax inserted button
									$('.qa-a-list-item:first-child .qa-a-item-main .qa-a-item-buttons .qa-form-light-button-comment').hide();
								});
							});
							
							// shortcuts to be quicker
							$(window).keydown(function(e) {
								// alt + a: Add Answer
								if(e.altKey && e.which==65 && $('#q_doanswer').is(':visible')) { 
									$('#q_doanswer').trigger('click');
									e.preventDefault(); 
								}
								// alt + w: Submit Question and its answers
								else if(e.altKey && e.which==87 && $('#quizx_send').is(':visible')) { 
									$('#quizx_send').trigger('click');
									e.preventDefault(); 
								}
								// alt + w: New Question (after question was submitted)
								else if(e.altKey && e.which==87 && $('.quizx-ask-new-button').is(':visible')) { 
									// is hardcoded link
									$('.quizx-ask-new-button')[0].click();
									// e.preventDefault(); 
								}
							}); // end shortcuts
							
							// click on red button 'Bitte noch 2 Antworten eingeben' triggers answer button
							$('#needmoreanswers').click( function(e) { 
								e.preventDefault();
								$('#q_doanswer').trigger('click');
							});
							
							// click on message submits question to redaktion queue
							$('#msgsubmitqu').click( function(e) { 
								e.preventDefault();
								$('#quizx_send').trigger('click');
							});
							
						}); // end ready
					</script>
					");


					// do not show points and votes on left of question
					$this->output('
					<style type="text/css">
						.qa-q-view-stats .qa-voting {
							display:none;
						}
						.qa-q-view-main {
							width:93%;
							margin:0;
						}
						/* needed to hide ajax returned buttons after best answer select */
						/*.qa-form-light-button-comment,*/
						.qa-form-light-button-hide,
						.qa-form-light-button-close {
							display:none;
						}
						/* reposition qu-by */
						.qa-q-view-meta {
							position: absolute;
							right: 10px;
							top: 10px;
						}
						
						/* edit mode: show edit button to edit the solution comment */
						.qa-a-item-c-list .qa-c-item-buttons .qa-form-light-button-edit {
							display:inline-block;
						}
						.qa-a-item-c-list .qa-c-item-footer {
							display:block;
						}
					</style>
					');
					
					// special: admin can hide and delete questions
					if(qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN)
					{
						$this->output('
						<style type="text/css">
							.qa-a-list-item .qa-form-light-button-hide,
							.qa-form-light-button .qa-form-light-button-hide, 
							.qa-form-light-button-hide, 
							.qa-a-item-c-list .qa-c-item-buttons .qa-form-light-button-hide 
							{
								display:inline-block;
							}
							.qa-a-item-content .entry-content {
								min-height:60px;
							}
						</style>');
					}
					
					// REMOVE similar questions from question page if in creation mode
					// in case the widget is repositioned, it is saver to go over all widgets
					foreach($this->content['widgets'] as $widget) 
					{
						$widgetkey = key($this->content['widgets']);
						$wcount = count($widget);
						if($wcount>0) 
						{
							// $widget holds position like top, bottom, high
							// $wd is array index of each widget etc.
							foreach($widget as $wd) 
							{
								for($i=0;$i<count($wd);$i++) 
								{
									if(get_class($wd[$i]) == 'qa_related_qs') 
									{
										// var_dump($this->content['widgets']['main']['bottom'][1]);
										// var_dump($this->content['widgets'][$widgetkey][key($widget)][$i]);
										unset($this->content['widgets'][$widgetkey][key($widget)][$i]);
									}
								};
							}
						}
					} // end foreach
						
				} // end !published and (byuser or editor)

				
				// remove buttons comment, hide from ANSWER(S)
				/* NEW: hide by CSS, display comment field after selected answer has been chosen! */
				/*for($i=0;$i<$answercount;$i++) {
					if(isset($this->content['a_list']['as'][$i]['form']['buttons']['comment'])) {
						unset($this->content['a_list']['as'][$i]['form']['buttons']['comment']);
					}
					if(isset($this->content['a_list']['as'][$i]['form']['buttons']['hide'])) {
						unset($this->content['a_list']['as'][$i]['form']['buttons']['hide']);
					}
					if(isset($this->content['a_list']['as'][$i]['form']['buttons']['flag'])) {
						unset($this->content['a_list']['as'][$i]['form']['buttons']['flag']);
					}
				}*/
				
				
				// get id of selected answer, if any
				$correctselectid = null; 
				if(isset($this->content['q_view']['raw']['selchildid'])) 
				{
					$correctselectid = $this->content['q_view']['raw']['selchildid'];
				}
				
				for($i=0;$i<$answercount;$i++) 
				{
					// answer fields
					if(isset($this->content['a_list']['as'][$i]['form'])) 
					{
						// hide comment button by inline-css, it will be displayed for selected anser by Jquery show()
						// if answer is already selected, do not hide the comment button!
						$answer_postid = $this->content['a_list']['as'][$i]['raw']['postid'];
						if((int)$correctselectid != (int)$answer_postid && isset($this->content['a_list']['as'][$i]['form']['buttons']['comment']['tags']))
						{
							$this->content['a_list']['as'][$i]['form']['buttons']['comment']['tags'] .= ' style="display:none;" ';
						}
					}
					
					// care for comment fields below answers
					if(isset($this->content['a_list']['as'][$i]['c_form'])) 
					{
						// remove email notify field from comment below answer
						unset($this->content['a_list']['as'][$i]['c_form']['fields']['notify']);
					}
				}
			
			
				// if question owner and at least 2 answers, and question not published yet
				if($this->qstatus=='open' && ($this->isbyuser || $this->iseditor)) 
				{
					$minanswers = 2;
					$missinganswers = $minanswers-$answercount;
					// need at least 2 answers
					if($answercount<$minanswers) 
					{
						$missingstring = ($missinganswers==1 ? qa_lang('quizx_lang/oneanswer') : $missinganswers.' '.qa_lang('quizx_lang/answers'));
						$this->content['q_view']['form']['buttons']['quizx_notice'] = array(
							'tags' => 'onclick="javascript:void(0);" class="qa-form-light-button qa-form-light-button-inform" id="needmoreanswers"', // disabled="disabled" 
							'label' => str_replace('~acount~', $missingstring, qa_lang('quizx_lang/addmoreanswers')),
							'popup' => qa_lang('quizx_lang/needtwoanswers')
						);
					}
					else 
					{
						// if best answer has been chosen
						$correctselected = isset($this->content['q_view']['raw']['selchildid']);
						
						// and if the question status still open (not editdone)
						$this->content['q_view']['form']['buttons']['quizx_send'] = array(
							'tags' => 'data-original="'.$questionid.'" id="quizx_send" name="quizx_send" onclick="qa_show_waiting_after(this, false);" class="qa-form-light-button qa-form-light-button-send'.($correctselected&&$this->qstatus=='open' ? '' : ' hide').'"',
							'label' => qa_lang('quizx_lang/submitquizquestion'),
							'popup' => qa_lang('quizx_lang/sendqutoeditors')
						);
						// duplicate button in end of answer list (usability, logical for user to find)
						// memo hide class not working on fixed element, so use inline display:none;
						// only show in bottom of page *after* solution comment has been posted (can be detected by URL show=, dont show if edit state)
						/*
						$solutioncomment_exists = false;
						for($i=0;$i<$answercount;$i++) 
						{
							$solutioncomment_exists = $solutioncomment_exists || !empty($this->content['a_list']['as'][$i]['c_list']['cs']);
						}
						if($solutioncomment_exists && strpos(qa_get_state(),'edit')===false && strpos(qa_get_state(),'comment')===false) 
						{
						*/
						if(strpos(qa_get_state(),'edit')===false && strpos(qa_get_state(),'comment')===false)
						{
							$this->content['q_view']['form']['buttons']['quizx_send_2'] = array(
								'tags' => 'data-original="'.$questionid.'" class="quizx_send_trigger qa-form-light-button qa-form-light-button-send'.($correctselected&&$this->qstatus=='open' ? '' : ' hide').'"',
								'label' => qa_lang('quizx_lang/sendqutoeditors'),
								'popup' => ''
							);
						}
						
						// ask user to choose best answer
						$this->content['q_view']['form']['buttons']['quizx_notice'] = array(
							'tags' => 'id="quizx_inform" onclick="javascript:void(0);" disabled="disabled" class="qa-form-light-button qa-form-light-button-inform'.($correctselected ? ' hide' : '').'"',
							'label' => qa_lang('quizx_lang/selectcorrectbelow'),
							'popup' => qa_lang('quizx_lang/selectcorrecttip')
						);

						// label answer button with weitere
						if(isset($this->content['q_view']['form']['buttons']['answer'])) 
						{
							$this->content['q_view']['form']['buttons']['answer']['label'] = qa_lang('quizx_lang/addanswer');							
						}
					}
					
					// sending answer NEVER by ajax as this does *not* display the best answer selects and the next answer button!
					if(isset($this->content['a_form']['buttons']['answer']['tags'])) 
					{
						// clear onclick event
						$this->content['a_form']['buttons']['answer']['tags'] = '';
					} 

				} // end open and (isbyuser or admin)
				

				// redaktion can accept the question at any time
				if($this->qstatus=='editdone' && qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN) 
				{
					$this->content['q_view']['form']['buttons']['quizx_unlock'] = array(
						'tags' => 'id="quizx_unlock" data-original="'.$questionid.'" name="quizx_unlock" onclick="qa_show_waiting_after(this, false);" class="qa-form-light-button qa-form-light-button-unlock"',
						'label' => qa_lang('quizx_lang/releasequizqu'),
						'popup' => qa_lang('quizx_lang/releasequizqu_tip')
					);
					// label answer button with weitere
					if(isset($this->content['q_view']['form']['buttons']['answer'])) 
					{
						$this->content['q_view']['form']['buttons']['answer']['label'] = qa_lang('quizx_lang/addanswer');
					}

					// jquery
					$this->output("
					<script type=\"text/javascript\">
						$(document).ready(function()
						{
							$('#quizx_unlock').click( function(e) 
							{
								e.preventDefault();
								// send qid to server
								var qid = $(this).attr('data-original');
								console.log('sending: '+qid);
								$.ajax({
									 type: 'POST',
									 url: '".qa_path('ajaxhandler')."',
									 data: { q_unlockid:qid },
									 cache: false,
									 success: function(data) 
									 {
										console.log('received: '+data);
										$('.qa-waiting, .qa-error').hide();
										$('#quizx_unlock, .qa-form-light-button-answer, .qa-part-a-list, qa-part-a-form, .qa-form-light-button-edit').hide();

										// ajax returns a postid of another unmoderated question, insert as link
										if(data!='') {
											$('#quizx_unlock').after('<a href=\"".qa_opt('site_url')."'+data+'\" class=\"qa-form-wide-button quizx-ask-new-button\" style=\"display:block;margin-top:50px;text-align:center;\">".qa_lang('quizx_lang/moderatemorequ')."</a>');
										}
										else {
											$('#quizx_unlock').after('<a class=\"qa-form-wide-button quizx-ask-new-button\" style=\"background:#CDE;display:block;margin-top:50px;text-align:center;color:#333 !important\">".qa_lang('quizx_lang/nomoreunmoderated')."</a>');
										}
									 }
								}); // end ajax
							}); // end click

						}); // end ready
					</script>
					");
				} // end redaktion

				// game mode
				if($this->qstatus=='published')
				{
				
					/* QUIZ GAME PLAY */
					$this->gamemode=true;
					
					// if player then remove all forms and buttons
					if(!$this->iseditor) 
					{
					
						// remove all forms from question 
						if(isset($this->content['q_view']['form']))
						{
							unset($this->content['q_view']['form']);
						}
						// remove all comments from question
						if(isset($this->content['q_view']['c_list']))
						{
							unset($this->content['q_view']['c_list']);
							// var_dump($this->content);
						}
						
						// remove all answer forms
						if(isset($this->content['a_form']))
						{
							unset($this->content['a_form']);
						}
						
						if(isset($this->content['q_view']['buttons_form_hidden'])){
							unset($this->content['q_view']['buttons_form_hidden']);
						}
						
						
						// go over all comments to remove the forms
						for($i=0;$i<$answercount;$i++)
						{
							if(isset($this->content['a_list']['as'][$i]['form']))
							{
								unset($this->content['a_list']['as'][$i]['form']);
								unset($this->content['a_list']['as'][$i]['buttons_form_hidden']);

								// remove edit button
								// unset($content['a_list']['as'][$i]['form']['buttons']['edit']);
								// remove comment button (solution comment)
								// unset($content['a_list']['as'][$i]['form']['buttons']['comment']);
							}
							
							// to remove the comment forms from answers
							if(isset($this->content['a_list']['as'][$i]['c_form']))
							{
								unset($this->content['a_list']['as'][$i]['c_form']);
							}
							// remove all comment on comment forms
							if(!empty($this->content['a_list']['as'][$i]['c_list']['cs']))
							{
								$cid = key($this->content['a_list']['as'][$i]['c_list']['cs']);
								if(isset($this->content['a_list']['as'][$i]['c_list']['cs'][$cid]['form']))
								{
									unset($this->content['a_list']['as'][$i]['c_list']['cs'][$cid]['form']);									
								}
								// unset($this->content['a_form']);
							}
							
							// remove hidden input codes from voting buttons of A
							if(isset($this->content['a_list']['as'][$i]['voting_form_hidden']))
							{
								unset($this->content['a_list']['as'][$i]['voting_form_hidden']);
							}
							
							// remove hide and edit button from solution comment (not working)
							// var_dump($content['a_list']['as'][$i]['c_list']['cs']);
							/*$commentcount = count($content['a_list']['as'][$i]['c_list']['cs']);
							if($commentcount>0)
							{
								$allcomments = $content['a_list']['as'][$i]['c_list']['cs'];
								foreach($allcomments as $c_id => $c_content)
								{ // $c is comment postid, $cid is content
									// remove form buttons
									unset($c_content['form']['buttons']);
									unset($c_content['form']);
								}
							}*/
							// remove answer button from q (only admin is allowed to add answers after publish)
							// ($content['q_view']['form']['buttons']['answer']);
							// unset($content['q_view']['form']['buttons']['answer']);
							// remove edit buttons from question/answers/comments (only admin is allowed to do so)
							// unset($content['q_view']['form']['buttons']['edit']);

						} // end iterate over all answers
						
					} // end is !editor
					
					// if editor/admin 
					if($this->iseditor)
					{
						$this->output('
							<style type="text/css">
							/* show buttons of comments (answer buttons shown by default) */
							.qa-a-item-c-list .qa-c-item-footer,
							.qa-a-item-c-list .qa-c-item-buttons .qa-form-light-button-edit, 
							.qa-a-item-c-list .qa-c-item-buttons .qa-form-light-button-hide
							{
								display:inline-block !important;
							}
							</style>
						');
					}
					
				} // end IS PUBLISHED (game mode)
				
			} // end question page

		} // end head_script

		
		
		// QUIZ CHOICE page
		// tweak: game page not with 1 × tag (like tags page) but with "1 Frage" / "2 Fragen"
		/*
		public function ranking_item($item, $class, $spacer=false) // $spacer is deprecated
		{
			if($this->request=='start')
			{
				$qu_tag = strip_tags($item['label']);
				// check database how many published question we have
				// GET all published questions of this tag
				$qu_pub_count = qa_db_read_one_value(
								qa_db_query_sub('SELECT COUNT(a.postid), a.tags, a.postid, b.questionid, b.status
													FROM `^posts` a, `^quizx_moderate` b
													WHERE a.tags = #
													AND a.postid = b.questionid
													AND b.status = "2"
													',
													$qu_tag), true);
				// replace $item['count']
				if(isset($qu_pub_count))
				{
					$item['count'] = $qu_pub_count;
				}
				// if 0 hits, do not output
				if($qu_pub_count==0)
				{
					return;
				}
				if (isset($item['avatar']))
					$this->avatar($item, $class);

				$this->ranking_label($item, $class);

				if (isset($item['score']))
					$this->ranking_score($item, $class);

				if (isset($item['count']))
					$this->ranking_count($item, $class);

			}
			else
			{
				qa_html_theme_base::ranking_item($item, $class, $spacer=false);
			}
		}
		*/
		
		// tweak to make e.g. lineare-funktionen to Lineare Funktionen
		/*
		public function ranking_label($item, $class)
		{
			if($this->request=='start')
			{
				$item_lab_replace = strip_tags($item['label']);
				if(strpos($item_lab_replace, '-') !== false){
					$item_lab_cap = str_replace('-', ' ', $item_lab_replace);
					// only replace within tag, otherwise href would be affected
					$item['label'] = str_replace('>'.$item_lab_replace.'<', '>'.$item_lab_cap.'<', $item['label']);
				}
				$this->ranking_cell($item['label'], $class.'-label');
			}
			else
			{
				qa_html_theme_base::ranking_label($item, $class);
			}
		}*/

		// tweak to capitalize Thema (tag) e.g. lineare-funktionen to Lineare Funktionen
		public function title()
		{
			// needed to set question.rss feed for SnowFlat theme (rss icon)
			if(!empty($this->content['feed']['url']))
			{
				if ($this->template=='qa' || $this->template=='questions' || $this->template=='unanswered')
				{
					$this->content['feed']['url'] = qa_path('feed').'/questions.rss';
				}
			}

			if($this->template=='tag')
			{
				// capitalize first letter after dash or space
				// $label_transform = preg_replace("/(\w+)/e","ucfirst('\\1')", $this->content['title']);
				$label_transform = implode('-', array_map('ucfirst', explode('-', $this->content['title'])));

				// remove hyphens
				$label_transform = str_replace('-', ' ', $label_transform);
				
				// add 
				$this->content['title'] = qa_lang('quizx_lang/quiz').': '.$label_transform;
			}
			
			qa_html_theme_base::title();
		}


		// display notice if owner
		public function page_title_error()
		{
			if($this->template=='qa')
			{
				$this->output('
					<div class="startpage-intro">
						<p style="font-size:20px;margin-top:30px;">
							'.qa_lang('quizx_lang/welcomemsg').' '.qa_opt('site_title').'!
						</p>
						<p>
							'.
							strtr( qa_lang('quizx_lang/welcometext1'), array( 
								'^1' => '<a href="'.qa_path('start').'">',
								'^2' => '</a>'
							)).
							' '.qa_lang('quizx_lang/welcometext2').' '.
							strtr( qa_lang('quizx_lang/welcometext3'), array( 
								'^1' => '<a href="'.qa_path('highscores').'">',
								'^2' => '</a>'
							)).
							' '.
							strtr( qa_lang('quizx_lang/welcometext4'), array( 
								'^1' => '<a href="'.qa_path('ask').'">',
								'^2' => '</a>'
							)).
							' '.qa_lang('quizx_lang/welcometext5').'
						</p>
					</div>
					'
				);
				
				// extra buttons on startpage
				$this->output('
					<div class="startpage_playbtns">
						<a class="qa-form-wide-button qx_red" href="'.qa_path('start').'">'.qa_lang('quizx_lang/startquiz').'</a>
						<a class="qa-form-wide-button qx_green" href="'.qa_path('highscores').'" title="'.qa_lang('quizx_lang/showbestplayers').'">'.qa_lang('quizx_lang/highscores').'</a>
						<a class="qa-form-wide-button qx_orange" href="'.qa_path('ask').'">'.qa_lang('quizx_lang/enterquizquestion').'</a>
					</div>
					'
				);
				
				// count all available questions
				/*$questioncount = qa_db_read_one_value( 
									qa_db_query_sub('SELECT COUNT(questionid) FROM `^quizx_moderate` 
														WHERE `status` = "2"
														'),
														true
													);*/
				$questioncount = qa_opt('cache_qcount');
				
				$this->output('<div class="quizx-stats-totalq">
					<p>
						'.
						strtr( qa_lang('quizx_lang/availablequizqu'), array( 
							'^1' => '<a href="'.qa_path('start').'"> '.$questioncount.' ',
							'^2' => '</a>'
						)).
						'
					</p>
				</div>');

			}
			
			if($this->template=='question')
			{
				
				// game mode, i.e. question published
				if($this->qstatus=='published')
				{
					$userid = qa_get_logged_in_userid();
					$questionid = $this->content['q_view']['raw']['postid'];
					// check if user has answered this question already, output information (yellow BG***)
					if(isset($userid))
					{
						$gamepitch = qa_db_read_one_assoc(
											qa_db_query_sub('SELECT timestamp, correct FROM `^quizx_gameplay`
												WHERE questionid = # 
												AND userid = #
												ORDER BY timestamp DESC
												LIMIT 1', 
												$questionid, $userid),
												true
											);
						if(isset($gamepitch))
						{
							// get total stats
							$quser_stats = qa_db_read_one_assoc(
											qa_db_query_sub('SELECT SUM(correct=1) AS qcorrect, SUM(correct=0) AS qincorrect FROM `^quizx_gameplay`
												WHERE questionid = # 
												AND userid = #', 
												$questionid, $userid),
												true
											);
						}
						
						$gamepitch_today = qa_db_read_one_value(
											qa_db_query_sub('SELECT timestamp FROM `^quizx_gameplay`
												WHERE questionid = # 
												AND userid = #
												AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
												ORDER BY timestamp DESC
												LIMIT 1', 
												$questionid, $userid),
												true
											);
					}
					else
					{
						// anonymous - identify by cookie
						$ipaddress = qa_remote_ip_address();
	
						$gamepitch = qa_db_read_one_assoc(
											qa_db_query_sub('SELECT timestamp, correct FROM `^quizx_gameplay`
												WHERE questionid = # 
												AND cookieid = #
												ORDER BY timestamp DESC
												LIMIT 1', 
												$questionid, $this->cookieid),
												true
											);
						if(isset($gamepitch))
						{
							// get total stats
							$quser_stats = qa_db_read_one_assoc(
											qa_db_query_sub('SELECT SUM(correct=1) AS qcorrect, SUM(correct=0) AS qincorrect FROM `^quizx_gameplay`
												WHERE questionid = # 
												AND cookieid = #', 
												$questionid, $this->cookieid),
												true
											);
						}
						
						$gamepitch_today = qa_db_read_one_value(
											qa_db_query_sub('SELECT timestamp FROM `^quizx_gameplay`
												WHERE questionid = # 
												AND cookieid = #
												AND timestamp >= DATE_SUB(NOW(), INTERVAL 1 DAY)
												ORDER BY timestamp DESC
												LIMIT 1', 
												$questionid, $this->cookieid),
												true
											);
					}
					
					if(isset($gamepitch) && isset($quser_stats['qcorrect']))
					{
							$pitch_correct = $gamepitch['correct'];
							$qresult_label = qa_lang('quizx_lang/correct');
							$displayicon = 'before_correct';
							if(!$pitch_correct)
							{
								$qresult_label = qa_lang('quizx_lang/wrong');
								$displayicon = 'before_incorrect';
							}
							$pitch_time = qa_opt('db_time')-strtotime($gamepitch['timestamp']);
							
							$output = '';
							
							if(isset($gamepitch_today))
							{
								date_default_timezone_set(qa_opt('quizx_servertimezone'));
								
								$diffplayagain = time()-strtotime($gamepitch_today);
								$diffplayagain_hours = floor($diffplayagain/60/60);
								$diffplayagain_min = round(($diffplayagain - $diffplayagain_hours*60*60)/60);
								
								$waitingtime = qa_lang('quizx_lang/waitstilltime');
								$waitingtime = str_replace('~hours', (23-$diffplayagain_hours), $waitingtime);
								$waitingtime = str_replace('~mins', (60-$diffplayagain_min), $waitingtime);
								
								
								$output .= '
								<div class="quizx_infobox">
									<p class="pitchinfo_1 before_incorrect">
										'.qa_lang('quizx_lang/alreadyanswered').'
									</p>
									<p class="pitchinfo_2">
										'.$waitingtime.'
									</p>
									<div id="notimer"></div>
								</div>';
								// remove answer fields by jquery, php unset does not work, fields already set
								$output .= '
								<script type="text/javascript">
									$(document).ready(function(){
										$(".qa-part-a-list").remove();
									});
								</script>
								';
							}
							else 
							{
								$lastanswer = qa_lang('quizx_lang/already_lastanswer');
								$lastanswer = str_replace('~pitchtime', qa_lang_html_sub('main/x_ago', qa_html(qa_time_to_string($pitch_time))), $lastanswer);
								$lastanswer = str_replace('~result', $qresult_label, $lastanswer);
								
								$ustats = qa_lang('quizx_lang/already_userstats');
								$ustats = str_replace('~qcorrect', $quser_stats['qcorrect'], $ustats);
								$ustats = str_replace('~qincorrect', $quser_stats['qincorrect'], $ustats);
								
								$output = '
								<div class="quizx_infobox">
									<p class="pitchinfo_1">
										'.$lastanswer.'
									</p>
									<p class="pitchinfo_2">
										'.$ustats.'
									</p>
								</div>';
							}
							
							// js hack: bring quizx_infobox out of qa-main, one level higher into qa-main-wrapper to display it alone on one line
							$output .= '
							<script type="text/javascript">
								$(document).ready(function(){
									var elem = $(".quizx_infobox").detach();
									$(".qa-main-wrapper").prepend(elem);
								});
							</script>
							';
							
							$this->output($output);
					}
					
				} // end question published
				
			} // end question page
			
			// default call
			qa_html_theme_base::page_title_error();
			
			if($this->template=='users')
			{ //  && isset($this->content['q_view'])) {
				$this->output('
					<div class="users_infobox_top" style="margin:20px 0;">
						<p>
							'.qa_lang('quizx_lang/seeqcreators').'
						</p>
					</div>
				');
			}
		} // end page_title_error
		
		public function q_view_clear()
		{
			qa_html_theme_base::q_view_clear();
			
			// show user if question is his own
			if($this->isbyuser && $this->qstatus!='open')
			{
				$this->output('
					<p style="font-size:12px;color:#854;position:absolute;left:20px;top:5px;">
						'.qa_lang('quizx_lang/yourquizquestion').'
					</p>
				');
			}
		}

		// user is question creator - show his not-submitted questions to him
		public function header()
		{
			qa_html_theme_base::header();
			
			$userid = qa_get_logged_in_userid();
			$qinmod = qa_db_read_all_assoc(
							qa_db_query_sub('SELECT `questionid` FROM `^quizx_moderate`
												WHERE userid = #
												AND status = 0
											',
											$userid));
			if( count($qinmod)>0 && 
					($this->template=='questions' || $this->template=='activity' ||
						($this->template=='question' && $this->qstatus=='published')) )
			{
				
				$qlisting = '';
				$qlisting .= '<ul class="unsubmittedlist">';
				foreach($qinmod as $qu)
				{
					$qtitle = qa_db_read_one_value(
								qa_db_query_sub('SELECT `title` FROM `^posts`
													WHERE postid = #
													', $qu['questionid']), true);
					$qlisting .= '
					<li>
						<a href="'.qa_path($qu['questionid']).'">'.$qtitle.'</a>
					</li>';					
				}
				$qlisting .= '</ul>';
			
				$this->output('
				<div class="unsubmittedholder">
					<div class="unsubmitted">
						<p>
							'.qa_lang('quizx_lang/unsubmittedqu').'
						</p>
						'.$qlisting.'
					</div>
				</div> <!-- unsubmittedholder -->
				');				
			}
		}

		// QUESTION - Status Notices
		public function main_parts($content) 
		{
			if($this->template=='question')
			{
				$qaoutputdone = false;
				
				// count answers
				$answercount = count($content['a_list']['as']);
				
				// do not output question, answer etc. if in moderation
				if($this->qstatus=='open' && $this->isbyuser)
				{
					// show nothing, only output error
					if($answercount>=2)
					{
						// enough answers, best answer selected?
						if(isset($content['q_view']['raw']['selchildid'])) 
						{
							$msg = strtr( qa_lang('quizx_lang/submitquallowed'), array( 
									'^1' => '<a id="msgsubmitqu" href="#">',
									'^2' => '</a>'
								));
							$this->error($msg);
						}
						else 
						{
							$msg = strtr( qa_lang('quizx_lang/addmoreorselect'), array( 
									'^1' => '<a id="needmoreanswers" href="#">',
									'^2' => '</a>'
								));
							$this->error($msg);
						}
						// orange error message
						$this->output('
							<style type="text/css">
								.qa-error { background:#00750E; position:fixed; bottom:0; right:0; }
							</style>');
					}
					else
					{
						$this->error( qa_lang('quizx_lang/answersmissing') );
						// orange error message
						$this->output('
						<style type="text/css">
							.qa-error { background:#FF6C00; position:fixed; bottom:0; right:0; }
						</style>');
					}
					// default output question and answers
					qa_html_theme_base::main_parts($content);
					$qaoutputdone = true;
				}
				else if($this->qstatus=='editdone')
				{
					// show nothing, only output error
					$this->error( qa_lang('quizx_lang/quneedsunlock') );
					// orange error message
					$this->output('
						<style type="text/css">
							.qa-error { background:#FF6C00; margin-bottom:40px; }
						</style>');
				}
				// published question in game mode
				else if($this->qstatus=='published')
				{
					/* QUIZ GAME PLAY */
					$this->gamemode=true;
					
					// creater looks onto his own question (not allowed to play his own)
					// changed: can play his own now
					/*
					if($this->isbyuser)
					{
						$this->error('Diese Frage steht nun bereit.');
						// blue error message
						$this->output('<style type="text/css">.qa-error { background:#006CEE; }</style>');
					}
					*/

					// hide selected answer and prepare select answers, see a_list_items and a_list_item below
					$this->output("
						<script type=\"text/javascript\">
							// game mode
							$(document).ready(function()
							{
								// hide all comments from stage
								$('.qa-a-item-c-list').hide();
								
								$('.qa-a-select-button').click( function(e)
								{
									e.preventDefault();
									var qid = '".$content['q_view']['raw']['postid']."';
									var aid = $(this).attr('data-original');
									// console.log('sending: q='+qid+' a='+aid+' el='+elapsed);
									var gdata = {
										qid: qid,
										aid: aid,
										time: elapsed,
									};
									var senddata = JSON.stringify(gdata);
									// reference
									var clicked = $(this);
									// remove tipsy
									$('.tipsy').remove();
									$.ajax({
										 type: 'POST',
										 url: '".qa_path('ajaxhandler')."',
										 data: { gamedata:senddata },
										 dataType: 'json',
										 cache: false,
										 success: function(data) 
										 {
											console.log('server returned: '+data+' | correct='+ data['correct']+' next='+data['nextqid']+' correctanswer='+data['correctanswer']+' qleft='+data['qleft']);

											$('.qa-waiting, .qa-error').hide();
											// ajax returns if the answer has been chosen correctly (1) or not(0)
											// make all checkmarks red
											$('.qa-a-select-button').css('background-color', '#F00');
											// but color the correct one green
											$('.qa-a-select-button[data-original=\"'+data['correctanswer']+'\"]').css('background-color', '#27AE60');

											// show help if exists
											if( $('.qa-a-item-c-list .qa-c-list-item').length > 0) 
											{
												$('.qa-a-item-c-list .qa-c-list-item').prepend('<p class=\"solhelp-label\">".qa_lang('quizx_lang/solutionhelper').":</p>');
												$('.qa-a-item-c-list').show();												
											}
											
											// button to next question, or end of topic
											var elem_result = '';
											var elem_next = '<div class=\"quizx_resultr\">';
											// show result (answer correct or not)
											var correctcount = 0;
											var incorrectcount = 0;
											correctcount = parseInt( $('.ustat_scorecount .ustat_correct span').text(), 10);
											incorrectcount = parseInt( $('.ustat_scorecount .ustat_incorrect span').text(), 10);
											
											if(data['correct']==0) {
												elem_result += '<p class=\"qa-form-wide-button quizx-result-button qx_red\">".qa_lang('quizx_lang/wrongunfort')."</p>';
												incorrectcount++;
												$('.ustat_incorrect').css('color', '#F00');
											}
											else if(data['correct']==1) {
												elem_result += '<p class=\"qa-form-wide-button quizx-result-button qx_green\">".qa_lang('quizx_lang/correctfort')."</p>';
												correctcount++;
												$('.ustat_correct').css('color', '#0A0');
											}
											// update stats in widget
											$('.ustat_scorecount .ustat_incorrect span').text( incorrectcount );
											$('.ustat_scorecount .ustat_correct span').text( correctcount );
											// update knob circle with new percentage
											var percentcorrect_new = Math.round( 100*correctcount/(correctcount+incorrectcount) );
											$({value: 0}).animate({value: percentcorrect_new}, {
												duration: 1000,
												easing:'swing',
												step: function(){
													$('.usergrading-knob').val(this.value).trigger('change');
												}
											});											
											// $('.usergrading-knob').val(percentcorrect_new).trigger('change');
											
											if(data['qleft']<=0) 
											{
												// no more questions
												elem_next += '<p class=\"qa-form-wide-button quizx-info qx_gray\">".qa_lang('quizx_lang/allquestionsdone')."</p>';
												// *** add: x von y richtig
												// *** Statistik für dieses Thema aufrufen
												elem_next += '<a href=\"".qa_path('start')."\" class=\"qa-form-wide-button quizx-continue-button\">".qa_lang('quizx_lang/choosenewtopic')."</a>';
											}
											else {
												// next question of topic
												elem_next += '<a href=\"".qa_opt('site_url')."'+data['nextqid']+'\" class=\"qa-form-wide-button quizx-continue-button\">".str_replace('~qleft~', "'+data['qleft']+'", qa_lang('quizx_lang/nextquestion'))."</a>';
											}
											
											// replace button skip-question with new actions
											$('.quizx-skip-question').html(elem_next);
											
											// stop timer
											clearInterval(timerinterval);

											clicked.parent().after(elem_next);
											clicked.parent().after(elem_result);

											// disable all select buttons
											$('.qa-a-select-button').unbind('mouseenter mouseleave onclick onmouseover onmouseout');
											$('.qa-a-select-button').attr('disabled', 'disabled');
											$('.qa-a-select-button').removeAttr('title');
											$('.qa-a-select-button').css('cursor', 'default');
											
											// hide 'richtig' labels
											$('.selection_sublab').hide();
										 } // end success
									}); // end ajax

								}); // end qa-a-select-button.click
								
								// move tags to top, above question title when playing
								$('.qa-q-view-tags').detach().prependTo('.qa-main');
								$('.qa-q-view-tags').css('margin-top', '0px');
								$('.qa-q-view-tags:first-child .qa-tag-link').css('font-size', '13px');
								
								
								// TIMER
								if($('#notimer').length==0) {
									$('.qa-part-q-view').after('<div id=\"gametimer\" title=\"".qa_lang('quizx_lang/yourtime')."\">0 s</div>');
									$('#gametimer').tipsy( { gravity:'w', offset:10, html:true } );
									var start = new Date();
									var elapsed = 0;
									var timerinterval = setInterval(function() 
									{
										elapsed = Math.round((new Date()-start) / 1000);
										$('#gametimer').text(elapsed+' s');
										// console.log( Math.round((new Date()-start) / 1000) + ' s');
									}, 1000);
								}
								
							}); // end ready
						</script>
					");
					
					// question page (gamemode): remove user-meta-who-when-what from content-top-position
					$content['q_view']['meta_order'] = '';

					// output content
					qa_html_theme_base::main_parts($content);
					$qaoutputdone = true;
				} // end status published
				else 
				{
					$qcreator = $content['q_view']['raw']['handle'];
					if(is_null($qcreator))
					{
						$qcreator = 'Gast';
					}
					$this->error(qa_lang('quizx_lang/qugetscreatedby').' '.$qcreator);
				}
				// admin has access to content
				if(!$qaoutputdone && $this->template=='question' && qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN)
				{
					$this->output('<p style="font-size:12px;">~ '.qa_lang('quizx_lang/editor').'</p>');
					// default output question and answers
					qa_html_theme_base::main_parts($content);
				}
			}
			else
			{
				qa_html_theme_base::main_parts($content);
			}

		} // end main_parts($content)
		
		/* GAME MODE */
		public function a_list_items($a_items)
		{
			foreach ($a_items as $a_item)
			{
				if($this->gamemode)
				{
					// remove all signs of best answer from output
					if(isset($a_item['select_text']))
					{
						unset($a_item['select_text']);
					}
					$a_item['classes'] = str_replace(' answer-selected', '', $a_item['classes']);
					$a_item['raw']['isselected'] = false;
					$a_item['selected'] = false;
				}
				// default add
				$this->a_list_item($a_item);
			}
		}
		public function a_list_item($a_item)
		{
			// 'select_tags' => string 'title="Als richtige Antwort auswählen" name="a42_doselect" onclick="return qa_answer_click(42, 41, this);"' (length=107)
			// do not show best answer
			if($this->gamemode) 
			{
				// var_dump($a_item['selected']);
				// needs also jquery to prevent the default input submit
				$a_item['select_tags'] = 'title="'.qa_lang('quizx_lang/selectthisanswer').'" data-original="'.$a_item['raw']['postid'].'" ';
			}
			
			qa_html_theme_base::a_list_item($a_item);
		}
		public function a_selection($post)
		{
			if($this->gamemode)
			{
				// FAKE the select buttons, they will work with AJAX - not q2a functions
				$this->output('<div class="qa-a-selection">');
				// <input title="Als richtige Antwort auswählen" name="a42_doselect" onclick="return qa_answer_click(42, 41, this);" type="submit" value="" class="qa-a-select-button">
				if (isset($post['select_tags']))
					$this->post_hover_button($post, 'select_tags', '', 'qa-a-select');
				elseif (isset($post['unselect_tags']))
					$this->post_hover_button($post, 'unselect_tags', '', 'qa-a-unselect');
				elseif ($post['selected'])
					$this->output('<div class="qa-a-selected">&nbsp;</div>');

				if (isset($post['select_text']))
					$this->output('<div class="qa-a-selected-text">'.@$post['select_text'].'</div>');

				$this->output('</div>');
			}
			else
			{
				qa_html_theme_base::a_selection($post);
			}
		}
		// show Klassenstufe in question if in game mode
		public function post_tags($post, $class)
		{
			qa_html_theme_base::post_tags($post, $class);
			
			// add info who created the question (credits!)
			if($this->template=='question' && $this->gamemode)
			{
				$q_creator = $post['raw']['handle'];
				if(isset($q_creator))
				{
					$this->output('
						<div class="quizx_qcreateinfo">
							<a href="'.qa_path('user/'.$q_creator).'">
								'.qa_lang('quizx_lang/createdby').' '.$q_creator.'
							</a>
						</div>');
				}
				else
				{
					// admin can see ip
					if(qa_get_logged_in_level()>=QA_USER_LEVEL_ADMIN)
					{
						$q_creatorip = $post['raw']['createip'];
						$this->output('
							<div class="quizx_qcreateinfo">
								<a href="'.qa_path('ip/'.$q_creatorip).'">
									'.qa_lang('quizx_lang/createdbyguest').'
								</a>
							</div>
						');
					}
					else
					{
						$this->output('<div class="quizx_qcreateinfo">'.qa_lang('quizx_lang/createdbyguest').'</div>');
					}
				}
			}
		}


		// only list questions in question list that have status "public"
		public function q_list($q_list)
		{
			$qscount = count($q_list['qs']); // 0 to max 30
			for($i=0;$i<$qscount;$i++)
			{
				$qid = $q_list['qs'][$i]['raw']['postid'];
				if(quizx_check_q_status($qid)!='published')
				{
					unset($q_list['qs'][$i]);
				}
			}

			// default call
			qa_html_theme_base::q_list($q_list);
		}


		public function post_meta($post, $class, $prefix=null, $separator='<br/>')
		{
			// changes of qa page
			if($this->template=='qa' || $this->template=='questions')
			{
				// do not show "answered by" as meta in questions list
				// $this->content['q_list']['qs'][0]['meta_order'] = '';
			}
			else
			{
				// default call
				qa_html_theme_base::post_meta($post, $class, $prefix=null, $separator='<br/>');
			}
		}

		// theme override: custom footer to add custom credits and ismobile div-flag
		public function attribution()
		{
			// default call
			qa_html_theme_base::attribution();
			
			/*
			// theme individual!
			$this->output(
				'<div class="qa-attribution">',
				'<a href="'.qa_path('feedback').'">contact</a>',
				'| <a href="'.qa_path('terms').'">terms</a>',
				'| <a href="'.qa_path('legal').'">legal</a>',
				'</div>'
			);
			*/
			
			// important: mobiles-identifier for jquery
			if(qa_is_mobile_probably())
			{
				$this->output('<div id="agentIsMobile"></div>');
			}
			
			// add div for lightbox plugin
			$this->output('<div id="lightbox-popup"> <div id="lightbox-center"> 
							<img id="lightbox-img" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D" alt="Lightbox" > 
							</div> </div>');
		}

		// theme override
		public function head_title()
		{
			$pagetitle = strlen($this->request) ? strip_tags(@$this->content['title']) : '';
			if($this->template=='question') 
			{
				$headtitle = (strlen($pagetitle) ? ($pagetitle.' - ') : '').$this->content['site_title'];
				if(isset($this->content['q_view']['raw']['tags'])) 
				{
					$tagintitle = q2apro_tagtoword($this->content['q_view']['raw']['tags']);
					$headtitle = $tagintitle.': '.$headtitle;
				}
			}
			else 
			{
				$headtitle = (strlen($pagetitle) ? ($pagetitle.' - ') : '').$this->content['site_title'];				
			}

			// add to title for Fragesport
			if($this->template=='qa')
			{
				$headtitle = qa_opt('site_title').' - '.qa_lang('quizx_lang/metatitle');
			}
			
			$this->output('<title>'.$headtitle.'</title>');
		}
		
		// theme override
		public function nav($navtype, $level=null)
		{
			qa_html_theme_base::nav($navtype, $level=null);
			
			if($navtype=='main')
			{
				if(!qa_is_logged_in())
				{
					$cookieid = qa_cookie_get();
					$guestid = getguestid_bycookie($cookieid);
					$guestscore = getguestscore($cookieid);
					$guesttime = getguestplaytime($cookieid);
					$this->output('
						<p style="float:right;margin-right:10px;font-size:12px;line-height:150%;color:#F5F5F5;">
							Gast-ID: '.getshorterguestid($guestid).' <br />
							<a href="'.qa_path('userstats').'">'.qa_lang('quizx_lang/recentscore').': '.$guestscore.'</a><br />
							'.qa_lang('quizx_lang/playtime').': '.$guesttime.'
						</p>
					');
				}
				// is logged-in user
				else
				{
					$userid = qa_get_logged_in_userid();
					$userscore = getuserscore($userid);
					$usertime = getuserplaytime($userid);
					$this->output('
						<p style="float:right;margin-right:10px;font-size:12px;line-height:150%;color:#F5F5F5;">
							<a href="'.qa_path('userstats').'">'.qa_lang('quizx_lang/recentscore').': '.$userscore.'</a><br />
							'.qa_lang('quizx_lang/playtime').': '.$usertime.'
						</p>
					');
				}
			}
		}

		// replace qa.feed with question.feed
		public function feed()
		{
			$feed = @$this->content['feed'];

			if (!empty($feed))
			{
				$this->output('<div class="qa-feed">');
				if ($this->template=='qa' || $this->template=='questions' || $this->template=='unanswered')
				{
					$this->output('<a href="'.qa_path('feed').'/questions.rss" class="qa-feed-link">'.qa_lang('quizx_lang/newquizqu_rss').'</a>');
				}
				else
				{
					$this->output('<a href="'.$feed['url'].'" class="qa-feed-link">'.@$feed['label'].'</a>');					
				}
				$this->output('</div>');
			}
		}
	
		// override to not output intro field on all pages
		public function sidebar()
		{
			if($this->template=='qa' || $this->template=='questions' || $this->template=='unanswered')
			{
				$sidebar = @$this->content['sidebar'];
				if (!empty($sidebar))
				{
					$this->output('<div class="qa-sidebar">');
					$this->output_raw($sidebar);
					$this->output('</div>', '');
				}
			}
		}

		// hide attribution coz of bots
		public function html()
		{
			$this->output(
				'<html>'
			);

			$this->head();
			$this->body();

			$this->output(
				'</html>'
			);
		}
	
	} // - END qa_html_theme_layer







	/* QUIZX base functions */

	// checks status of question, status can be "open", "editdone" or "published"
	function quizx_check_q_status($qid)
	{
		// check if question is in moderation
		$qinmod = qa_db_read_one_value(
					qa_db_query_sub('SELECT status FROM `^quizx_moderate`
										WHERE questionid = #',
										$qid), true);
		if($qinmod==0)
		{
			return 'open';
		}
		else if($qinmod==1)
		{
			return 'editdone';
		}
		else if($qinmod==2)
		{
			return 'published';
		}
		return $qinmod; // null
	}

	
	function q2apro_tagtoword($string)
	{
		// capitalize first letters of words
		$label_transform = implode('-', array_map('ucfirst', explode('-', $string)));

		// remove hyphens
		$label_transform = str_replace('-', ' ', $label_transform);
		
		return $label_transform;
	} // end q2apro_tagtoword
			
/*
	Omit PHP closing tag to help avoid accidental output
*/
