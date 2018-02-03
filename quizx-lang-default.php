<?php
/*
	Plugin Name: Q2APRO Quizx
*/

	return array(
		
		// default
		'enable_plugin' => 'Enable Plugin',
		'plugin_disabled' => 'Plugin has been disabled.',
		'access_forbidden' => 'Access forbidden.',
		'plugin_page_url' => 'Open Editor desk:',
		'minimum_level' => 'Level to access the editor desk:',
		'q2apro_contact' => 'For questions please visit ^1q2apro.com^2',
		
		// page for admin - editor desk
		'admin_timeformat' => 'Timeformat for PHP and Javascript',
		'admin_servertimezone' => 'Server time zone for correct time display. ^1Find your timezone here^2.',
		'admin_localization' => 'Localization for PHP time output (e.g. en_US, de_DE, fr_FR, see ^1here for more^2).',
		'admin_lastqutoshow' => 'Number of last questions played by user, showing up in Userstats Widget (sidebar)',
		'admin_checkwidgets' => 'Please remember to add the needed Widgets to the layout. Recommendation: 1. "User Statistics" - Side panel - Below sidebar box, 2. "Activities" - Side panel - Below sidebar box, 3. "Question Statistics" - Side panel - Top, 4. "Skip Question" - Main area - Bottom. Click here:',
		'access_problem' => 'Could not access server.',
		'page_editordesk_title' => 'Editor Desk',
		'th_postid' => 'id',
		'th_creator' => 'Creator',
		'th_created' => 'Date',
		'th_status' => 'Status',
		'th_questiontitle' => 'Question title',
		'th_posttags' => 'Topic',
		'status_getscreated' => 'gets created',
		'status_checkrelease' => 'check and release',
		'published' => 'published',
		'unlock' => 'release',
		
		// general
		'quiz' => 'Quiz',
		'guest' => 'Guest',
		'seconds' => 'Seconds',
		'seconds_abbr' => 's',
		'minutes_abbr' => 'min',
		'correct' => 'correct',
		'correctfort' => 'Super, correct!',
		'wrong' => 'wrong',
		'wrongunfort' => 'Unfortunately wrong.',
		'allquestionsdone' => 'All questions to this topic has been answered.',
		'choosenewtopic' => 'Choose new topic!',
		'nextquestion' => 'Go to next question (~qleft~ left)', // do not remove ~qleft~
		'yourtime' => 'Your time for this question',
		'qugetscreatedby' => 'The question is currently created by:',
		'createdby' => 'Created by:',
		'createdbyguest' => 'Created by guest',
		
		// welcome page
		'metatitle' => 'The Community-Quiz', // title of start page, appears in search engines (default page is expected to be 'qa')
		'welcomemsg' => 'Welcome at ',
		'welcometext1' => 'Here you can ^1test your knowledge^2.', // do not remove ^1 ^2, it becomes a link
		'welcometext2' => 'Answer quiz question to various areas of knowledge and improve your general knowledge.',
		'welcometext3' => 'The ^1List of the Best Players^2 shows you how good you are compared to others.',
		'welcometext4' => 'Furthermore you can ^1create our own quiz questions^2.',
		'welcometext5' => 'The more quiz questions you create, the more all the community can learn and enjoy fun while playing.',
		'startquiz' => 'Start Quiz',
		'showbestplayers' => 'Show best players',
		'enterquizquestion' => 'Enter quiz question',
		'availablequizqu' => 'Currently there are ^1quiz questions^2 available.', // number will be inserted
		
		// page game start
		'choosetopic' => 'Choose topic',
		'notopicsfound' => 'Sorry, there are no quiz questions to this topic.',
		'playanew' => 'Start to play anew',
		'unlocksallquagain' => 'Unlocks all quiz questions again',
		'goal_hint' => 'It is your goal to answer all quiz questions correctly at the first time.',
		'playrandomly' => 'Play all Questions (randomly)',
		'playbytopics' => 'Play by Topic',
		'quizquestion' => 'Quiz question',
		'quizquestions' => 'Quiz questions',
		'xfromy' => 'from',
		'youplayedallqu' => 'You have played all quiz questions', // x from y questions
		'youplayedxfromyqu' => 'You have played ~data~', // x from y questions
		
		
		'starttest' => 'Start Test',
		'highscores' => 'Highscores',
		'editor' => 'Editor',
		'editordesk' => 'Editor desk',
		'profile' => 'Profile',
		'bestplayers' => 'Best Players',
		'recentscore' => 'Recent Score',
		'playtime' => 'Play time',
		
		'thxforquizquestion' => 'Thank you for your quiz question. It will be checked and approved within the next 24 hours.',
		'createanotherquestion' => 'Create another Quiz Question',
		
		'addmoreanswers' => 'Please add ~acount~.', // do not remove ~acount~ 
		'oneanswer' => '1 answer', 
		'answers' => 'answers',
		'needtwoanswers' => 'You need to add 2 answers so that this quiz question can be sent to the editors',
		'submitquizquestion' => 'Submit Quiz Question',
		'sendqutoeditors' => 'Send quiz question to editors',
		'selectcorrectbelow' => 'Select the correct answer below',
		'selectcorrecttip' => 'Simply click on the check mark next to the answer. You can also change this afterwards.',
		'addanswer' => 'Create another answer',
		'releasequizqu' => 'Release quiz question (editor desk)',
		'releasequizqu_tip' => 'This quiz question will be published.',
		'moderatemorequ' => 'Moderate more questions',
		'nomoreunmoderated' => 'No unmoderate questions left. Well done.',
		
		'alreadyanswered' => 'You have played this quiz question already within the last 24 hours. Today you cannot play this specific one anymore.',
		'waitstilltime' => 'Please wait ~hours~ h ~mins~ min.', // do not translate values ~hours~ and ~mins~
		'already_lastanswer' => 'You have played this question the last time ~pitchtime~ ~result~.',
		'already_userstats' => 'In total you have ~qcorrect~ correctly answered and ~qincorrect~ incorrectly.',
		
		'seeqcreators' => 'Here you see the members who created most of the quiz questions.',
		'yourquizquestion' => 'This is your question :-)',
		'unsubmittedqu' => 'You have incomplete quiz questions left. Please submit them:',
		'submitquallowed' => 'Great, you can now ^1submit the question^2.',
		'addmoreorselect' => '^1Add more answers^2 or select the correct answer by clicking the checkmark.',
		'answersmissing' => 'You have to add answers to the quiz question.',
		'quneedsunlock' => 'The editors have to approve this question.',
		'solutionhelper' => 'Solution helper',
		'selectthisanswer' => 'Choose this answer?',
		
		'newquizqu_rss' => 'New Quiz Questions', // rss link sidepanel
		
		// page highscores
		'pagetitle_highscores' => 'Highscores - Best Players',
		'last24hours' => 'of the last 24 hours',
		'fromday' => 'from', // from day x
		'chooseday' => 'Choose day', 
		'xfromycorrect' => '~ccount~ of ~attempts~ correct', 
		'success' => 'Success', 
		'time' => 'Time', 
		'secperqu' => 'Sec. per Question', 
		'answpermin' => 'Answ./min', 
		'points' => 'Points', 
		'correctansw' => 'Correct ones', 
		
		// page userstats
		'yourstats' => 'Your Game Statistics', 
		'playquizqu' => 'Played quiz questions', 
		'24hour' => '24-hour-', 
		'gameplay' => 'Gameplay', 
		'thatsthe' => 'That is the', 
		'gameplayfrom' => 'Gameplay of', 
		'gameplaybytopic' => 'Gameplay by Topic', 
		'yourstats_hint' => 'Hello ~name~, this is your game statistic.', 
		'yourstats_played' => 'You have played ~x~ quiz questions in total.', 
		'showfullgameplay' => 'Show complete Gameplay.', 
		'showfullgameplay24' => 'Show 24-hour-gameplay.', 
		'yourfullgameplay' => 'This is your complete gameplay:', 
		'topic' => 'Topic', 
		'firstattempt' => 'First attempt', 
		'questiontitle' => 'Question title', 
		'total' => 'Total', 
		'totalrating' => 'Overall rating', 
		'quansweredcorrect' => 'quiz questions answered correctly', // x from y ...
		'average' => 'Average', 
		'perquestion' => 'per quiz question', // seconds per question
		'notplayedyet' => 'You have not played yet. No play activity detected.',
		
		// widget activities
		'hasthequestion' => 'has the question', // player x has the question y answered/played
		'answered' => 'answered', // wrong/correctly answered
		'whoplaysnow' => 'Who is playing now', // wrong/correctly answered
		
		// widget contributors
		'lastcontributors' => 'The last quiz questions were created by:',
		
		// widget questionstats
		'versus' => 'vs.', // correct vs. wrong
		'haveansweredcorrect' => 'have answered the question correctly', // x ...
		'newqu_urfirst' => 'This quiz question is brandnew. You are the first player!', 
		
		// widget skipquestion
		'skipquestion' => 'Skip question', 
		'quleft' => 'left', // questions left to play
		'fortopic' => 'for topic', // questions left to play for topic ...
		'hinttoquestion' => 'Note regarding this question?',
		'hinttoquestion_tooltip' => 'Contact the editors for improvements',
		
		// widget userstats
		'yourlastattempts' => 'Your last attempts:',
		'rating_1a' => 'excellent',
		'rating_1' => 'very good',
		'rating_2' => 'good',
		'rating_3' => 'okay',
		'rating_4' => 'satisfactory',
		'rating_5' => 'inadequate',
		'rating_6' => 'insufficient',
		'grade' => 'Grade',
		'yourscore' => 'Your Score',
		'yourstatsindetail' => 'Your statistics in detail',
		
	);


/*
	Omit PHP closing tag to help avoid accidental output
*/