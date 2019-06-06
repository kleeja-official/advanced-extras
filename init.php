<?php
// Kleeja Plugin
// Developer: Kleeja team

// Prevent illegal run
if (! defined('IN_PLUGINS_SYSTEM'))
{
    exit();
}


// https://github.com/Falicon/BitlyPHP


// Plugin Basic Information
$kleeja_plugin['advanced_extras']['information'] = [
    // The casucal name of this plugin, anything can a human being understands
    'plugin_title' => [
        'en' => 'Advanced Extras',
        'ar' => 'هيدر وفوتر إضافي متفدم'
    ],
    // Who wrote this plugin?
    'plugin_developer' => 'Kleeja.com',
    // This plugin version
    'plugin_version' => '1.0.1',
    // Explain what is this plugin, why should I use it?
    'plugin_description' => [
        'en' => 'An advanced interface for the extra templates feature',
        'ar' => 'واجهة متقدمة لصفحة هيدر وفوتر إضافيين'
    ],
    //settings page, if there is one (what after ? like cp=j_plugins)
    'settings_page' => 'cp=n_extra',
    // Min version of Kleeja that's requiered to run this plugin
    'plugin_kleeja_version_min' => '2.0',
    // Max version of Kleeja that support this plugin, use 0 for unlimited
    'plugin_kleeja_version_max' => '3.9',
    // Should this plugin run before others?, 0 is normal, and higher number has high priority
    'plugin_priority' => 0,
];

//after installation message, you can remove it, it's not requiered
$kleeja_plugin['advanced_extras']['first_run']['ar'] = '
شكراً لاستخدامك هذه الإضافة قم بمراسلتنا بالأخطاء عند ظهورها على البريد: <br>
info@kleeja.com
<hr>
<br>
<h3>لاحظ:</h3>
<b>تجد إعدادات الإضافة في صفحة: هيدر وفوتر إضافي في اللوحة</b>
';

$kleeja_plugin['advanced_extras']['first_run']['en'] = '
Thanks for using this plugin, to report bugs visit:
<br>
https://github.com/kleeja-official/advanced-extras/issues
';


// Plugin Installation function
$kleeja_plugin['advanced_extras']['install'] = function ($plg_id) {
    //new language variables
    add_olang([
        'ADVANCED_EXTRAS_APPEAR_PAGE' => 'عرض في صفحة'
    ],
        'ar',
        $plg_id);

    add_olang([
        'ADVANCED_EXTRAS_APPEAR_PAGE' => 'Appears in page',
    ],
        'en',
        $plg_id);
};


//Plugin update function, called if plugin is already installed but version is different than current
$kleeja_plugin['advanced_extras']['update'] = function ($old_version, $new_version) {
    // if(version_compare($old_version, '0.5', '<')){
    // 	//... update to 0.5
    // }
    //
    // if(version_compare($old_version, '0.6', '<')){
    // 	//... update to 0.6
    // }

    //you could use update_config, update_olang
};


// Plugin Uninstallation, function to be called at unistalling
$kleeja_plugin['advanced_extras']['uninstall'] = function ($plg_id) {
    //delete language variables
    foreach (['ar', 'en'] as $language)
    {
        delete_olang(null, $language, $plg_id);
    }

    global $SQL , $dbprefix;
    $update_query	= [
        'UPDATE'	=> "{$dbprefix}stats",
        'SET'		  => "ex_footer = '' , ex_header = '' "
    ];

    $SQL->build($update_query);

    if ($SQL->affected())
    {
        //delete cache ..
        delete_cache('data_extra');
    }
};


// Plugin functions
$kleeja_plugin['advanced_extras']['functions'] = [
    'end_admin_page' => function($args) {
        if (! empty($args['go_to']))
        {
            $go_to = $args['go_to'];


            if ($go_to == 'n_extra')
            {
                global $lang;

                $ex_header = $args['ex_header'];
                $ex_footer = $args['ex_footer'];

                $stylee = 'admin_adv_extras';
                $styleePath = dirname(__FILE__);

                //$ex_header, $ex_footer

                $ex_values['header'] = advanced_extras_get_values($ex_header);
                $ex_values['footer'] = advanced_extras_get_values($ex_footer);

                foreach (['header', 'footer'] as $k)
                {
                    $extra_pages[$k] = [
                        ['name' => 'all', 'title' => $lang['ALL'], 'value' => isset($ex_values[$k]['all']) ? $ex_values[$k]['all'] : ''],
                        ['name' => 'index', 'title' => $lang['INDEX'], 'value' => isset($ex_values[$k]['index']) ? $ex_values[$k]['index'] : ''],
                        ['name' => 'download', 'title' => $lang['DOWNLOAD'], 'value' => isset($ex_values[$k]['download']) ? $ex_values[$k]['download'] : ''],
                        ['name' => 'rules', 'title' => $lang['RULES'], 'value' => isset($ex_values[$k]['rules']) ? $ex_values[$k]['rules'] : ''],
                        ['name' => 'guide', 'title' => $lang['GUIDE'], 'value' => isset($ex_values[$k]['guide']) ? $ex_values[$k]['guide'] : ''],
                        ['name' => 'stats', 'title' => $lang['STATS'], 'value' => isset($ex_values[$k]['stats']) ? $ex_values[$k]['stats'] : ''],
                        ['name' => 'reports', 'title' => $lang['REPORT'], 'value' => isset($ex_values[$k]['reports']) ? $ex_values[$k]['reports'] : ''],
                        ['name' => 'call', 'title' => $lang['CALL'], 'value' => isset($ex_values[$k]['call']) ? $ex_values[$k]['call'] : ''],
                        ['name' => 'profile', 'title' => $lang['PROFILE'], 'value' => isset($ex_values[$k]['profile']) ? $ex_values[$k]['profile'] : ''],
                        ['name' => 'fileuser', 'title' => $lang['YOUR_FILEUSER'], 'value' => isset($ex_values[$k]['fileuser']) ? $ex_values[$k]['fileuser'] : ''],
                        ['name' => 'login', 'title' => $lang['LOGIN'], 'value' => isset($ex_values[$k]['login']) ? $ex_values[$k]['login'] : ''],
                        ['name' => 'register', 'title' => $lang['REGISTER'], 'value' => isset($ex_values[$k]['register']) ? $ex_values[$k]['register'] : ''],
                    ];
                }

                return compact('stylee', 'styleePath', 'extra_pages');
            }
        }
    },

    'require_admin_page_begin_n_extra' => function ($args) {
        if (ip('submit'))
        {
            $_POST['ex_header'] = '';
            $_POST['ex_footer'] = '';

            if (! empty($_POST['ex_header_x']) && is_array($_POST['ex_header_x']))
            {
                foreach ($_POST['ex_header_x'] as $key => $value)
                {
                    $_POST['ex_header'] .= "\n!!! start advanced_extras " . htmlspecialchars($key) . " !!!\n" . $value . "\n!!! end advanced_extras " . htmlspecialchars($key) . " !!!\n";
                }
            }

            if (! empty($_POST['ex_footer_x']) && is_array($_POST['ex_footer_x']))
            {
                foreach ($_POST['ex_footer_x'] as $key => $value)
                {
                    $_POST['ex_footer'] .= "\n!!! start advanced_extras " . htmlspecialchars($key) . " !!!\n" . $value . "\n!!! end advanced_extras " . htmlspecialchars($key) . " !!!\n";
                }
            }
        }
    },

    'Saaheader_links_func' => function($args) {
        $extras = $args['extras'];
        $go_page = g('go', 'str',
            defined('IN_DOWNLOAD') ? 'download' : (defined('IN_REAL_INDEX') ? 'index' : ''));

        if (! empty($extras['header']))
        {
            $ex_extras = advanced_extras_get_values($extras['header']);

            $extras['header'] = $ex_extras['all'];

            foreach ($ex_extras as $page=>$value)
            {
                if ($page == $go_page)
                {
                    $extras['header'] .= (trim($extras['header']) == '' ? '' : '<br>') . $value;
                }
            }
        }

        return compact('extras');
    },

    'Saafooter_func' => function($args) {
        $extras = $args['extras'];
        $go_page = g('go', 'str',
            defined('IN_DOWNLOAD') ? 'download' : (defined('IN_REAL_INDEX') ? 'index' : ''));

        if (! empty($extras['footer']))
        {
            $ex_extras = advanced_extras_get_values($extras['footer']);

            $extras['footer'] = $ex_extras['all'];

            foreach ($ex_extras as $page=>$value)
            {
                if ($page == $go_page)
                {
                    $extras['footer'] .= (trim($extras['footer']) == '' ? '' : '<br>') . $value;
                }
            }
        }

        return compact('extras');
    }
];


/**
 * special functions
 */

if (! function_exists('advanced_extras_get_values'))
{
    function advanced_extras_get_values($content)
    {
        $re = '/!!! start advanced_extras ([a-zA-Z0-9_]+) !!!\n?(.*?)\n?!!! end advanced_extras \1 !!!/sm';

        preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);

        $values = [];

        if (! empty($matches))
        {
            foreach ($matches as $match)
            {
                $values[$match[1]] = $match[2];
            }
        }

        return $values;
    }
}
