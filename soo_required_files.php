<?php
$plugin['version'] = '0.2.6';
$plugin['author'] = 'Jeff Soo';
$plugin['author_uri'] = 'http://ipsedixit.net/txp/';
$plugin['description'] = 'Load JavaScript and CSS files per article';
$plugin['type'] = 1; // load on admin side for prefs management
$plugin['allow_html_help'] = 1;

defined('PLUGIN_HAS_PREFS') or define('PLUGIN_HAS_PREFS', 0x0001); 
defined('PLUGIN_LIFECYCLE_NOTIFY') or define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002); 
$plugin['flags'] = PLUGIN_HAS_PREFS | PLUGIN_LIFECYCLE_NOTIFY;

if (! defined('txpinterface')) {
    global $compiler_cfg;
    @include_once('config.php');
    @include_once($compiler_cfg['path']);
}

# --- BEGIN PLUGIN CODE ---

if(class_exists('\Textpattern\Tag\Registry')) {
    Txp::get('\Textpattern\Tag\Registry')
        ->register('soo_required_files')
        ;
}

@require_plugin('soo_plugin_pref');     // optional

if (@txpinterface == 'admin') {
    add_privs('plugin_prefs.soo_required_files','1,2');
    add_privs('plugin_lifecycle.soo_required_files','1,2');
    register_callback('soo_required_files_manage_prefs', 'plugin_prefs.soo_required_files');
    register_callback('soo_required_files_manage_prefs', 'plugin_lifecycle.soo_required_files');
}

function soo_required_files_manage_prefs($event, $step) 
{
    if (function_exists('soo_plugin_pref'))
        return soo_plugin_pref($event, $step, soo_required_files_pref_spec());
    
        // message to install soo_plugin_pref
    if (substr($event, 0, 12) == 'plugin_prefs') {
        $plugin = substr($event, 13);
        $message = '<p><br /><strong>'.gTxt('edit')." $plugin ".
            gTxt('edit_preferences').':</strong><br />'.gTxt('install_plugin').
            ' <a href="http://ipsedixit.net/txp/92/soo_plugin_pref">'.
            'soo_plugin_pref</a></p>';
        pagetop(gTxt('edit_preferences')." &#8250; $plugin", $message);
    }
}

function soo_required_files_pref_spec()
{
    return array(
        'custom_field' => array(
            'val'   => 'Requires',
            'html'  => 'text_input',
            'text'  => 'Custom field name',
        ),
        'css_dir' => array(
            'val'   => 'css/',
            'html'  => 'text_input',
            'text'  => 'Default css dir (relative to base URL, with closing slash)',
        ),
        'js_dir' => array(
            'val'   => 'js/',
            'html'  => 'text_input',
            'text'  => 'Default js dir (relative to base URL, with closing slash)',
        ),
        'form_prefix' => array(
            'val'   => 'require_',
            'html'  => 'text_input',
            'text'  => 'Optional prefix for form names',
        ),
        'per_page' => array(
            'val'   => 0,
            'html'  => 'yesnoradio',
            'text'  => 'Load {page}.css and {page}.js?',
        ),
        'per_section' => array(
            'val'   => 0,
            'html'  => 'yesnoradio',
            'text'  => 'Load {section}.css and {section}.js?',
        ),
        'html_version' => array(
            'val'   => 5,
            'html'  => 'text_input',
            'text'  => 'HTML version (if < 5, type attribute included in output)',
        ),
    );
}

function soo_required_files_prefs()
{
    static $prefs;
    if (! $prefs) {
        foreach (soo_required_files_pref_spec() as $name => $spec) {
            $prefs[$name] = $spec['val'];
        }
        if (function_exists('soo_plugin_pref_vals')) {
            $prefs = array_merge($prefs, soo_plugin_pref_vals('soo_required_files'));
        }
    }
    return $prefs;
}

function soo_required_files($atts, $thing = '')
{
    global $page, $s, $id;
    $prefs = soo_required_files_prefs();
    extract($prefs);
    $required = do_list(parse($thing));
    
    // tag atts override defaults/prefs
    foreach ($atts as $k => $v)
        if (array_key_exists($k, $prefs))
            $$k = $v;
    
    if ($per_page)
        $required = array_merge($required, _soo_required_files_add($page));
    
    if ($per_section)
        $required = array_merge($required, _soo_required_files_add($s));
    
    // if individual article, get custom field contents
    if ($id and $custom_field)
        $required = array_merge($required, do_list(custom_field(array(
            'name'      =>  $custom_field,
            'escape'    =>  'html',
            'default'   =>  '',
        ))));
    
    $required = array_unique($required);
    
    $css_close_tag = $html_version < 5 ? ' type="text/css" />' : '>';
    $js_close_tag = $html_version < 5 ? ' type="text/javascript"' : '';

    foreach ($required as $req) {
        if (substr(strtolower($req), -4) === '.css')
            $out[] = '<link rel="stylesheet" href="'.
            hu.$css_dir.$req.'"'.$css_close_tag;
        elseif (substr(strtolower($req), -3) === '.js')
            $out[] = '<script src="'.
            hu.$js_dir.$req.'"'.$js_close_tag.'></script>';
        elseif ($req)
            $out[] = parse_form($form_prefix.$req);
    }
    
    return isset($out) ? implode("\n", $out) : '';
}

function _soo_required_files_add($name)
{
    extract(soo_required_files_prefs());
    $path_root = preg_replace('/index.php/', '', $_SERVER['SCRIPT_FILENAME']);
    if (file_exists($path_root . $css_dir . $name . '.css'))
        $out[] = $name . '.css';
    if (file_exists($path_root . $js_dir . $name . '.js'))
        $out[] = $name . '.js';
    return isset($out) ? $out : array();
}

# --- END PLUGIN CODE ---

?>
