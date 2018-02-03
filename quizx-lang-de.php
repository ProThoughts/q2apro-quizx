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
		'page_editordesk_title' => 'Redaktion',
		'th_postid' => 'id',
		'th_creator' => 'Ersteller',
		'th_created' => 'Datum',
		'th_status' => 'Status',
		'th_questiontitle' => 'Fragetitel',
		'th_posttags' => 'Thema',
		'status_getscreated' => 'wird erstellt',
		'status_checkrelease' => 'prüfen und freischalten',
		'published' => 'veröffentlicht',
		'unlock' => 'freischalten',
		
		// general
		'quiz' => 'Quiz',
		'guest' => 'Gast',
		'seconds' => 'Sekunden',
		'seconds_abbr' => 's',
		'minutes_abbr' => 'min',
		'correct' => 'richtig',
		'correctfort' => 'Super, korrekt!',
		'wrong' => 'falsch',
		'wrongunfort' => 'Leider falsch.',
		'allquestionsdone' => 'Alle Fragen zum Thema beantwortet.',
		'choosenewtopic' => 'Neues Thema aussuchen!',
		'nextquestion' => 'Nächste Frage aufrufen (~qleft~ übrig)', // do not remove ~qleft~
		'yourtime' => 'Deine Zeit für diese Aufgabe',
		'qugetscreatedby' => 'Die Frage wird derzeit erstellt von:',
		'createdby' => 'Erstellt von:',
		'createdbyguest' => 'Erstellt von Gast',
		
		// welcome page
		'metatitle' => 'Das Community-Quiz', // title of start page, appears in search engines (default page is expected to be 'qa')
		'welcomemsg' => 'Willkommen bei',
		'welcometext1' => 'Bei uns könnt ihr euer ^1Wissen testen^2.', // do not remove ^1 ^2, it becomes a link
		'welcometext2' => 'Beantwortet Fragen zu verschiedenen Wissensbereichen und verbessert langfristig euer Allgemeinwissen.',
		'welcometext3' => 'Die ^1Bestenliste^2 zeigt euch, wie gut ihr im Vergleich zu anderen seid.',
		'welcometext4' => 'Auch könnt ihr bei uns ^1eigene Testfragen^2 erstellen.',
		'welcometext5' => 'Je mehr Fragen ihr einstellt, desto mehr Spaß haben alle.',
		'startquiz' => 'Quiz starten',
		'showbestplayers' => 'Beste Spieler anzeigen',
		'enterquizquestion' => 'Testfrage eingeben',
		'availablequizqu' => 'Aktuell stehen ^1Testfragen^2 zur Verfügung.',
		
		// page game start
		'choosetopic' => 'Thema auswählen',
		'notopicsfound' => 'Sorry, keine Fragen zum Thema gefunden.',
		'playanew' => 'Von vorne spielen',
		'unlocksallquagain' => 'Schaltet alle Testfragen wieder frei',
		'goal_hint' => 'Ziel ist es, alle Fragen beim ersten Mal richtig zu beantworten.',
		'playrandomly' => 'Alle Testfragen spielen (zufällig)',
		'playbytopics' => 'Nach Themen spielen',
		'quizquestion' => 'Testfrage',
		'quizquestions' => 'Testfragen',
		'xfromy' => 'von',
		'youplayedallqu' => 'Du hast alle Testfragen gespielt', // x from y questions
		'youplayedxfromyqu' => 'Du hast ~data~ gespielt', // x from y questions
		
		
		'starttest' => 'Test starten',
		'highscores' => 'Highscores',
		'editor' => 'Editor',
		'editordesk' => 'Redaktion',
		'profile' => 'Profil',
		'bestplayers' => 'Beste Spieler',
		'recentscore' => 'Aktuelle Score',
		'playtime' => 'Spielzeit',
		
		'thxforquizquestion' => 'Vielen Dank für deine Testfrage. Sie wird innerhalb von 24 Stunden geprüft und freigeschaltet.',
		'createanotherquestion' => 'Weitere Testfrage erstellen',
		
		'addmoreanswers' => 'Bitte noch ~acount~ eingeben.', // do not remove ~acount~ 
		'oneanswer' => '1 Antwort', 
		'answers' => 'Antworten',
		'needtwoanswers' => 'Es sind 2 Antworten nötig, damit du deine Testfrage an die Redaktion senden kannst',
		'submitquizquestion' => 'Testfrage einreichen',
		'sendqutoeditors' => 'Testfrage an die Redaktion senden',
		'selectcorrectbelow' => 'Richtige Antwort unten auswählen',
		'selectcorrecttip' => 'Dazu einfach auf den Haken bei der richtigen Antwort klicken. Kann danach auch noch geändert werden.',
		'addanswer' => 'Weitere Antwort erstellen',
		'releasequizqu' => 'Testfrage freischalten (Redaktion)',
		'releasequizqu_tip' => 'Die Testfrage wird veröffentlicht.',
		'moderatemorequ' => 'Weitere Frage moderieren',
		'nomoreunmoderated' => 'Keine unmoderierten Fragen mehr!',
		
		'alreadyanswered' => 'Du hast diese Frage in den letzten 24 Stunden bereits beantwortet. Heute ist kein weiterer Versuch mehr möglich.',
		'waitstilltime' => 'Warte noch ~hours~ Std ~mins~ min.', // do not translate values ~hours~ and ~mins~
		'already_lastanswer' => 'Du hast diese Frage das letzte Mal ~pitchtime~ ~result~ beantwortet.',
		'already_userstats' => 'Insgesamt hast du ~qcorrect~ mal richtig und ~qincorrect~ mal falsch gelegen.',
		
		'seeqcreators' => 'Hier siehst du die Mitglieder, die am meisten Testfragen erstellt haben.',
		'yourquizquestion' => 'Das ist deine Frage :-)',
		'unsubmittedqu' => 'Du hast noch unfertige Fragen. Bitte einreichen:',
		'submitquallowed' => 'Sehr schön, du kannst die Frage ^1nun einreichen^2.',
		'addmoreorselect' => '^1Weitere Antworten eingeben^2 oder richtige Antwort mit Klick auf den Haken auswählen.',
		'answersmissing' => 'Du musst die Frage noch mit Antworten versehen.',
		'quneedsunlock' => 'Die Frage ist noch von der Redaktion freizuschalten.',
		'solutionhelper' => 'Lösungshelfer',
		'selectthisanswer' => 'Diese Antwort auswählen?',
		
		'newquizqu_rss' => 'Neue Testfragen', // rss link sidepanel
		
		// page highscores
		'pagetitle_highscores' => 'Highscores - Beste Spieler',
		'last24hours' => 'der letzten 24 Stunden',
		'fromday' => 'vom', // from day x
		'chooseday' => 'Tag wählen', 
		'xfromycorrect' => '~ccount~ von ~attempts~ richtig', 
		'success' => 'Erfolg', 
		'time' => 'Zeit', 
		'secperqu' => 'Sek. je Frage', 
		'answpermin' => 'Antw./min', 
		'points' => 'Punkte', 
		'correctansw' => 'Richtige', 
		
		// page userstats
		'yourstats' => 'Deine Spiel-Statistik', 
		'playquizqu' => 'Gespielte Testfragen', 
		'24hour' => '24-Stunden-', 
		'gameplay' => 'Gameplay', 
		'thatsthe' => 'Das ist der', 
		'gameplayfrom' => 'Gameplay von', 
		'gameplaybytopic' => 'Gameplay nach Thema', 
		'yourstats_hint' => 'Hallo ~name~, dies ist deine Spielstatistik.', 
		'yourstats_played' => 'Du hast insgesamt ~x~ Testfragen gespielt.', 
		'showfullgameplay' => 'Vollständigen Gameplay anzeigen.', 
		'showfullgameplay24' => '24-Stunden-Gameplay anzeigen.', 
		'yourfullgameplay' => 'Dies ist dein vollständiger Gameplay:', 
		'topic' => 'Thema', 
		'firstattempt' => 'Erster Versuch', 
		'questiontitle' => 'Fragetitel', 
		'total' => 'Gesamt', 
		'totalrating' => 'Gesamtwertung', 
		'quansweredcorrect' => 'Testfragen richtig beantwortet', // x from y ...
		'average' => 'Durchschnitt', 
		'perquestion' => 'je Testfrage', // seconds per question
		'notplayedyet' => 'Du hast noch nicht gespielt. Keine Spielaktivität verzeichnet.',
		
		// widget activities
		'hasthequestion' => 'hat die Frage', // player x has the question y answered/played
		'answered' => 'beantwortet', // wrong/correctly answered
		'whoplaysnow' => 'Wer gerade spielt', // wrong/correctly answered
		
		// widget contributors
		'lastcontributors' => 'Die letzten Testfragen stammen von:',
		
		// widget questionstats
		'versus' => 'vs.', // correct vs. wrong
		'haveansweredcorrect' => 'haben die Frage richtig beantwortet', // x ...
		'newqu_urfirst' => 'Diese Testfrage ist brandneu. Du bist der erste Spieler!', 
		
		// widget skipquestion
		'skipquestion' => 'Frage überspringen', 
		'quleft' => 'übrig', // questions left to play
		'fortopic' => 'zum Thema', // questions left to play for topic ...
		'hinttoquestion' => 'Hinweis zu dieser Frage?',
		'hinttoquestion_tooltip' => 'Kontaktiere die Redaktion für Verbesserungen',
		
		// widget userstats
		'yourlastattempts' => 'Deine letzten Versuche:',
		'rating_1a' => 'exzellent',
		'rating_1' => 'sehr gut',
		'rating_2' => 'gut',
		'rating_3' => 'befriedigend',
		'rating_4' => 'genügend',
		'rating_5' => 'mangelhaft',
		'rating_6' => 'ungenügend',
		'grade' => 'Note',
		'yourscore' => 'Dein Spielstand',
		'yourstatsindetail' => 'Deine Statistik im Detail',
		
	);


/*
	Omit PHP closing tag to help avoid accidental output
*/