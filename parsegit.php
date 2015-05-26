<!DOCTYPE html> 
<?php 
/**
 * parsegit.php
 * Github LaTex-Reader
 * 2015 
 * Version: alpha
 * Christian Heise
 * CC-BY-SA
 */

// Config
$project = ''; // Projektname
$description = ''; // Beschreibung
$mehrinfolink = ''; // more Infolink
$kontakturl = ''; // Contact URL
$version = "";  // Version of the Document
$progress = ''; // Progress of the Document
$cache = 'true'; // Progress of the Document
$cachetimeday = 300; // Progress of the Document in Seconds
$cachetimenight = 1800; // Progress of the Document in Seconds

$username = ''; // GitHub User
$repo = ''; // GitHub Repository
$ausername = ''; // Authorea Username
$aarticleid = '';  // Authorea Article ID

$piwikurl = ''; // Tracker Image

// Define $GET Variables
if (isset($_GET['bib'])) { $bibtex = $_GET['bib']; }  else  { $bib=""; } 
if (isset($_GET['bibtex'])) { $bibtex = $_GET['bibtex']; }  else  { $bibtex=""; } 
if (isset($_GET['chapter'])) { $chapter = $_GET['chapter']; }  else  { $chapter=""; } 
if (isset($_GET['bibtexentry'])) { $bibtexentry = $_GET['bibtexentry']; } else  { $bibtexentry=""; } 
$current_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
date_default_timezone_set("Germany/Berlin");
$now = date('G');

// Cache

if($cache=="true")
{

// Tages- und Nachcaching 
// TODO: als Funktion umsetzen und effizienter machen

if ($now > 10 && $now < 23) 
	{
    $cmodus="day";
	} else {
    $cmodus="night";
	}
		
		if ($cmodus=="day"){
    	$cachetime = $cachetimeday;
    	}
		else
    	{
    	$cachetime = $cachetimenight;
    	}

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
			$houtput .=  ' <a href = "https://github.com/'.$username.'/'.$repo.'/commit/'.$commit['sha'].'" target="_blank">'.$commit['commit']['message'].'</a> am ';
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
$footer='<div style="text-align:center; margin-left:10px; padding-top:0px; max-width:100%">&nbsp;<br><small><b>Daten:</b> Inhalt und Entstehungsprozess der Arbeit kann auf <a href="https://www.authorea.com/users/'.$ausername.'/articles/'.$aarticleid.'/_show_article" target="_blank">Authorea</a> und auf <a href="https://github.com/christianheise/'.$repo.'" target="_blank">GitHub</a> eingesehen werden.<br> 
<b>Lizenz:</b> Der gesamte Inhalt steht unter <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.de" target="_blank">Creative Commons (CC BY-SA 3.0)</a>.
<br><b>Reader-Version:</b> 0.'.date("Ym").' | <b>Quellcode(Reader):</b> <a title="Code of this Reader" target="_blank" href="https://github.com/christianheise/authorea-github-tex-reader">hier</a> | <a title="offene-doktorarbeit.de" target="_top" href="'.$kontakturl.'">Weitere Informationen & Kontakt</a><br>
</small><br>&nbsp;</div>
<!-- Piwik Image Tracker-->
<img src="'.$piwikurl.'&amp;action_name='.$nav.'&amp;url='.$current_url.'&amp;urlref='.$HREFERER.'" style="border:0" alt="" />
<!-- End Piwik -->
</body></html>';

$header='';


// Start Module: 1 - Bibtexbrowsereinbindung

if ($bibtex=="all")
	{ 
	$nav="Literaturangaben";

	// HTML-Kopf für Modul 1
	// TODO: als Funktion umsetzen und effizienter machen
	echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
	<head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  	<title>Literaturangaben</title>
	<link rel="canonical" href="http://'.$_SERVER['HTTP_HOST'].'/literatur/all">
	<!-- Latest jquery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="../css/bootstrap.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="../css/bootstrap-theme.min.css">
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
	
	// Container Anfang
	echo '<a name="top"></a><div class="container" style="padding-top:40px;">
	<div role="main" class="col-md-9"><ol class="breadcrumb"><li><a href="/uebersicht">Übersicht</a></li><li class="active">Referenzen</li>
	</ol><h1 class="page-header">Literaturangaben <small>(<a href="https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/bibliography/biblio.bib" target="_blank">komplette Bibtex-Datei anzeigen</a>)</small></h1>';
	
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

 	echo '<label>Kapitel auswählen</label><div class="list-group">';
	
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
	echo '</div><label>Referenzen / Daten</label><div class="list-group">';
 	echo '<a href="/literatur/all" class="list-group-item active"> <h5 class="list-group-item-heading">Literaturliste anzeigen</h5></a>'; 
  	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" class="list-group-item" target="_blank"> <h5 class="list-group-item-heading">Daten anzeigen</h5></a>'; 
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
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>'.$chapter.'</title>
	<link rel="canonical" href="http://'.$_SERVER['HTTP_HOST'].'/chapter/'.$chapter.'">
	<!-- Latest jquery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="../css/bootstrap.css">
	<!-- Optional theme -->
	<link rel="stylesheet" href="../css/bootstrap-theme.min.css">	
	<!-- Latest compiled and minified JavaScript -->
	<script src="../js/bootstrap.min.js"></script>
	</head><body>';

 	// Container for Chapter
 	
 	echo '<div class="container" style="padding-top:20px;">';
 	// Titel der Arbeit aus Repo auslesen
 	$title = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/title.md');
 	echo '<div tabindex="-1" id="content" class="bs-docs-header" style="padding-bottom:10px;">
    <div class="container" style="width:100%;" >
    <h4 id="top"><small>'.$project.'</small><br>';
    echo $title;
    echo '&nbsp;<span style="margin-top:5px" class="badge">&nbsp;Stand: ';
	echo $stand;
	echo '&nbsp;</span></h4></div></div>';
	echo '<div role="main" class="col-md-9" style="padding-top:5px;">';
 	echo '<ol class="breadcrumb">';
 	echo '<li><a href="/uebersicht">&Uuml;bersicht';
  	echo '</a></li><li class="active">'.$chapter.'</li></ol>';
 	echo "<h1 class='page-header' style='padding-top:0px;'>Kapitel: ".$chapter."</h1>";
 
	// Inhalt für jeweiliges $chapter aus Github auslesen

 	$contents = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/' . $chapter . '.tex');
 	$Parsedown = new Parsedown();
 	$content = $Parsedown->text($contents);
 
	// Konvertierung von LaTex
	// TODO: als Funktion umsetzen und effizienter machen

 	$content = preg_replace('~\\---- TODO: (.*) ----~', '<p class="bg-warning" style="padding-left:10px;"><br><b>Todo:</b> $1<br>&nbsp;</p>', $content);
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
 	$content = preg_replace('~\\\cite{([^{}]*(?:{(?1)}[^{}]*)*+)}~', '&#91;<a href="/quelle/$1">$1</a>&#93;', $content);
	echo '<div>';
	echo $content;
	echo "</div>";
 	$word = str_word_count($content);
 	
 	// Ende main
 	echo '</div>';
	
	// Navigation rechte Seite
	echo '<div role="complementary" class="col-md-3"><nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm affix">';
    $opt_words = file_get_contents('https://raw.githubusercontent.com/'.$username.'/'.$repo.'/master/layout.md');
	$pattern = explode('.tex', $opt_words);
 	echo '<label>Kapitel auswählen</label><div class="list-group">';
	
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
  	echo '</div><label>Referenzen / Daten</label><div class="list-group">';
	echo '<a href="/literatur/all#top" class="list-group-item"> <h5 class="list-group-item-heading">Literaturliste anzeigen</h5></a>'; 
  	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" class="list-group-item" target="_blank"> 
  	<h5 class="list-group-item-heading">Daten anzeigen</h5></a></div>'; 
	echo '</nav></div>';
    // Navigation rechte Seite Ende
    echo '</div>';
 	// Container for Chapter Ende
	}
// Variablen für Module: 3. Kapitelübersicht
else
	{ 
 	$nav="Kapiteluebersicht";
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
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
	</head>
	<body><div class="container" style="padding-top:0px;">
	<div style="padding-bottom:0px;">';

	echo '<h2 style="font-size:26px;"><small style="font-size:14px; line-height:25px;">'.$project.'</small><br>'.$title.'';
	echo '';
	echo '&nbsp;<span style="margin-top:5px" class="badge">&nbsp;Stand: ';
	
	// Aktuellen Stand ausgeben via letzten Commit
	echo $stand;
	echo '&nbsp;</span></h2>';
	echo '<p class="lead"><small>'.$description.' [<a href="'.$mehrinfolink.'" target="_top">mehr Informationen über dieses Vorhaben...</a>]</small></p></div>';
	
	// Ansichtoption und Links zu Github und Authorea
	echo '<h4>Ansicht</h4>'; 
	echo '<p style="padding-bottom:0px; padding-left:20px; line-height:40px;"><button style="margin-bottom:5px" type="button" class="btn btn-primary btn-xs active">Liveansicht (Working Draft';
	echo ')</button> <a style="margin-bottom:5px" href="https://www.authorea.com/users/'.$ausername.'/articles/'.$aarticleid.'/_show_article" class="btn btn-default btn-xs">Orginaldokument auf Authorea</a> 
	<a style="margin-bottom:5px" href="https://github.com/'.$username.'/'.$repo.'" class="btn btn-default btn-xs" target="_blank">Text und Daten auf GitHub</a>
	</p>';
	
	// Naviagion auf Main
	// TODO: als Funktion umsetzen und effizienter machen
	echo '<h4>Kapitel auswählen</h4>'; 
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
 			}
 		}
	echo '</div>';

	echo '<h4>Referenzen / Daten</h4>'; 
	echo '<div class="btn-toolbar" style="padding-left:20px;" role="toolbar">';
	echo '<a href="/literatur/all#top" style="margin-bottom:5px;" class="btn btn-default btn-sm" role="button">Literaturliste anzeigen</a>';
	echo '<a href="https://github.com/'.$username.'/'.$repo.'/tree/master/data" target="_blank" style="margin-bottom:5px;" class="btn btn-default btn-sm" role="button">Daten auf GitHub anzeigen</a>';
	echo '</div>';

	
	// Commits von "get latest Commits and Status of work" anzeigen
	
	if($ccount>3)
	{
	echo '<h4>Letzte 5 Änderungen</h4>'; 
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
	<p><b>Version:</b> <?php echo $version; ?><br><b>Stand:</b> <?php echo $stand; ?></p>
	<div class="progress">
  	<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress; ?>%;">
    <?php echo $progress; ?>%
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
	echo "<!-- Cached deactivated -->\n";
	}
?>
