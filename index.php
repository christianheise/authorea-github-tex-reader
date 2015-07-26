<!DOCTYPE html> 
<?php 
/**
 * parsegit.php
 * Github Authorea LaTex-Reader
 * 2015 
 * Version: alpha
 * Christian Heise
 * CC-BY-SA
 */

// General Settings

$project = ''; // Projektname
$description = ''; // Beschreibung
$mehrinfolink = ''; // more Infolink
$moreinfolinktext =''; // more Infolinktext
$kontakturl = ''; // Contact URL
$version = "";  // Version of the Document
$headtitle = ''; // Title for Head
$progress = ''; // Progress of the Document
$autor = ""; // Autorname
$cache = 'false'; // Activate Cache - false or true
$cachetimeday = 300; // Cachingtime (day)
$cachetimenight = 1800; // Cachingtime (night)
$abweichung = 50; // expected variance for Pagecount because of figures and references
$licencetext = ''; // Licencetext in Footer
$moreinfotext=''; // Footer Text  for contact and imprint
$notfound='<b>Error:</b> Not Found.'; // Not Found Text
$DOI = ''; // Digital Object Identifier
$Badge = ''; // Badges like zenodo DOI-Badge


// Settings for Import and Tracking

$username = ''; // GitHub User
$repo = ''; // GitHub Repository
$ausername = ''; // Authorea Username
$aarticleid = '';  // Authorea Article ID
$datatext = 'Data: <a href="https://www.authorea.com/users/'.$ausername.'/articles/'.$aarticleid.'/_show_article" target="_blank">Authorea</a> & <a href="https://github.com/christianheise/'.$repo.'" target="_blank">GitHub</a> eingesehen werden.<br>'; // Data-Text in Footer


$piwikurl = ''; // Tracker Image

// Language Settings

$langstatus="Stand"; // Status
$langof = "von"; // of
$langextent = "Umfang"; // Extend
$langpages = "Seiten"; // Pages
$langstill = "noch"; // still
$langvoll = "Komplettansicht"; // full view text
$langchap = "Kapitel"; // chapter
$langchoosechap = "Kapitel auswählen"; // chapter
$langtoc = "Inhaltsverzeichnis"; // TOC
$langreferences = "Literaturverzeichnis"; // bibliographical references
$langlinkmain = "Übersicht"; // Linktext Breadcrumblink to Mainpage
$langsdata = "Daten"; // Data
$langfigs = "Abbildungen"; // Figures
$langrefs = "Literatur" ; // Literature
$langshowrefs = "Literatur anzeigen"; //show Literature
$langshowfigs = "Abbildungen anzeigen"; //show Figures
$langshowdata = "Daten anzeigen"; //show  Data
$langwords = "Wörter"; //words
$langtodos = "Todos"; //Todos
$langview = "Ansicht"; // Viewmodus
$langtext = "Text"; // Text
$langlastchagnes = "Letzte 5 Änderungen"; // last changes
$langat = "am"; // Lastchanges Date "at"


// Start Code Github Authorea LaTex-Reader
// Just edit below if you know what you are doing

$rversion="0.34-alpha";

// Define $GET Variables
if (isset($_GET['bib'])) { $bibtex = $_GET['bib']; }  else  { $bib=""; } 
if (isset($_GET['bibtex'])) { $bibtex = $_GET['bibtex']; }  else  { $bibtex=""; } 
if (isset($_GET['chapter'])) { $chapter = $_GET['chapter']; }  else  { $chapter=""; }
if (isset($_GET['note'])) { $note = $_GET['note']; }  else  { $note=""; }  
if (isset($_GET['bibtexentry'])) { $bibtexentry = $_GET['bibtexentry']; } else  { $bibtexentry=""; } 
$current_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
date_default_timezone_set("Germany/Berlin");
$now = date('G');


// Head

$htmlhead ='<div id="top" class="header-div">
				<div class="header-logo">
					<img style="height: 30px !important;" src="../../img/oa.png">
					&nbsp;<span style="color: #f68212;font-weight: bold;">
					<a style="color: #f68212;" href="'.$kontakturl.'">'.$headtitle.'</a></font></span>
				</div>
			</div>';

// Functions


	// if file exists
	function get_http_response_code($url) {
    $headers = get_headers($url);
    return substr($headers[0], 9, 3);
	}

	// TOC for full view
	function TableOfContents($depth, $html_string)
	/*AutoTOC function written by Alex Freeman
	* Released under CC-by-sa 3.0 license
	* http://www.10stripe.com/  */
	{
 
	//get the headings down to the specified depth
	$html_string = preg_replace('/<h1[^>]*?>(.*?)<\/h[1]>/', '<h1>$1</h1>', $html_string);
	$html_string = str_replace('Kapitel:', '', $html_string);
	
	$pattern = '/<h[1-'.$depth.']*[^>]*>.*?<\/h[1-'.$depth.']>/';
	$whocares = preg_match_all($pattern,$html_string,$winners);
 
	//reformat the results to be more usable
	$heads = implode("\n",$winners[0]);
	$heads = preg_replace('/<h1[^>]*?>(.*?)<\/h[1]>/', '<h1><a href="#$1">$1</a></h1>', $heads);
	$heads = str_replace('<a href="# ','<a href="#',$heads);
	$heads = preg_replace('/<h1>/','<li class="toc$1">',$heads);
	$heads = preg_replace('/<\/h1>/','</a></li>',$heads);
	$heads = preg_replace('/<h2>/','<ol style="list-style-type:disc;"><li class="toc2">',$heads);
	$heads = preg_replace('/<\/h2>/','</a></li></ol>',$heads);
	$heads = preg_replace('/<h3>/','<ol style="list-style-type:circle;"><li class="toc3">',$heads);
	$heads = preg_replace('/<\/h3>/','</a></li></ol>',$heads);
	$heads = preg_replace('/<h4>/','<ol style="list-style-type:none;"><li class="toc4">',$heads);
	$heads = preg_replace('/<\/h4>/','</a></li></ol>',$heads);
 
	//plug the results into appropriate HTML tags
	$contents = '<div id="toc" class="well well-sm"> 
	<p id="toc-header" style="font-weight:bold; font-size:120%; line-height: 40px;">'.$langtoc.'</p>
	<ul style="list-style-type:none; margin-left:2em; line-height: 18px;">
	'.$heads.'
	</ul>
	</div>';
	echo $contents;
	}


	// Cache

	if($cache=="true")
	{

	// Tages- und Nachtcaching 
	// TODO: als Funktion umsetzen und effizienter machen
	if ($now > 10 && $now < 23) 
	{
    $cmodus="day";
	} else {
    $cmodus="night";
	}
		
		if ($cmodus=="day")
			{
    		$cachetime = $cachetimeday;
    		}
			else
    		{
    		$cachetime = $cachetimenight;
    		}

	// File-Cache starten
	// TODO: effizienter machen

	$break  = $_SERVER['PHP_SELF'];
	$file = str_replace("/", "", $break);
	// Cache in Verzeichniss ablegen
	$cachefile = 'cache/cached-'.$chapter.''.$bibtex.'-'.substr_replace($file ,"",-4).'.html';
	// Serve from the cache if it is younger than $cachetime
	if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
    // Cache Debug-Ausgabe
    echo "<!-- Cached copy, generated ";
    $timestamp = filemtime($cachefile);
	$timestamp_later = $timestamp + 7200;
	$newftime=strftime('%H:%M', $timestamp_later);
	echo "".$newftime." Cachetime (".$cmodus."-modus): ".$cachetime." -->\n";
    include($cachefile);
    exit;
	}
	// Start the output buffer
	ob_start(); 
	}
	else
	{
	echo "<!-- Cache not activated -->\n";
	}

	// include Parser
	include('includes/parsedown.php');

	// get latest Commits and Status of work
	// TODO: als Funktion umsetzen und effizienter machen
	$url = 'https://api.github.com/repos/'.$username.'/'.$repo.'/commits?page=1&per_page=5';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	//GitHub API request require a user agent, GitStatus uses Chrome UA
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1468.0 Safari/537.36'); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$commits = json_decode(curl_exec($ch), true);
	curl_close($ch);

		$ccount = 0;
		$houtput = "";
		foreach ($commits as $commit) 
		{
		
			$houtput .= '<li>';
			$houtput .= '';
			$houtput .=  ' <a href = "https://github.com/'.$username.'/'.$repo.'/commit/'.$commit['sha'].'" target="_blank">'.$commit['commit']['message'].'</a> '.$langat.' ';
			$date = $commit['commit']['committer']['date'];
			$time = date("H:i", strtotime($date));
			$timestamp = strtotime($time);
			$timestampplus2 = $timestamp + 7200;
			$newDate = date('d.m.y', strtotime($date . ' + 2 hours'));
			$newDatedetail = date('H:i', strtotime($date . ' + 2 hours'));
			$houtput .=  ' '.$newDate.'';	
			$houtput .=  '</li>';
			$ccount = $ccount+1;
			// get Stand der Arbeit
			if($ccount==1)
			{
			$stand="".$newDate." um ".$newDatedetail."";
			}		
		}

// define Referer
	if(isset($_SERVER['HTTP_REFERER']))
	{ 
	$HREFERER=$_SERVER['HTTP_REFERER'];
	}	
	else
	{ 
	$HREFERER="";
	}

// Footer including Tracker
$footer='<div style="text-align:center; margin-left:10px; padding-top:0px; max-width:100%">&nbsp;<br><small>'.$datatext.' '.$licencetext.'
<br><b>Reader-Version:</b> '.$rversion.' | <a title="Code of this Reader" target="_blank" href="https://github.com/christianheise/authorea-github-tex-reader">Source Code (Reader)</a> | <a title="'.$kontakturl.'" target="_top" href="'.$kontakturl.'">'.$moreinfotext.'</a><br>
</small><br>&nbsp;</div>';

$header='';


// Start Module: 1 - Bibtexbrowsereinbindung

if ($bibtex=="all")
	{ 
	$nav=$langreferences;

	// HTML-Kopf für Modul 1
	// TODO: als Funktion umsetzen und effizienter machen
	echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
	<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> 
  	<title>'.$langreferences.'</title>
	<link rel="canonical" href="http://'.$_SERVER['HTTP_HOST'].'/literature/all">
	<!-- Latest jquery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="../css/bootstrap.css">
	<!-- custom  -->
	<link rel="stylesheet" href="../css/custom.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="../js/bootstrap.min.js"></script>
	<style type="text/css">
	.sheader
	{ 
	font-size: 26px;
	font-weight: normal;
	}
	</style>
	</head>
	<body>';
	echo $htmlhead;
	
	// Container Anfang
	echo '<div class="container" style="padding-top:40px;">
	<div role="main" class="col-md-9"><ol class="breadcrumb"><li><a href="/uebersicht">'.$langlinkmain.'</a></li><li class="active">'.$langreferences.'</li>
	</ol><h1 class="page-header">'.$langreferences.' <small>(<a href="https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/bibliography/biblio.bib" target="_blank">BibTeX '.$langshow.'</a>)</small></h1>';
	
	// Definiere Variablen für die Bibtexeinbindung
	$_GET['bib']='https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/bibliography/biblio.bib';
	$_GET['all']=1;

	// Bibtexbrowser einbinden
	include('includes/bibtexbrowser.php');
	
	// col-md-9 Ende
	echo '</div>';

	// Navigation rechte Seite in Modul 1
  	echo '<div role="complementary" class="col-md-3">
        <nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm affix">
        <ul class="nav bs-docs-sidenav"><li>';
    
    // .tex Dateien aus Layout für Naviagation laden
    $opt_words = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/layout.md');
	$pattern = explode('.tex', $opt_words);

 	echo '<label>'.$langchoosechap.'</label><div class="list-group">';
	
	// für jedes Element ein Button
	foreach ($pattern as $value) 
		{ 
		
		// säubern
		$trimmed = trim($value);
		
			if ($trimmed=="")
			{ 
			}
 			else
 			{ 
 			echo '<a href="/chapter/'.$value.'#top" class="list-group-item">
    		<h5 class="list-group-item-heading ">'.$value.'</h5>';
 			echo '</a>'; 
  			}
		}
	
	// Links unter dem Inhaltsverzeichnis
	echo '</div><label>'.$langrefs.' & '.$langdata.'</label><div class="list-group">';
 	echo '<a href="/literature/all" class="list-group-item active"> <h5 class="list-group-item-heading">'.$langrefs.' '.$langshow.'</h5></a>'; 
  	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" class="list-group-item" target="_blank"> <h5 class="list-group-item-heading">'.$langdata.' '.$langshow.'</h5></a>'; 
 	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/images" class="list-group-item" target="_blank"> <h5 class="list-group-item-heading">'.$langfigs.' '.$langshow.'</h5></a>'; 
 	echo '</li></ul></nav></div>';
    
    // Navigation rechte Seite Ende + Container Ende
    echo '</div>';
           
	}

// Variablen für Module: 2. Chapteransicht
elseif($chapter!="") 
	{ 
	$nav="Chapter - ".$chapter."";

	// HTML-Kopf für Modul 2
	// TODO: als Funktion umsetzen und effizienter machen
	echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
	<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
	if($chapter!="all")
	{
	echo '<title>'.$chapter.' - '.$project.' - '.$version.'</title>';
	}
	elseif($chapter=="all")
	{
	echo '<title>'.$langvoll.' - '.$project.' - '.$version.'</title>';
	}
	
	echo '<link rel="canonical" href="http://'.$_SERVER['HTTP_HOST'].'/chapter/'.$chapter.'">
	<!-- Latest jquery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="../css/bootstrap.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
	<!-- custom  -->
	<link rel="stylesheet" href="../css/custom.css" media="screen">	
	<!-- Latest compiled and minified JavaScript -->
	<script src="../js/bootstrap.min.js"></script>
	<script>
	function setFontSize(size) {';
	echo "var body = document.getElementsByTagName('body');";
 	echo '
 	for(var i = 0; i < body.length; i++) {
  	if (body[i].style.fontSize) {
  	var s = parseInt(body[i].style.fontSize.replace("%",""));
  	} 
  	else {
   	var s = size;
  	}
  	body[i].style.fontSize = size + "%";
 	}
	}
	</script>
	<style type="text/css">
	.sheader
	#toc
	{
	border: 1px solid #bba;
	background-color: #f7f8ff;
	padding: 1em;
	font-size: 90%;
	text-align: center;
	width:15em;
	}

	#toc-header
	{
	display: inline;
	padding: 0;
	font-size: 100%;
	font-weight: bold;
	}

	#toc ul
	{
	list-style-type: none;
	margin-left: 0;
	padding-left: 0;
	text-align: left;
	}

	.toc1
	{
	font-weight: bold;
	font-size: 100%;
	}

	.toc2
	{
	margin-left: 0.5em;
	font-size: 90%;
	list-style-type: outside none square;
	}

	.toc3
	{
	margin-left: 2em;
	font-size: 80%;
	list-style-type: outside none circle;
	}

	.toc4
	{
	margin-left: 4em;
	font-size: 80%;
	list-style-type: none;
	}
	</style>
	</head><body>';
	echo $htmlhead;

 	// Container for Chapter
 	echo '<div class="articletools">
 	<p style="text-align:center; margin-top:5px;"><a href="../../">
 	<img class="home" src="../img/home.png"></a></p>
 	<a href="#top"><img class="toparrow" src="../img/arrow.svg"></a>';
 	
 	// Fontsize Desktop
 	echo '<p class="fontsizedesk" style="text-align:center; margin-top:5px;"><a class="articlefont1" href="';
	echo "javascript:setFontSize('120');";
	echo '">A</a>';
	echo '<a class="articlefont2" href="';
	echo "javascript:setFontSize('140');";
	echo '">A</a>'; 
	echo '<a class="articlefont3" href="';
	echo "javascript:setFontSize('160');";
	echo '">A</a></p>';

	// Fontsize mobile
	echo '<p class="fontsizemob" style="text-align:center; margin-top:5px; border-top: 2px solid #ffffff;"><a class="articlefont1" href="';
	echo "javascript:setFontSize('120');";
	echo '">A</a><br>';
	echo '<a class="articlefont2" href="';
	echo "javascript:setFontSize('140');";
	echo '">A</a><br>';
	echo '<a class="articlefont3" href="';
	echo "javascript:setFontSize('180');";
	echo '">A</a></p>';
  
	echo '</div>';
 	
 	
 	echo '<div class="container" style="padding-top:20px;">';
 	// Titel der Arbeit aus Repo auslesen
 	$title = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/title.md');
 	
	echo '<div role="main" class="col-md-9" style="padding-top:0px; word-wrap: break-word;">';
	echo '<div tabindex="-1" id="content" class="bs-docs-header" style="padding-bottom:10px;">
    <div class="container" style="width:100%;" >';
    echo '<h4><small>'.$project.'</small><br>';
    echo $title;
    echo '&nbsp;<span style="margin-top:5px" class="badge">&nbsp;Stand: ';
	echo $stand;
	echo '&nbsp;</span></h4><small>'.$autor.'</small></div></div>';
 	
 
	// Inhalt für jeweiliges $chapter aus Github auslesen
	if($chapter!="all")
	{
	echo '<ol class="breadcrumb">';
 	echo '<li><a href="/uebersicht">'.$langlinkmain.'';
  	echo '</a></li><li class="active">'.$chapter.'</li></ol>';
 
		// Inhalt für jeweiliges $chapter aus Github auslesen
		// check if available
		if(get_http_response_code('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/' . $chapter . '.tex') != "200"){
    	header("Location: /uebersicht?note=NotFound",TRUE,301);
    	exit;
		}
		else
		{
    	$contents = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/' . $chapter . '.tex');
		}
	 	$Parsedown = new Parsedown();
 		$content = $Parsedown->text($contents);
	}
	// gesamten Text anzeigen
	else
	{
	echo '<ol class="breadcrumb">';
 	echo '<li><a href="/uebersicht">'.$langlinkmain.'';
  	echo '</a></li><li class="active">'.$langvoll.'</li></ol>';
	
	$opt_words = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/layout.md');
	$pattern = explode('.tex', $opt_words);
	
	// Kapitel auslesen und Navigation bauen
	// TODO: als Funktion umsetzen und effizienter machen
	foreach ($pattern as $value) 
		{ 
		$trimmed = trim($value);
 		$content .= file_get_contents('data/'.$trimmed.'-data.txt');

		}

	
	}
 
	// Konvertierung von LaTex
	// TODO: als Funktion umsetzen und effizienter machen
    $todocount = substr_count($content, '---- TODO:');
 	$content = preg_replace('~\\---- TODO: (.*) ----~', '<p class="bg-warning" style="padding-left:10px; padding-right: 5px; font-style: italic; font-size:120%;"><br><b>Todo:</b> $1<br>&nbsp;</p>', $content);
 	$content = preg_replace('~\\\textbf{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '<strong>$1</strong>', $content);
 	$content = preg_replace('~\\\textit{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '<em>$1</em>', $content);
 	$content = preg_replace('~\\\begin{quote}~', '<blockquote>', $content);
	$content = preg_replace('~\\\end{quote}~', '</blockquote>', $content);
 	$content = preg_replace('~\\\end{abstract}~', '', $content);
 	$content = preg_replace('~\\\begin{abstract}~', '', $content);
 	$content = preg_replace('~\\\begin{itemize}~', '<ul>', $content);
 	$content = preg_replace('~\\\end{itemize}~', '</ul>', $content);
	$content = preg_replace('~\\\begin{enumerate}~', '<ol>', $content);
 	$content = preg_replace('~\\\end{enumerate}~', '</ol>', $content);
 	$content = preg_replace('~\\\item~', '</li><li>', $content);
 	$content = preg_replace('~\\\chapter{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '', $content);
 	$content = preg_replace('~\\\section{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '<h2>$1</h2>', $content);
 	$content = preg_replace('~\\\subsection{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '<h3>$1</h3>', $content);
 	$content = preg_replace('~\\\subsubsection{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '<h4>$1</h4>', $content);
 	$content = preg_replace('~\\\href{([^{}]*(?:{(?1)}[^{}]*)*+)}{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '', $content);
 	$content = preg_replace('~\\{http://([^{}]*(?:{(?1)}[^{}]*)*+)</a>}~', '<a href="http://$1" target="_blank">$1</a>', $content);
 	$content = preg_replace('~\\\cite[:(.*)]~', '\cite', $content);
 	$content = preg_replace('~cite\[:.*?\]~', 'cite', $content);
 	$content = preg_replace('~\\\cite{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '&#91;<a href="/quelle/$1">$1</a>&#93;', $content);
	
	$word = str_word_count(strip_tags($content));
	

	if($chapter!="all")
	{
	$myfile = fopen("data/".$chapter.".txt", "w") or die("Unable to open file - Check Permissions for Data-Folder!");
	$txt = $word;
	fwrite($myfile, $txt);
	fclose($myfile);
	
	$myfile3 = fopen("data/".$chapter."-todo.txt", "w") or die("Unable to open file - Check Permissions for Data-Folder!");
	$todocounttxt = $todocount;
	fwrite($myfile3, $todocounttxt);
	fclose($myfile3);

	$myfile2 = fopen("data/".$chapter."-data.txt", "w") or die("Unable to open file - Check Permissions for Data-Folder!");
	$txt2 = "<h1 class='page-header' style='padding-top:0px;'>".$langchap.": ".$chapter."</h1>";
	$txt2 .= $content;
	fwrite($myfile2, $txt2);
	fclose($myfile2);
	
	echo "<h1 class='page-header' style='padding-top:0px;'>Kapitel: ".$chapter." <small>(".$word." ".$langwords." / ".$todocount." ".$langtodos.")</small></h1>";
	echo '<div>';
	echo $content;
	echo "</div>";
	}
	else
	{
	echo "<h1 class='page-header' style='padding-top:0px;'>".$langvoll."</h1>";
	
	TableOfContents(4,$content);
	
	echo '<div>';
	$content = preg_replace('/<h1[^>]*?>(.*?)<\/h1>/', '<a id="$1"></a><h1>$1</h1>', $content);
	$content = str_replace('<a id="Kapitel: ', '<a id="', $content);
	echo $content;
	echo "</div>";
	

	}
	
	
 	
 	
 	// Ende main
 	echo '</div>';
	
	// Navigation rechte Seite
	echo '<div role="complementary" class="col-md-3"><nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm affix">';
    $opt_words = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/layout.md');
	$pattern = explode('.tex', $opt_words);
 	echo '<label>'.$langchoosechap .'</label><div class="list-group">';
	
	// Kapitel auslesen und Navigation bauen
	// TODO: als Funktion umsetzen und effizienter machen
	foreach ($pattern as $value) 
		{ 
		$trimmed = trim($value);
		if ($trimmed=="")
			{ 
			}
		elseif($trimmed==$chapter)
			{  
			echo '<a href="/chapter/'.$trimmed.'#top" class="list-group-item active">
    		<h5 class="list-group-item-heading ">'.$trimmed.'</h5>';
 			echo '</a>'; 
 			}
 		else
 			{ 
 		echo '<a href="/chapter/'.$trimmed.'#top" class="list-group-item">
    	<h5 class="list-group-item-heading ">'.$trimmed.'</h5>';
 		echo '</a>'; 
  			}
		}
  	echo '</div><label>'.$langrefs.' / '.$langsdata.'</label><div class="list-group">';
	echo '<a href="/literature/all#top" class="list-group-item"> <h5 class="list-group-item-heading">'.$langshowrefs.'</h5></a>'; 
  	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" class="list-group-item" target="_blank"> 
  	<h5 class="list-group-item-heading">'.$langshowdata.'</h5></a>
  	<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/images" class="list-group-item" target="_blank"> <h5 class="list-group-item-heading">'.$langshowfigs.'</h5></a>
 	</div>'; 
 	
	
    echo '</nav></div>';
    // Navigation rechte Seite Ende
    echo '</div>';
 	// Container for Chapter Ende
	}
// Variablen für Module: 3. Kapitelübersicht
else
	{ 
	$title = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/title.md');
	
	// HTML-Kopf für Modul 3
	// TODO: als Funktion umsetzen und effizienter machen
	echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
	<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	<title>'.$title.'</title>
  	<!-- Latest jquery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.js"></script>
	<link rel="canonical" href="http://'.$_SERVER['HTTP_HOST'].'/uebersicht">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
	<!-- custom  -->
	<link rel="stylesheet" href="../css/custom.css">
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	</head>
	<body>';
	echo $htmlhead;
	echo '<div class="container" style="padding-top:0px;">
	<div style="padding-bottom:0px;">';

	// Dokument nicht Verfügbar
	if($note=="NotFound")
	{
	echo '<br><br><div class="alert alert-danger" role="alert">'.$notfound.'</div>';
	}
	else
	{
	}

	echo '<h2 style="font-size:26px;"><small style="font-size:14px; line-height:25px;">'.$project.'</small><br>'.$title.'';
	echo '';
	echo '&nbsp;<span style="margin-top:5px" class="badge">&nbsp;'.$langstatus.': ';
	
	// Aktuellen Stand ausgeben via letzten Commit
	echo $stand;
	echo '&nbsp;</span></h2><small>'.$autor.'</small>';	
	echo '<p class="lead"><small>'.$description.' [<a href="'.$mehrinfolink.'" target="_top">'.$moreinfolinktext.'</a>]</small></p></div>';
	
	
	// Ansichtoption und Links zu Github und Authorea
	echo '<h4>'.$langview .'</h4>'; 
	echo '<p style="padding-bottom:0px; padding-left:20px; line-height:25px;"><button style="margin-bottom:5px" type="button" class="btn btn-primary btn-xs active">Live ('.$version.'';
	echo ')</button> <a style="margin-bottom:5px" href="https://www.authorea.com/users/'.$ausername.'/articles/'.$aarticleid.'/_show_article" target="_blank" class="btn btn-default btn-xs">'.$langtext.' @ Authorea</a> 
	<a style="margin-bottom:5px" href="https://github.com/'.$username.'/'.$repo.'" class="btn btn-default btn-xs" target="_blank">'.$langtext.' & '.$langsdata.' @ GitHub</a> 
	</p>';
	
	
	// DOI und Altmetrics
	if($Badge!="" or $DOI!="")
	{
	echo "<script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>";
	echo '<h4>DOI / Altmetrics <small>Status: '.$version.'</small></h4>'; 
	echo '<p style="float:left; padding-bottom:0px; margin-right:5px; padding-left:20px; line-height:25px;">'.$Badge.'
	</p><div style="padding-bottom:0px; padding-top:0px; padding-left:20px; line-height:25px; data-badge-details="right" data-badge-type="1" data-doi="'.$DOI.'" class="altmetric-embed"></div>';
	}
	else
	{ 
	}
	
	// Naviagion auf Main
	// TODO: als Funktion umsetzen und effizienter machen
	echo '<h4>'.$langchoosechap.' / '.$langvoll.'</h4>'; 
	$opt_words = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/layout.md');
	$pattern = explode('.tex', $opt_words);
	$n=0;
 	echo '<div class="btn-toolbar" style="padding-left:20px;" role="toolbar">';
	foreach ($pattern as $value) 
		{ 
		$trimmed = trim($value);
		if ($trimmed=="")
			{ 
			}
		else
			{  
			echo '<a href="/chapter/'.$trimmed.'#top" style="padding-top:5px;margin-bottom:5px;" class="btn btn-default btn-sm" role="button">'.$n.'. '.$trimmed.'</a>';
 			$n=$n+1;
			
			
			$myfile = fopen("data/".$trimmed."-todo.txt", "r") or die("Unable to create Todos - check permissions of Data Folder!");
			$todocount = fgets($myfile); 
			fwrite($myfile, $todocount);
			fclose($myfile);
			$countalltodo=$countalltodo+$todocount;
			
			$myfile = fopen("data/".$trimmed.".txt", "r") or die("Unable to create Counter - check permissions of Data Folder!");
			$count = fgets($myfile); 
			fwrite($myfile, $count);
			fclose($myfile);
			$countall=$countall+$count;
 			

 			
 			}
 		}
	echo '</div>';
	echo '<div class="btn-toolbar" style="padding-left:20px;" role="toolbar">';
	echo '<a href="/chapter/all#top" style="padding-top:5px;margin-bottom:5px;" class="btn btn-default btn-sm" role="button">'.$langvoll.'</a>';
 	echo '</div>';		

	echo '<h4>'.$langrefs.' / '.$langsdata.'</h4>'; 
	echo '<div class="btn-toolbar" style="padding-left:20px;" role="toolbar">';
	echo '<a href="/literature/all#top" style="margin-bottom:5px;" class="btn btn-default btn-sm" role="button">'.$langshowrefs.'</a>';
	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" target="_blank" style="margin-bottom:5px;" class="btn btn-default btn-sm" role="button">'.$langshowdata.'</a>';
	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" target="_blank" style="margin-bottom:5px;" class="btn btn-default btn-sm" role="button">'.$langshowfigs.'</a>';
	echo '</div>';

	
	// Commits von "get latest Commits and Status of work" anzeigen
	
	if($ccount>3)
	{
	echo '<h4>'.$langlastchagnes.'</h4>'; 
	echo "<ul>";
	echo $houtput; 
	echo "</ul>";
	}
	
	else
	{	
	}
	
	// Status
	
	echo '<h4>Status</h4>'; 
	echo '<div style="padding-left:20px;">';
	?>
	<p><b>Version:</b> <?php echo $version; ?><br><b><?php echo $langstatus; ?>:</b> <?php echo $stand; ?><br><b><?php echo $langextent; ?>:</b> <?php echo $countall; ?> <?php echo $langwords; ?> / <?php  
	$seiten=$countall/315; 
	echo round($seiten+$abweichung); 
	echo " ".$langpages."";
	?></p>
	<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress; ?>%;">
    <?php echo $progress; ?>%  (<?php 
    echo $langstill;
    echo $countalltodo;
    echo " ".$langtodos.""; 
    ?> )
  	</div>
	</div>

	<?php
	// Ende Container
	echo '</div>';
	echo '</div>';
}

// Ende initiale If-Schleife

// Footer laden
echo $footer;

echo '<!-- Piwik Image Tracker-->
<img src="'.$piwikurl.'&amp;action_name='.$nav.'&amp;url='.$current_url.'&amp;urlref='.$HREFERER.'" style="border:0" alt="" />
<!-- End Piwik -->
</body></html>';

// Cache Ende

if($cache=="true")
	{
	// Cache the contents to a file
	$cached = fopen($cachefile, 'w');
	fwrite($cached, ob_get_contents());
	fclose($cached);
	ob_end_flush(); // Send the output to the browser
	}
else
	{
	echo "<!-- Cache deactivated -->\n";
	}
?>
