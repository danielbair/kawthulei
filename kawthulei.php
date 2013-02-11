<?php
/*
Plugin Name: Hello Kawthulei Po!
Plugin URI: http://kanyawtech.org
Description: This plugin allows you to use Sgaw Karen Unicode text on your wordpress site.
Version: 1.0
Author: Ben Sharon
Author URI: http://kanyawtech.org
License: GPL
*/

/***********************************************************************
 * Class containing basic constants, set arrays, and helper functions
 * related to Myanmar language and characters
 **********************************************************************/

class hkp_MyRefClass {
	public $langugeCodes = array( // All language codes that could potentially use the Myanmar script. Note: some frequently use other scripts to write as well (such as Thai, Latin, etc.)
		'my',	// ISO 639-1
		'bur',	//	ISO 639-2
		'mya',	//	ISO 639-2
		'mya',	//	ISO 639-3
		'obr',	//	Old Burmese
		'sanmymr', 'sanmya', 'sanmy', 'sanbur',	//	Sanskrit as used in Burmese Buddhist literature
		'plimymr', 'plimya', 'plimy', 'plibur',	//	Pali as used in Burmese Buddhist literature
		'mnw', 'omx',	//	Mon
		'ksw', 'kar',	//	Sgaw Karen (Note: some Karen's in both Myanmar and Thailand (usually Catholics, I think) write their language using a Latin script. I have seen books, and movie subtitles in this script. Also, there are some in Thailand who use a subset of the Thai script.)
		'pwo',	// 	Western Pwo Karen
		'kjp',	//	Eastern Pwo Karen
		'blk',	//	Pa'o Karen
		'kyu', 'eky',	// Kayah
		'bwe',	//	Bwe Karen
		'kvy',	//	Yintale Karen (actually not a written language, but if they did write it, it'd probably be with the Myanmar script)
		'kxf',	//	Manumanaw (Manu)
		'csh',	//	Asho Chin
		'shn',	//	Shan (modern Shan script)
		'kht',	//	Khamti Shan
		'aio',	//	Aiton
		'phk',	//	Phake
		'rbb', 'pll', 'pce',	//	Rumai Palaung
		'jkp',	//	Paku Karen
		'jkm',	//	Mobwa Karen
		'wea',	//	Wewaw
	);

	public $zeroWidthSpace = ''; // empty here, add it in the constructor
	public $zeroWidthNonJoiner = ''; // empty here, add it in the constructor
	public $allMyanmarCharacters = array(); // same with this one
	public $specialMyanmarPhraseCharacters = array(",", ".", " "); // these are characters that are not specifically myanmar, but can be used in a myanmar phrase
	public $openingParentheses = array("(", "[", "{");
	public $closingParentheses = array(")", "]", "}");
	public $karenConsonants = array("က", "ခ", "ဂ", "ဃ", "င", "စ", "ဆ", "ၡ", "ဇ", "ည", "တ", "ထ", "ဒ", "န", "ပ", "ဖ", "ဘ", "မ", "ယ", "ရ", "လ", "ဝ", "သ", "ဟ", "အ", "ဧ");

	public function MyRefClass() { // the constructor
		// first, fill up the $allMyanmarCharacters array
		for ($i=4096; $i<=4256; $i++)
			 array_push( $this->allMyanmarCharacters , $this->my_unichr($i) );
		for ($i=43616; $i<=43644; $i++)
			 array_push( $this->allMyanmarCharacters , $this->my_unichr($i) );

		// second, assign the line breaking character
		$u200b = "\u200b";
		$this->zeroWidthSpace = json_decode('"'.$u200b.'"');
		$u200c = "\u200c";
		$this->zeroWidthNonJoiner = json_decode('"'.$u200c.'"');
//		return $this; // do we need this or not?
	}
	
	
	public function my_unichr($u) { // converts a decimal integer to unicode character
		return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
	}

	public function getLineBreakChar() {
		return $this->zeroWidthSpace;
	}

	public function languageIsMyanmar() { // this method could be expanded to check what language wordpress is set too as well (this only checks WPML's language)
		if ( isset($ICL_LANGUAGE_CODE) ) {
			$current_language_code = str_replace('-' , '' , strtolower( $ICL_LANGUAGE_CODE ) );
			if ( in_array($current_language_code, $this->langugeCodes ) )
				return false;
		}
		return true;
	}
	
	public function isMyanmarCharacter( $char ) {
		return in_array( $char, $this->allMyanmarCharacters);
	}

	public function isOpeningParentheses( $char ) {
		return in_array( $char, $this->openingParentheses );
	}

	public function isClosingParentheses( $char ) {
		return in_array( $char, $this->closingParentheses );
	}
	
	public function isKarenConsonant( $char ) {
		return in_array( $char, $this->karenConsonants );
	}

	public function isMyanmarPhraseCharacter( $char ) {
		if ($this->isMyanmarCharacter($char) or
					in_array( $char, $this->specialMyanmarPhraseCharacters) or
					$char == $this->getLineBreakChar() or
					$char == $this->zeroWidthNonJoiner)
			return true;
		return false;
	}
};

$hkp_myref = new hkp_MyRefClass();

/***********************************************************************
 * CSS and JS
 **********************************************************************/
add_action( 'wp_enqueue_scripts', 'addMyStyleSheets', 100 );
function addMyStyleSheets() {
	global $hkp_myref;
	wp_register_style( 'myStyles', plugins_url('css/myStyles.css', __FILE__) );
	wp_enqueue_style( 'myStyles' );

	if ( $hkp_myref->languageIsMyanmar() ) {
	   wp_register_script('myImageConversion', plugins_url('/js/myImageConversion.js', __FILE__) );
	   wp_enqueue_script( 'myImageConversion' );
	}
}

if ( wp_get_theme() == 'CyberChimps Pro Starter Theme' ) {
	add_action( 'cyberchimps_header', 'addNoScript', 1 );
} else {
	add_action( 'wp_head', 'addNoScript', 100 );
}
function addNoScript() { // this is added inside the head html tags... (codex.wordpress.org/Plugin_API/Action_Reference/wp_head) We need one after the banner but before the menu bar. this type of thing is theme specific, I believe. And the theme may or may not have a hook there. they're easy to add though... http://archive.extralogical.net/2007/06/wphooks/
	// I found a solution for the inserting the div tag after the body tag using jquery in wp_footer below. This function is only inserting the noscript tag for those who have javascript turned off to alert them about the necessity of running javascript on this website.
	echo "<noscript>"."\n"
	   . "   <div class=\"myUnsupportedMessage\" id=\"topUnsupportedMessage\" style=\"display: block;\">"."\n"
	   . "      <p><em class=\"warning\">Warning!</em><br>Your setup does not support Javascript. Please enable Javascript in your browser or use a browser that supports Javascript to allow displaying Karen text properly.</p>"."\n"
	   . "   </div>"."\n"
	   . "</noscript>"."\n";
}


add_action( 'wp_footer' , 'addMyJavascripts', 1 );
function addMyJavascripts() {
	global $hkp_myref;
	if ( $hkp_myref->languageIsMyanmar() ) {
	  echo '<div id="myUniTest" style="display: none;">'."\n"
	     . '   <span class="myUniTest" id="myTestAWidth1">က္က</span>'."\n"
	     . '   <span class="myUniTest" id="myTestAWidth2">ကက</span>'."\n"
	     . '   <span class="myUniTest" id="myTestBWidth1">က</span>'."\n"
	     . '   <span class="myUniTest" id="myTestBWidth2">ကူ</span>'."\n"
	     . '   <span class="myUniTest" id="myTestC">ကၢ်</span>'."\n"
	     . '   <span class="myUniTest" id="myTestD">ကၣ်</span>'."\n"
	     . '</div>'."\n"
	     . '<div class="myUnsupportedMessage" id="bottomUnsupportedMessage"></div>'."\n"
	     . '<script type="text/javascript">'."\n"
	     . '   var myUnicode = new TlsMyUnicode();'."\n"
	     . '   setTimeout(function(){myUnicode.main("' . plugins_url('/js/', __FILE__) . '")},100);'."\n"
	     . '   jQuery(document).ready( function($) {'."\n"
	     . "      $('body').prepend('<div class=\"myUnsupportedMessage\" id=\"topUnsupportedMessage\"></div>');"."\n"
	     . '   } );'."\n"
	     . '</script>'."\n";
	}
}


/**************************
* Post / page content hooks
**************************/
add_filter( 'the_content', 'addMyTextClassSpans');
// applied to the post content retrieved from the database, prior to printing on the screen (also used in some other operations, such as trackbacks).
// applies to search results too

add_filter( 'the_content_feed', 'addMyTextClassSpans');
// applied to the post content prior to including in an RSS feed.

// Not sure if it's a good idea to include this or not. without it, content will not wrap properly in the editing window (and then they'll do what they always do on Word, and type enter at the end of each line. Not good.) this comment applies to the other similar edit_pre hooks below too. It almost makes sense not to strip them out to start with. But I still think it's a good idea because a. it's the 'proper' canonical way to store Myanmar b. since their invisible, with lots of editing those U+200b's are going to be moving all over the place, this keeps them under control, and c. it will help search work properly (we need to make sure to strip them from the search query too)
add_filter( 'content_edit_pre', 'insert200B');
// applied to post excerpt prior to display for editing.

add_filter( 'content_save_pre', 'strip200B');
// applied to post content prior to saving it in the database (also used for attachments).


/**************************
* Menu/widget content hooks
**************************/
add_filter( 'wp_nav_menu', 'addMyTextClassSpans');
// applied to the menu name retrieved from the database, prior to printing on the screen.

add_filter( 'wp_list_pages', 'addMyTextClassSpans');
// applied to the menu name retrieved from the database, prior to printing on the screen.

add_filter( 'widget_content', 'addMyTextClassSpans');
// applied to the widget text retrieved from the database, prior to printing on the screen.


/**************************
* Content excerpt hooks
**************************/
add_filter( 'the_excerpt', 'addMyTextClassSpans');
// applied to the post excerpt (or post content, if there is no excerpt) retrieved from the database, prior to printing on the screen (also used in some other operations, such as trackbacks).

add_filter( 'the_excerpt_rss', 'addMyTextClassSpans');
// applied to the post excerpt prior to including in an RSS feed.

add_filter( 'excerpt_edit_pre', 'insert200B');
// applied to post excerpt prior to display for editing.

add_filter( 'excerpt_save_pre', 'strip200B');
// applied to post excerpt prior to saving it in the database (also used for attachments).


/**************************
* Comment hooks
**************************/
add_filter( 'comment_text', 'addMyTextClassSpans');
// applied to the comment text before displaying on the screen by the comment_text function, and in the admin menus.

add_filter( 'comment_text_rss', 'addMyTextClassSpans');
// applied to the comment text prior to including in an RSS feed.

add_filter( 'pre_comment_content', 'strip200B');
// applied to the content of a comment prior to saving the comment in the database.


/**************************
* Title hooks
**************************/
/*
 * Here is a workaround for the title issue.
 * Every where in the content.php, and content-page.php theme files, <?php the_title(); ?> needs to be relaced with <?php do_action( 'the_html_safe_title' ); //the_title(); ?>
*/
add_filter( 'the_html_safe_title', 'addMyTextClassSpansToPostTitles' );
function addMyTextClassSpansToPostTitles() {
	global $post;
	echo addMyTextClassSpans($post->post_title);
}

/*
add_filter( 'the_title_rss', 'addMyTextClassSpans');
// applied to the post title before including in an RSS feed (after first filtering with the_title.
// need to check this, I think it's not possible to format rss feed titles with html, it will display as text, I think.
*/
add_filter( 'title_edit_pre', 'insert200B');
// applied to post title prior to display for editing.

add_filter( 'title_save_pre', 'strip200B');
// applied to post title prior to saving it in the database (also used for attachments).

//add_filter( 'wp_title', 'addMyTextClassSpans'); // This is the title tag in the head, html in there isn't rendered on my Firefox.
// applied to the blog page title before sending to the browser in the wp_title function.

add_filter( 'widget_title', 'addMyTextClassSpans');

/***********************************************************************
 * Callback Functions
 **********************************************************************/
function addMyTextClassSpans($content) {
	global $hkp_myref;
	
	$inTag = false; $inMyanmarRun = false; $outputText = ''; $lastChar = '';
	$inputTextArray = preg_split("//u", $content, -1, PREG_SPLIT_NO_EMPTY); // this splits it up into an array of characters. someone helped me with it on a forum. anything else runs into unicode encoding issues making it so that unicode characters can't be compared ('က' == 'က' would be false)
	foreach($inputTextArray as $char) {
		if ( (!$inTag and $lastChar == '<' and ctype_alnum($char)) or ($inTag and $char == '>') )
			$inTag = !$inTag;
		if (!$inTag and !$inMyanmarRun and $hkp_myref->isMyanmarCharacter($char)) {
			$outputText .= "<span class='myText'>" . $char;
 			$inMyanmarRun = true;
		} elseif ($inMyanmarRun and !$hkp_myref->isMyanmarPhraseCharacter($char)) {
			$outputText .= "</span>" . $char;
 			$inMyanmarRun = false;
		} else
			$outputText .= $char;
		$lastChar = $char;
	}
	if ($inMyanmarRun) $outputText .= "</span>"; // line ends with a Myanmar Character so the closing tag didn't get inserted in the loop.
	return insert200B($outputText); // insert line breaking character before sending it to the browser (the callbacks don't otherwise ask for this)
}

function strip200B($content) { // use this function to clean things up
	global $hkp_myref;
	$content = str_replace( $hkp_myref->getLineBreakChar() , '' , $content );
	return $content;
}

function insert200B( $inputText ) {
	global $hkp_myref;

	

	$outputText = ''; $lastChar = '';
	$inputTextArray = preg_split("//u", strip200B($inputText), -1, PREG_SPLIT_NO_EMPTY);
	foreach($inputTextArray as $char) {
		if ($hkp_myref->isKarenConsonant($char) and ($hkp_myref->isMyanmarCharacter($lastChar) or $hkp_myref->isOpeningParentheses($lastChar)))
			$outputText .= $hkp_myref->getLineBreakChar() . $char;
		else if ($hkp_myref->isOpeningParentheses($lastChar) and $hkp_myref->isMyanmarCharacter($lastChar) )
			$outputText .= $hkp_myref->getLineBreakChar() . $char;
		else
			$outputText .= $char;
		$lastChar = $char;
	}
	return $outputText;
}

?>
