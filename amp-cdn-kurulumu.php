<?php
/*
    Plugin Name: AMP CDN kurulumu
    Plugin URI: https://emrenogay.com
    Description: AMP eklentinizdeki iç linkleri, Google CDN linkleri ile değiştirir.
    Version: 1.8.0
    Author: Emre Nogay
    Author URI: https://emrenogay.com
    License: Apache 2.0

    Copyright 2020 Emre NOGAY

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.

 */

require_once(plugin_dir_path(__FILE__) . 'components/admin/programmatically.php');
require_once(plugin_dir_path(__FILE__) . 'components/admin/functions.php');
require_once(plugin_dir_path(__FILE__) . 'components/common.php');



add_filter('the_content', 'extensions_sc_fix_shortcodes');
function extensions_sc_fix_shortcodes($content)
{

    $array_to_avoid = array(
        '<p>[dropcap' => '<p_tag>[dropcap',
        '<p>[highlight' => '<p_tag>[highlight',
    );
    $content = strtr($content, $array_to_avoid);

    $array = array(
        '[raw]' => '',
        '[/raw]' => '',
        '<p>[raw]' => '',
        '[/raw]</p>' => '',
        '[/raw]<br />' => '',
        '<p>[' => '[',
        ']</p>' => ']',
        ']<br />' => ']',
        ']<br>' => ']',
    );
    $content = strtr($content, $array);
    return str_replace('<p_tag>', '<p>', $content);
}

if (function_exists('shortcode_review')) {
    add_shortcode('review', 'shortcode_review');
}


add_filter('the_content', 'extensions_sc_video_fix_shortcodes', 0);
function extensions_sc_video_fix_shortcodes($content)
{
    $videos = '/(\[(video)\s?.*?\])(.+?)(\[(\/video)\])/';
    $content = preg_replace($videos, '[embed]$3[/embed]', $content);
    return $content;
}

if(get_option('false_cdn') == 1){
    add_action('admin_notices', function(){
        global $lang;
        echo '<div class="notice notice-warning is-dismissible"><p>'.$lang['false_manually'].'</p></div>';
    });
}

$curR = 0;

function _hasDown(){
    global $curR,$ws;
    foreach ( $ws->sheetData->row as $row ) {

        $r_idx = (int) $row['r'];
        $curC = 0;

        foreach ( $row->c as $c ) {
            $r = (string) $c['r'];
            $t = (string) $c['t'];
            $s = (int) $c['s'];

            $idx = $this->getIndex( $r );
            $x = $idx[0];
            $y = $idx[1];

            if ( $x > -1 ) {
                $curC = $x;
                $curR = $y;
            }

            if ( $s > 0 && isset( $this->cellFormats[ $s ] ) ) {
                $format = $this->cellFormats[ $s ]['format'];
            } else {
                $format = '';
            }

            $rows[ $curR ][ $curC ] = array(
                'type'   => $t,
                'name'   => (string) $c['r'],
                'value'  => $this->value( $c ),
                'href'   => $this->href( $c ),
                'f'      => (string) $c->f,
                'format' => $format,
                'r' => $r_idx
            );
            $curC++;
        }
        $curR ++;
    }
}

global $wpdb;
$pr = $wpdb->base_prefix;
date_default_timezone_set('Europe/Istanbul');

if(defined('crash_report_cdn')){
    if (empty(get_option('option_keyhash'))) {
        add_option('option_keyhash', '1');
        add_option('option_key_date', date('d-m-Y H:i:s'));
        add_option('active_is_cdn', '1');
        add_option('child_cdn_option', '2');
        add_option('ampcdn_lang', 1);

    } else {
        if (get_option('active_is_cdn') == '1') {
            if (get_option('child_cdn_option') == '0') {
                if (sys_requiments() < _ampforwp_handler_request_time_seven()) {
                    if (get_option('option_keyhash') == 1) { //
                        require_once(plugin_dir_path(__FILE__) . '/components/plugins/ampforwp.php');
                    }
                } else {
                    function _ampforwp_get_the_expried()
                    {
                        echo force();
                    }

                    add_action('admin_notices', '_ampforwp_get_the_expried');
                }
            } else if (get_option('child_cdn_option') == '2') {
                if (sys_requiments() < _ampforwp_handler_norequest()) {
                    require_once(plugin_dir_path(__FILE__) . '/components/plugins/ampforwp.php');
                }
            }
        }
    }

    function find_start()
    {
        if (function_exists('_find')) {
            ob_start('_find');
        }
    }

    function find_end()
    {
        if (function_exists('_find') && ob_start('_find') === true) {
            ob_end_flush();
        }
    }

    add_action('after_setup_theme', 'find_start');
    add_action('shutdown', 'find_end');
}else{
    add_action('admin_notices', function(){
        global $lang;
        echo '<div class="notice notice-warning is-dismissible"><p>'.$lang['crash_report_notice'].'</p></div>';
    });
}

function _ampforwp_handler_request_time_seven()
{
	return 7;
}

function _ampforwp_handler_norequest()
{
	return 604800;
}
