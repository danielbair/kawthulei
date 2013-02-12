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

function hkp_languageIsMyanmar() { // this function should be expanded to check what language wordpress is set too, and what language any other translation plugins are set to as well (for now, it only checks WPML's language)
	// This function needs to testing to ensure that it's working. I don't have WPML installed on my local wordpress
	$langugeCodes = array( // All language codes that could potentially use the Myanmar script. Note: some frequently use other scripts to write as well (such as Thai, Latin, etc.)
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
	
	global $ICL_LANGUAGE_CODE;
	if ( isset($ICL_LANGUAGE_CODE) ) {
		$current_language_code = str_replace('-' , '' , strtolower( $ICL_LANGUAGE_CODE ) );
		if ( in_array($current_language_code, $langugeCodes ) )
			return true;
		else
			return false;
	} else
		return true;
}

/***********************************************************************
 * CSS and JS
 **********************************************************************/
add_action( 'wp_enqueue_scripts', 'hkp_addMyStyleSheet', 100 );
function hkp_addMyStyleSheet() {
	wp_register_style( 'myStyles', plugins_url('css/myStyles.css', __FILE__) );
	wp_enqueue_style( 'myStyles' );
}

if ( hkp_languageIsMyanmar() ) {
	add_action( 'wp_enqueue_scripts', 'hkp_addMyJSIncludes', 100 );
	add_action( 'cyberchimps_after_navigation', 'hkp_topUnsupportedMessage', 1 ); // This line is theme specific
	add_action( 'cyberchimps_before_footer_container', 'hkp_bottomUnsupportedMessage', 1 ); // This line is theme specific
	add_action( 'wp_footer' , 'hkp_addMyCompatibilityTest', 1 );
}

function hkp_addMyJSIncludes() {
	wp_register_script('myImageConversion', plugins_url('/js/myImageConversion.js', __FILE__) );
	wp_enqueue_script( 'myImageConversion' );
}

function hkp_topUnsupportedMessage() {
	echo "<div class=\"myUnsupportedMessage\" id=\"topUnsupportedMessage\"></div>"."\n"
		. "<noscript>"."\n"
		. "   <div class=\"myUnsupportedMessage\" style=\"display: block;\">"."\n"
		. "      <p><em class=\"warning\">Warning!</em><br>Your setup does not support Javascript. Please enable Javascript in your browser or use a browser that supports Javascript to allow displaying Karen text properly.</p>"."\n"
		. "   </div>"."\n"
		. "</noscript>"."\n";
}

function hkp_bottomUnsupportedMessage() {
	echo '<div class="myUnsupportedMessage" id="bottomUnsupportedMessage"></div>';
}

function hkp_addMyCompatibilityTest() {
	if ( hkp_languageIsMyanmar() ) {
	  echo '<div id="myUniTest" style="display: none;">'."\n"
	     . '   <span class="myUniTest" id="myTestAWidth1">က္က</span>'."\n"
	     . '   <span class="myUniTest" id="myTestAWidth2">ကက</span>'."\n"
	     . '   <span class="myUniTest" id="myTestBWidth1">က</span>'."\n"
	     . '   <span class="myUniTest" id="myTestBWidth2">ကူ</span>'."\n"
	     . '   <span class="myUniTest" id="myTestC">ကၢ်</span>'."\n"
	     . '   <span class="myUniTest" id="myTestD">ကၣ်</span>'."\n"
	     . '</div>'."\n"
	     . '<script type="text/javascript">'."\n"
	     . '   var myUnicode = new TlsMyUnicode();'."\n"
	     . '   setTimeout(function(){myUnicode.main("' . plugins_url('/js/', __FILE__) . '")},100);'."\n"
	     . '</script>'."\n";
	}
}

/**************************
* Post / page content hooks
**************************/
add_filter( 'the_content', 'hkp_addMyTextClassSpans');
// applied to the post content retrieved from the database, prior to printing on the screen (also used in some other operations, such as trackbacks).
// applies to search results too

add_filter( 'the_content_feed', 'hkp_addMyTextClassSpans');
// applied to the post content prior to including in an RSS feed.

// Not sure if it's a good idea to include this or not. without it, content will not wrap properly in the editing window (and then they'll do what they always do on Word, and type enter at the end of each line. Not good.) this comment applies to the other similar edit_pre hooks below too. It almost makes sense not to strip them out to start with. But I still think it's a good idea because a. it's the 'proper' canonical way to store Myanmar b. since their invisible, with lots of editing those U+200b's are going to be moving all over the place, this keeps them under control, and c. it will help search work properly (we need to make sure to strip them from the search query too)
add_filter( 'content_edit_pre', 'hkp_insertLineBreakCharacter');
// applied to post excerpt prior to display for editing.

add_filter( 'content_save_pre', 'hkp_stripLineBreakCharacter');
// applied to post content prior to saving it in the database (also used for attachments).


/**************************
* Menu/widget content hooks
**************************/
add_filter( 'wp_nav_menu', 'hkp_addMyTextClassSpans');
// applied to the menu name retrieved from the database, prior to printing on the screen.

add_filter( 'wp_list_pages', 'hkp_addMyTextClassSpans');
// applied to the menu name retrieved from the database, prior to printing on the screen.

add_filter( 'widget_content', 'hkp_addMyTextClassSpans');
// applied to the widget text retrieved from the database, prior to printing on the screen.


/**************************
* Content excerpt hooks
**************************/
add_filter( 'the_excerpt', 'hkp_addMyTextClassSpans');
// applied to the post excerpt (or post content, if there is no excerpt) retrieved from the database, prior to printing on the screen (also used in some other operations, such as trackbacks).

add_filter( 'the_excerpt_rss', 'hkp_addMyTextClassSpans');
// applied to the post excerpt prior to including in an RSS feed.

add_filter( 'excerpt_edit_pre', 'hkp_insertLineBreakCharacter');
// applied to post excerpt prior to display for editing.

add_filter( 'excerpt_save_pre', 'hkp_stripLineBreakCharacter');
// applied to post excerpt prior to saving it in the database (also used for attachments).


/**************************
* Comment hooks
**************************/
add_filter( 'comment_text', 'hkp_addMyTextClassSpans');
// applied to the comment text before displaying on the screen by the comment_text function, and in the admin menus.

add_filter( 'comment_text_rss', 'hkp_addMyTextClassSpans');
// applied to the comment text prior to including in an RSS feed.

add_filter( 'pre_comment_content', 'hkp_stripLineBreakCharacter');
// applied to the content of a comment prior to saving the comment in the database.


/**************************
* Title hooks
**************************/
/*
 * Here is a workaround for the title issue.
 * Every where in the content.php, and content-page.php theme files, <?php the_title(); ?> needs to be relaced with <?php do_action( 'the_html_safe_title' ); //the_title(); ?>
*/
add_filter( 'the_html_safe_title', 'hkp_addMyTextClassSpansToPostTitles' );
function hkp_addMyTextClassSpansToPostTitles() {
	global $post;
	echo hkp_addMyTextClassSpans($post->post_title);
}

/*
add_filter( 'the_title_rss', 'hkp_addMyTextClassSpans');
// applied to the post title before including in an RSS feed (after first filtering with the_title.
// need to check this, I think it's not possible to format rss feed titles with html, it will display as text, I think.
*/
add_filter( 'title_edit_pre', 'hkp_insertLineBreakCharacter');
// applied to post title prior to display for editing.

add_filter( 'title_save_pre', 'hkp_stripLineBreakCharacter');
// applied to post title prior to saving it in the database (also used for attachments).

//add_filter( 'wp_title', 'hkp_addMyTextClassSpans'); // This is the title tag in the head, html in there isn't rendered on my Firefox.
// applied to the blog page title before sending to the browser in the wp_title function.

add_filter( 'widget_title', 'hkp_addMyTextClassSpans');

/***********************************************************************
 * Callback Functions
 **********************************************************************/
function hkp_addMyTextClassSpans($inputText) {
	$inputText = preg_replace('/([\p{Myanmar}][\p{Myanmar}.,()\'\" '.json_decode('"\u200b"').']*[\p{Myanmar}])(?=[^>]*(<|$))/u', '<span class="myText">$1</span>', $inputText); // inserts span tags around myanmar text

	return preg_replace('/(?<=[\p{Myanmar}])([ကခဂဃငစဆၡဇညတထဒနပဖဘမယရလဝသဟအဧ])/u', json_decode('"\u200b"').'$1', $inputText); // inserts U+200b before all Sgaw Karen consonants
}

function hkp_stripLineBreakCharacter($content) { // strips out line breaking characters (cleans things up in preparation for storing in the database, or reinserting them in the proper places)
	return str_replace(json_decode('"\u200b"'), '', $content);
}

function hkp_insertLineBreakCharacter( $inputText ) { // inserts the line breaking character before all sgaw Karen consonants. note: this is the only function that would have to change to make this plugin compatible with Myanmar, and some other scripts
	return preg_replace('/(?<=[\p{Myanmar}])([ကခဂဃငစဆၡဇညတထဒနပဖဘမယရလဝသဟအဧ])/u', json_decode('"\u200b"').'$1', $inputText);
}

?>
