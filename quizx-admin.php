<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	class quizx_admin 
	{

		// initialize db-table 'eventlog' if it does not exist yet
		function init_queries($tableslc) 
		{
		
			$tablename1 = qa_db_add_table_prefix('quizx_moderate');
			
			// create table qa_quizx_moderate which stores the questions to be moderated
			if(!in_array($tablename1, $tableslc))
			{
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS `^quizx_moderate` (
					`questionid` int(10) unsigned NOT NULL,
					`userid` int(10) unsigned DEFAULT NULL,
					`status` tinyint(1) unsigned DEFAULT "0" COMMENT "0-open 1-editdone 2-published",
					`tags` varchar(800) DEFAULT NULL,
					PRIMARY KEY (`questionid`),
					UNIQUE KEY `questionid` (`questionid`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
				);
			}
 
			$tablename2 = qa_db_add_table_prefix('quizx_gameplay');
			
			// create table qa_quizx_gameplay which stores the game results
			if(!in_array($tablename2, $tableslc))
			{
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS `^quizx_gameplay` (
					`userid` int(10) unsigned DEFAULT NULL,
					`timestamp` datetime NOT NULL,
					`questionid` int(10) unsigned NOT NULL,
					`answerid` int(10) unsigned NOT NULL,
					`correct` tinyint(1) unsigned NOT NULL,
					`elapsed` int(10) unsigned DEFAULT NULL,
					`cookieid` bigint(20) unsigned DEFAULT NULL,
					`ipaddress` int(10) unsigned DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
				);
			}

			$tablename3 = qa_db_add_table_prefix('quizx_stamps');
			
			// create table qa_quizx_stamps which stores the reset times of each player (to play questions again)
			if(!in_array($tablename3, $tableslc))
			{
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS `^quizx_stamps` (
					`userid` int(10) unsigned DEFAULT NULL,
					`timestamp` datetime NOT NULL,
					`cookieid` bigint(20) unsigned DEFAULT NULL,
					`actiontype` varchar(255) CHARACTER SET utf8 DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
				);
			}

			$tablename4 = qa_db_add_table_prefix('quizx_highscores');
			
			// create table qa_quizx_highscore which stores the monthly scores of all players
			if(!in_array($tablename4, $tableslc))
			{
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS `^quizx_highscores` (
					`date` date NOT NULL,
					`userid` int(10) unsigned NOT NULL,
					`rating` decimal(10,3) unsigned NOT NULL,
					`qcorrect` int(10) unsigned NOT NULL,
					`qincorrect` int(10) unsigned NOT NULL,
					PRIMARY KEY (`date`,`userid`),
					KEY `date` (`date`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
				);
			}

		} // end init_queries


		// option's value is requested but the option has not yet been set
		function option_default($option)
		{
			switch($option)
			{
				case 'quizx_enabled':
					return 1; // true
				case 'quizx_timeformat':
					return 'd.m.Y';
				case 'quizx_servertimezone':
					return 'Europe/Berlin';
				case 'quizx_localization':
					return 'en_US';
				case 'quizx_lastqutoshow':
					return 3;
				case 'quizx_permission':
					return QA_PERMIT_ADMINS; // default level to access editor page
				default:
					return null;				
			}
		}
			
		function allow_template($template)
		{
			return ($template!='admin');
		}       
			
		function admin_form(&$qa_content){                       

			// process the admin form if admin hit Save-Changes-button
			$ok = null;
			if (qa_clicked('quizx_save'))
			{
				qa_opt('quizx_enabled', (bool)qa_post_text('quizx_enabled')); // empty or 1
				qa_opt('quizx_timeformat', (String)qa_post_text('quizx_timeformat'));
				qa_opt('quizx_servertimezone', (String)qa_post_text('quizx_servertimezone'));
				qa_opt('quizx_localization', (String)qa_post_text('quizx_localization'));
				qa_opt('quizx_lastqutoshow', (int)qa_post_text('quizx_lastqutoshow'));
				qa_opt('quizx_permission', (int)qa_post_text('quizx_permission')); // level
				$ok = qa_lang('admin/options_saved');
			}
			
			// form fields to display frontend for admin
			$fields = array();
			
			$fields[] = array(
				'type' => 'checkbox',
				'label' => qa_lang('quizx_lang/enable_plugin'),
				'tags' => 'name="quizx_enabled"',
				'value' => qa_opt('quizx_enabled'),
			);
			
			$fields[] = array(
				'type' => 'input',
				'label' => qa_lang('quizx_lang/admin_timeformat'),
				'tags' => 'name="quizx_timeformat"',
				'value' => qa_opt('quizx_timeformat'),
			);

			$fields[] = array(
				'type' => 'input',
				'label' => strtr( qa_lang('quizx_lang/admin_servertimezone'), array( 
							'^1' => '<a target="_blank" href="http://php.net/manual/en/timezones.php">',
							'^2' => '</a>'
						  )),
				'tags' => 'name="quizx_servertimezone"',
				'value' => qa_opt('quizx_servertimezone'),
			);

			$fields[] = array(
				'type' => 'input',
				'label' => strtr( qa_lang('quizx_lang/admin_localization'), array( 
							'^1' => '<a target="_blank" href="http://stackoverflow.com/q/3191664/1066234">',
							'^2' => '</a>'
						  )),
				'tags' => 'name="quizx_localization"',
				'value' => qa_opt('quizx_localization'),
			);
			
			$fields[] = array(
				'type' => 'input',
				'label' => qa_lang('quizx_lang/admin_lastqutoshow'),
				'tags' => 'name="quizx_lastqutoshow"',
				'value' => qa_opt('quizx_lastqutoshow'),
			);

			$view_permission = (int)qa_opt('quizx_permission');
			$permitoptions = qa_admin_permit_options(QA_PERMIT_ALL, QA_PERMIT_SUPERS, false, false);
			$pluginpageURL = qa_path('desk');
			$fields[] = array(
				'type' => 'select',
				'label' => qa_lang('quizx_lang/minimum_level'),
				'tags' => 'name="quizx_permission"',
				'options' => $permitoptions,
				'value' => $permitoptions[$view_permission],
			);
			
			$avwidgstring = qa_lang('admin/widgets_explanation');			
			// remove hyphen in end of default string
			$avwidgstring = substr($avwidgstring, 0, strlen($avwidgstring)-1);
			
			$fields[] = array(
				'type' => 'static',
				'note' => qa_lang('quizx_lang/plugin_page_url').' <a target="_blank" href="'.$pluginpageURL.'">'.$pluginpageURL.'</a>',
			);
			
			$fields[] = array(
				'type' => 'static',
				'note' => '<span style="color:#00F;">'.qa_lang('quizx_lang/admin_checkwidgets').' <a target="_blank" href="'.qa_path('admin/layout').'#home_description" style="text-decoration:underline;">'.$avwidgstring.'</a></span>',
			);
			
			// link to q2apro.com
			$fields[] = array(
				'type' => 'static',
				'note' => '<span style="color:#789;">'.strtr( qa_lang('quizx_lang/q2apro_contact'), array( 
							'^1' => '<a target="_blank" href="http://www.q2apro.com/contact">',
							'^2' => '</a>'
						  )).'</span>',
			);
			
			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
				'fields' => $fields,
				'buttons' => array(
					array(
						'label' => qa_lang('main/save_button'),
						'tags' => 'name="quizx_save"',
					),
				),
			);
		}
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/