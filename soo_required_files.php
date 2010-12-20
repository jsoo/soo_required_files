<?php

$plugin['name'] = 'soo_required_files';
$plugin['version'] = '0.2.3';
$plugin['author'] = 'Jeff Soo';
$plugin['author_uri'] = 'http://ipsedixit.net/txp/';
$plugin['description'] = 'Load JavaScript and CSS files per article';
$plugin['type'] = 1; // load on admin side for prefs management

defined('PLUGIN_HAS_PREFS') or define('PLUGIN_HAS_PREFS', 0x0001); 
defined('PLUGIN_LIFECYCLE_NOTIFY') or define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); 
$plugin['flags'] = PLUGIN_HAS_PREFS | PLUGIN_LIFECYCLE_NOTIFY;

defined('txpinterface') or @include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

@require_plugin('soo_plugin_pref');		// optional

// Plugin init not needed on admin side
if ( @txpinterface == 'public' )
{
	global $soo_required_files;
	$soo_plugin_display_prefs = function_exists('soo_plugin_pref_vals') ? 
		array_merge(soo_required_files_defaults(true), soo_plugin_pref_vals('soo_required_files')) : soo_required_files_defaults(true);
}
elseif ( @txpinterface == 'admin' ) 
{
	add_privs('plugin_prefs.soo_required_files','1,2');
	add_privs('plugin_lifecycle.soo_required_files','1,2');
	register_callback('soo_required_files_prefs', 'plugin_prefs.soo_required_files');
	register_callback('soo_required_files_prefs', 'plugin_lifecycle.soo_required_files');
}

function soo_required_files_prefs( $event, $step ) {
	if ( function_exists('soo_plugin_pref') )
		return soo_plugin_pref($event, $step, soo_required_files_defaults());
	if ( substr($event, 0, 12) == 'plugin_prefs' ) {
		$plugin = substr($event, 13);
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
		'css_dir'	=>	array(
			'val'	=>	'css/',
			'html'	=>	'text_input',
			'text'	=>	'Default css dir (relative to base URL, with closing slash)',
		),
		'js_dir'	=>	array(
			'val'	=>	'js/',
			'html'	=>	'text_input',
			'text'	=>	'Default js dir (relative to base URL, with closing slash)',
		),
		'form_prefix'	=>	array(
			'val'	=>	'require_',
			'html'	=>	'text_input',
			'text'	=>	'Optional prefix for form names',
		),
		'per_page'	=>	array(
			'val'	=>	0,
			'html'	=>	'yesnoradio',
			'text'	=>	'Load {page}.css and {page}.js?',
		),
		'per_section'	=>	array(
			'val'	=>	0,
			'html'	=>	'yesnoradio',
			'text'	=>	'Load {section}.css and {section}.js?',
		),
	);
}

function soo_required_files( $atts, $thing = '' ) {
	
	global $soo_required_files, $page, $s, $id;
	extract($soo_required_files);
	$required = do_list(parse($thing));
	
	// tag atts override defaults/prefs
	foreach ( $atts as $k => $v )
		if ( array_key_exists($k, $soo_required_files) )
			$$k = $v;
	
	if ( $per_page )
		$required = array_merge($required, _soo_required_files_add($page));
	
	if ( $per_section )
		$required = array_merge($required, _soo_required_files_add($s));
	
	// if individual article, get custom field contents
	if ( $id and $custom_field )
		$required = array_merge($required, do_list(custom_field(array(
			'name'		=>	$custom_field,
			'escape'	=>	'html',
			'default'	=>	'',
		))));
	
	$required = array_unique($required);

	foreach ( $required as $req ) {
		if ( substr(strtolower($req), -4) === '.css' )
			$out[] = '<link rel="stylesheet" type="text/css" href="' . 
			hu . $css_dir . $req . '" />';
		elseif ( substr(strtolower($req), -3) === '.js' )
			$out[] = '<script type="text/javascript" src="' . 
			hu . $js_dir . $req . '"></script>';
		elseif ( $req )
			$out[] = parse_form($form_prefix . $req);
	}
	
	return isset($out) ? implode("\n", $out) : '';
}

function _soo_required_files_add( $name ) {
	global $soo_required_files;
	extract($soo_required_files);
	$path_root = preg_replace('/index.php/', '', $_SERVER['SCRIPT_FILENAME']);
	if ( file_exists($path_root . $css_dir . $name . '.css') )
		$out[] = $name . '.css';
	if ( file_exists($path_root . $js_dir . $name . '.js') )
		$out[] = $name . '.js';
	return isset($out) ? $out : array();
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

 <div id="toc">

h2. Contents

* "Overview":#overview
* "Requirements":#requirements
* "Installation":#installation
** "Upgrading from 0.1.1":#upgrading
* "Usage":#usage
** "Attributes":#attributes
** "Per-page loading":#per-page
** "Per-section loading":#per-section
** "Per-article loading":#per-article
** "Loading tag contents":#tag-contents
** "Load order":#load-order
* "Preferences & defaults":#defaults
* "Examples":#examples
* "History":#history

 </div>

h1. soo_required_files

h2(#overview). Overview

Automatic loading of CSS and JavaScript files in the page @<head>@ (or anywhere you like). For background, see "Article-specific CSS & JavaScript in Textpattern":http://ipsedixit.net/txp/73/article-specific-css-javascript-in-textpattern.

*soo_required_files* has four options for loading files and forms:

* all contexts (by tag contents)
* per page (for files with matching names)
* per section (for files with matching names)
* per article (by custom field)

These may be used in any combination.

h2(#requirements). Requirements

No special requirements, except that per-article use %(required)requires% a custom field (named *Requires* by default). 

All my plugins are generally developed in the latest Txp public release and may not have been tested in earlier versions. 

*soo_required_files* is compatible with "soo_plugin_pref":http://ipsedixit.net/txp/92/soo_plugin_pref, which %(required)requires% Txp version 4.2.0 or greater.

h2(#installation). Installation

For per-article file and form loading, "name one of your custom fields":http://textbook.textpattern.net/wiki/index.php?title=Advanced_Preferences#Custom_Fields "Requires". (If you wish to name it something else, change the custom field setting in plugin preferences (see "Preferences & defaults":#defaults, below.)

Install the optional "soo_plugin_pref":http://ipsedixit.net/txp/92/soo_plugin_pref if you want to change default settings (Txp version 4.2.0 or greater %(required)required%).

h3(#upgrading). Upgrading from 0.1.1

If you are upgrading from version 0.1.1 with *soo_plugin_pref*, check preferences after installation. Two attribute names have been changed in this version, meaning any custom settings for those attributes (default js and css directories) will be overwritten on upgrade.

h2(#usage). Usage

Place the @soo_required_files@ tag in the page @<head>@. It works as either a single or container tag:

pre. <txp:soo_required_files />

or

pre. <txp:soo_required_files>
	<!-- comma-separated list of file and form names -->
</txp:soo_required_files>

h3(#attributes). Attributes

You can override any of the preferences with its corresponding attribute; the following are the ones you might actually want to set this way:

* @per_page@ _(boolean)_ Whether to enable per-page loading
* @per_section@ _(boolean)_ Whether to enable per-section loading

h3(#per-page). Per-page loading

When this option is enabled, either through "preferences":#defaults or by the @per_page@ attribute, the plugin will look for a css and a js file with the same name as the current page. For example, if the current section uses a page called "article", the plugin will look for files named "article.css" and "article.js" in their respective directories.

h3(#per-section). Per-section loading

Works the same as per-page loading, except based on the current section name (and the @per_section@ attribute/preference).

h3(#per-article). Per-article loading

The *Requires* field takes a comma-separated list. List items can include:

* JavaScript file names (e.g., "script.js")
* CSS file names (e.g, "style.css")
* Txp form names

Any item ending in ".js" is assumed to be a JavaScript file. It will result in a @<script>@ element loading the specified file from the default JavaScript directory (see "Defaults":#defaults, below).

Any item ending in ".css" is assumed to be a CSS file. It will result in a @<link>@ element loading the specified file in the default CSS directory (see "Defaults":#defaults, below).

Anything else is assumed to be the name of a Txp form. The plugin will attempt to output the named form (first adding the default prefix; see "Defaults":#defaults, below).

h3(#tag-contents). Loading tag contents

If you use @soo_required_files@ as a container, the tag contents are treated in the same way as the *Requires* field contents, above. That is, the tag contents should be a comma-separated list and can be any combination of css files, js files, and Txp form names.

As of version 0.2.2 you can include Txp tags in the tag contents, allowing e.g. Txp conditional tags for further automation options.

h3(#load-order). Load order

Files and forms are loaded in the following order:

# Tag contents
# Per-page files
# Per-section files
# *Requires* field contents

h2(#defaults). Preferences & defaults

You can change the default values for various settings and attributes by installing the "soo_plugin_pref":http://ipsedixit.net/txp/92/soo_plugin_pref plugin. Once installed you can access the preferences "by clicking the *Options* link for *soo_required_files* in the plugin list":http://textbook.textpattern.net/wiki/index.php?title=Plugins#Panel_layout_.26_controls.

The initial defaults are:

|_. Attribute|_. Description|_. Default|
|@custom_field@|_. Custom field name|<kbd>Requires</kbd>|
|@css_dir@|_. CSS directory|<kbd>css/</kbd>|
|@js_dir@|_. JS directory|<kbd>js/</kbd>|
|@form_prefix@|_. Form-name prefix|<kbd>require_</kbd>|
|@per_page@|_. Per-page loading|No|
|@per_section@|_. Per-section loading|No|

h2(#examples). Examples

h3. Using the Requires field

With defaults as above, entering <kbd>script1.js, style1.css, foo </kbd> in the *Requires* field will get @<txp:soo_required_files />@ to output something like:

pre. <script type="text/javascript" src="http://example.com/js/script1.js"></script>
<link rel="stylesheet" type="text/css" href="http://example.com/css/style1.css" />

followed by whatever is in the form named "require_foo".

Forms are especially useful for preset combinations of files. For example, some scripts (e.g. "Shadowbox":http://www.shadowbox-js.com/) require a mix of JavaScript and CSS files. Putting the appropriate @<script>@ and @<link>@ elements in a form allows you to call this up with a single entry in the *Requires* field. So to load my "require_Shadowbox" form (which loads the scripts and CSS required for Shadowbox), I enter <kbd>Shadowbox</kbd> in the *Requires* field.

h3. Combining per-section/page, per-article, and tag content file loading

For my "personal website":http://ipsedixit.net/ I have CSS files named after each section (including the default section), plus a file called "base.css" for common styles. Some articles have specific js/css requirements, so I also have a *Requires* field, used as above. I have a form called @page_top@ that creates the @<head>@ element for every section. It doesn't have any @<script>@ or @<link>@ tags, just this:

pre. <txp:soo_required_files>base.css</txp:soo_required_files>

Because I have enabled per-section loading in preferences, every HTML page automatically gets both the base stylesheet and the section-specific stylesheet, and individual article pages will also load anything listed in *Requires*.

h2(#history). History

h3. Version 0.2.3 (2010/12/20)

* Code cleaning only; no functional changes

h3. Version 0.2.2 (2010/07/10)

* Tag contents can now include other Txp tags, allowing e.g. Txp conditional tags for further automation options.

h3. Version 0.2.1 (2009/10/03)

* Fixed to work with sub-directory installations

h3. Version 0.2 (2009/09/25)

* New features:
** Per-section and/or per-page file loading
** Container tag mode for additional files/forms
** All preferences can now be overriden by tag attributes
* Note: new attribute names for default js and css directories. Users upgrading from version 0.1.1 should check preferences and update these values if needed.

h3. Version 0.1.1 (2009/09/18)

* Added compatibility with "soo_plugin_pref":http://ipsedixit.net/txp/92/soo_plugin_pref for preference management

h3. Version 0.1 (2009/05/15)

* Initial release. Per-article loading of js/css files, Txp forms.

</div>
# --- END PLUGIN HELP ---
-->
<?php
}

?>
