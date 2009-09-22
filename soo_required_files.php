<?php

$plugin['name'] = 'soo_required_files';
$plugin['version'] = '0.1.1';
$plugin['author'] = 'Jeff Soo';
$plugin['author_uri'] = 'http://ipsedixit.net/txp/';
$plugin['description'] = 'Load JavaScript and CSS files per article';
$plugin['type'] = 1; 

if (!defined('PLUGIN_HAS_PREFS')) define('PLUGIN_HAS_PREFS', 0x0001); 
if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); 
$plugin['flags'] = PLUGIN_HAS_PREFS | PLUGIN_LIFECYCLE_NOTIFY;

if (!defined('txpinterface'))
	@include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

@require_plugin('soo_plugin_pref');		// optional

global $soo_required_files;

if ( function_exists('soo_plugin_pref_vals') )
	$soo_required_files = soo_plugin_pref_vals('soo_required_files');
else 
	foreach ( soo_required_files_defaults() as $name => $val )
		$soo_required_files[$name] = $val;

add_privs('plugin_prefs.soo_required_files','1,2');
add_privs('plugin_lifecycle.soo_required_files','1,2');
register_callback('soo_required_files_prefs', 'plugin_prefs.soo_required_files');
register_callback('soo_required_files_prefs', 'plugin_lifecycle.soo_required_files');

function soo_required_files_prefs( $event, $step ) {
	if ( function_exists('soo_plugin_pref') )
		return soo_plugin_pref($event, $step, soo_required_files_defaults());
	if ( substr($event, 0, 12) == 'plugin_prefs' ) {
		$plugin = substr($event, 12);
		$message = '<p><br /><strong>' . gTxt('edit') . " $plugin " .
			gTxt('edit_preferences') . ':</strong><br />' . gTxt('install_plugin') . 
			' <a href="http://ipsedixit.net/txp/92/soo_plugin_pref">' . 
			'soo_plugin_pref</a></p>';
		pagetop(gTxt('edit_preferences') . " &#8250; $plugin", $message);
	}
}

function soo_required_files_defaults( ) {
	return array(
		'custom_field'		=>	array(
			'val'	=>	'Requires',
			'html'	=>	'text_input',
			'text'	=>	'Custom field name',
		),
		'default_css_dir'	=>	array(
			'val'	=>	'css/',
			'html'	=>	'text_input',
			'text'	=>	'Default css dir (relative to base URL, with closing slash)',
		),
		'default_js_dir'	=>	array(
			'val'	=>	'js/',
			'html'	=>	'text_input',
			'text'	=>	'Default js dir (relative to base URL, with closing slash)',
		),
		'form_prefix'	=>	array(
			'val'	=>	'require_',
			'html'	=>	'text_input',
			'text'	=>	'Optional prefix for form names',
		),
	);
}

function soo_required_files( ) {
	
	global $soo_required_files, $thisarticle;
	
	if ( empty($thisarticle) ) return;	// requires individual article context

	$custom_field_atts = array(
		'name'		=>	$soo_required_files['custom_field'],
		'escape'	=>	'html',
		'default'	=>	'',
		);
		
	$required = do_list(custom_field($custom_field_atts));
	foreach ( $required as $req ) {
		if ( preg_match('/\.css$/', $req) )
			$out[] = '<link rel="stylesheet" type="text/css" href="' . 
			hu . $soo_required_files['default_css_dir'] . $req . '" />';
		elseif ( preg_match('/\.js$/', $req) )
			$out[] = '<script type="text/javascript" src="' . 
			hu . $soo_required_files['default_js_dir'] . $req . '"></script>';
		elseif ( $req )
			$out[] = parse_form($soo_required_files['form_prefix'] . $req);
	}
	return isset($out) ? implode("\n", $out) : '';

}

# --- END PLUGIN CODE ---

if (0) {
?>
<!-- CSS SECTION
# --- BEGIN PLUGIN CSS ---
<style type="text/css">
div#sed_help pre {padding: 0.5em 1em; background: #eee; border: 1px dashed #ccc;}
div#sed_help h1, div#sed_help h2, div#sed_help h3, div#sed_help h3 code {font-family: sans-serif; font-weight: bold;}
div#sed_help h1, div#sed_help h2, div#sed_help h3 {margin-left: -1em;}
div#sed_help h2, div#sed_help h3 {margin-top: 2em;}
div#sed_help h1 {font-size: 2.4em;}
div#sed_help h2 {font-size: 1.8em;}
div#sed_help h3 {font-size: 1.4em;}
div#sed_help h4 {font-size: 1.2em;}
div#sed_help h5 {font-size: 1em;margin-left:1em;font-style:oblique;}
div#sed_help h6 {font-size: 1em;margin-left:2em;font-style:oblique;}
div#sed_help li {list-style-type: disc;}
div#sed_help li li {list-style-type: circle;}
div#sed_help li li li {list-style-type: square;}
div#sed_help li a code {font-weight: normal;}
div#sed_help li code:first-child {background: #ddd;padding:0 .3em;margin-left:-.3em;}
div#sed_help li li code:first-child {background:none;padding:0;margin-left:0;}
div#sed_help dfn {font-weight:bold;font-style:oblique;}
div#sed_help .required, div#sed_help .warning {color:red;}
div#sed_help .default {color:green;}
div#sed_help kbd {
	font-family: Verdana, Arial, sans-serif;
	font-size: 11px;
	color: #000;
	line-height: 11px;
	height: 17px;
	background: #eee;
	border: solid #aaa;
	border-width: 1px 0 0 1px;
	padding: -1px 1px;	
}
</style>
# --- END PLUGIN CSS ---
-->
<!-- HELP SECTION
# --- BEGIN PLUGIN HELP ---
<div id="sed_help">

h1. soo_required_files

Article-specific loading of CSS and JavaScript files in the page @<head>@ (or anywhere you like). For background, see "Article-specific CSS & JavaScript in Textpattern":http://ipsedixit.net/txp/73/article-specific-css-javascript-in-textpattern.

h2. Requirements

A custom field, named *Requires* by default. *soo_required_files* was developed in Txp 4.0.8 and may not have been tested in earlier versions. 

h2. Usage

Place the @soo_required_files@ tag in the page @<head>@. The tag only produces output when there is individual article context and when the article has an entry in the *Requires* field.

pre. <txp:soo_required_files />

The *Requires* field takes a comma-separated list. List items can include:

* JavaScript file names (e.g., "script.js")
* CSS file names (e.g, "style.css")
* Txp form names

Any item ending in ".js" is assumed to be a JavaScript file. It will result in a @<script>@ element loading the specified file from the default JavaScript directory (see "Defaults":#defaults, below).

Any item ending in ".css" is assumed to be a CSS file. It will result in a @<link>@ element loading the specified file in the default CSS directory (see "Defaults":#defaults, below).

Anything else is assumed to be the name of a Txp form. The plugin will attempt to output the named form (first adding the default prefix; see "Defaults":#defaults, below).

h2(#defaults). Defaults

You can change the default values for custom field name, JavaScript and CSS directories, and form-name prefix. The more elegant way is to install the "soo_plugin_pref":http://ipsedixit.net/txp/92/soo_plugin_pref plugin (which %(required)requires% Txp 4.2.0 or greater). But if you don't want another plugin and don't mind editing code you could directly edit the defaults array in the plugin code; look for the @soo_required_files_defaults()@ function and edit the @val@ values as desired.

On installation the defaults are:

|_. Custom field name|<kbd>Requires</kbd>|
|_. CSS directory|@css/@|
|_. JS directory|@js/@|
|_. Form-name prefix|@require_@|

h2. Examples

With defaults as above, entering <kbd>script1.js, style1.css, foo </kbd> in the *Requires* field will get @<txp:soo_required_files />@ to output something like:

pre. <script type="text/javascript" src="http://example.com/js/script1.js"></script>
<link rel="stylesheet" type="text/css" href="http://example.com/css/style1.css" />

followed by whatever is in the form named "require_foo".

Forms are especially useful for preset combinations of files. For example, some scripts (e.g. "Shadowbox":http://www.shadowbox-js.com/) require a mix of JavaScript and CSS files. Putting the appropriate @<script>@ and @<link>@ elements in a form allows you to call this up with a single entry in the *Requires* field. So to load my "require_Shadowbox" form (which loads the scripts and CSS required for Shadowbox), I enter <kbd>Shadowbox</kbd> in the *Requires* field.

</div>
# --- END PLUGIN HELP ---
-->
<?php
}

?>
