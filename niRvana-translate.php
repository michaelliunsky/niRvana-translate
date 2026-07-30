<?php
/*
 * Plugin Name:       niRvana-translate 多语言翻译插件
 * Plugin URI:        https://blog.mkliu.top/536.html
 * Description:       基于 translate.js 的 WordPress 多语言翻译插件.
 * Version:           1.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.0
 * Author:            michaelliunsky
 * Author URI:        https://blog.mkliu.top/
 * License:           GNU General Public License v3.0
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       nirvana-translate
 */

if (!defined('ABSPATH')) {
    exit;
}

// -------------------------------
// 常量
// -------------------------------
define('NIRVANA_TX_URL', plugin_dir_url(__FILE__));
define('NIRVANA_OPT_LANGS', 'nirvana_translate_languages');
define('NIRVANA_OPT_MENU_NAME', 'nirvana_translate_menu_name');

// -------------------------------
// 加载翻译文本域
// -------------------------------
add_action('plugins_loaded', function() {
    load_plugin_textdomain('nirvana-translate', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// -------------------------------
// 卸载清理
// -------------------------------
function nirvana_translate_uninstall() {
    delete_option(NIRVANA_OPT_LANGS);
    delete_option(NIRVANA_OPT_MENU_NAME);
}
register_uninstall_hook(__FILE__, 'nirvana_translate_uninstall');

// -------------------------------
// 插件链接：在插件列表中显示"设置"链接
// -------------------------------
function nirvana_translate_settings_link($links)
{
    $settings_link = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=nirvana-translate-plugin'), __('设置', 'nirvana-translate'));
    $links[] = $settings_link;
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nirvana_translate_settings_link');

// -------------------------------
// 管理页面：菜单与设置注册
// -------------------------------
function nirvana_translate_admin_menu()
{
    add_menu_page(
        __('niRvana 翻译设置', 'nirvana-translate'),
        __('多语言翻译', 'nirvana-translate'),
        'manage_options',
        'nirvana-translate-plugin',
        'nirvana_translate_settings_page',
        'dashicons-translation',
        61
    );
}
add_action('admin_menu', 'nirvana_translate_admin_menu');

function nirvana_translate_register_settings() {
    register_setting('nirvana_translate_options', NIRVANA_OPT_LANGS, 'nirvana_translate_sanitize_languages');
    register_setting('nirvana_translate_options', NIRVANA_OPT_MENU_NAME, 'sanitize_text_field');

    add_settings_section('nirvana_translate_section_main', __('语言与脚本设置', 'nirvana-translate'), null, 'nirvana-translate-plugin');

    add_settings_field(NIRVANA_OPT_LANGS, __('翻译语言设置', 'nirvana-translate'), 'nirvana_translate_render_languages', 'nirvana-translate-plugin', 'nirvana_translate_section_main');
    add_settings_field(NIRVANA_OPT_MENU_NAME, __('菜单显示名称', 'nirvana-translate'), 'nirvana_translate_render_menu_name', 'nirvana-translate-plugin', 'nirvana_translate_section_main');
}
add_action('admin_init', 'nirvana_translate_register_settings');

function nirvana_translate_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('niRvana-theme 多语言翻译插件设置', 'nirvana-translate'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nirvana_translate_options');
            do_settings_sections('nirvana-translate-plugin');
            submit_button();
            ?>

            <div class="notice notice-info" style="margin-top:16px;padding:12px 16px;">
                <p style="margin:0 0 8px;"><strong><?php echo esc_html__('使用引导', 'nirvana-translate'); ?></strong></p>
                <ol style="margin:0 0 8px 20px;">
                    <li><?php echo esc_html__('在"翻译语言设置"添加语言并保存。', 'nirvana-translate'); ?></li>
                    <li><?php echo esc_html__('到【外观 → 菜单】→ 左侧 niRvana翻译菜单 → 勾选并"添加到菜单"。', 'nirvana-translate'); ?></li>
                </ol>
                <p style="margin:0;"><strong><?php echo esc_html__('语言简码参考：', 'nirvana-translate'); ?></strong>
                    <a href="<?php echo esc_url(NIRVANA_TX_URL . 'language.json'); ?>" target="_blank" rel="noopener"><?php echo esc_html__('translate.js 支持列表', 'nirvana-translate'); ?></a>
                </p>
            </div>
        </form>
        <div style="margin-top: 30px; padding: 15px; background-color: #f0f0f1; border-left: 4px solid #0073aa; text-align: center;">
            <p style="font-size: 16px; font-weight: bold; margin: 0;">
                Plugin niRvana-translate | Designed by michaelliunsky<br>
                <a href="https://blog.mkliu.top" target="_blank" style="color: #0073aa;">https://blog.mkliu.top</a>
            </p>
        </div>
    </div>
    <?php
}

// -------------------------------
// 校验函数
// -------------------------------
function nirvana_translate_sanitize_languages($input)
{
    $result = [];
    if (!is_array($input)) {
        return $result;
    }
    foreach ($input as $lang) {
        if (empty($lang['name']) || empty($lang['code'])) {
            continue;
        }
        $result[] = [
            'name' => sanitize_text_field($lang['name']),
            'code' => sanitize_text_field($lang['code']),
            'icon' => !empty($lang['icon']) ? esc_url_raw($lang['icon']) : '',
        ];
    }
    return $result;
}

// -------------------------------
// 设置字段渲染：语言列表
// -------------------------------
function nirvana_translate_render_languages()
{
    $langs = get_option(NIRVANA_OPT_LANGS, []);
    ?>
    <div id="nirvana-languages-wrap">
        <div id="nirvana-languages-list">
            <?php foreach ($langs as $i => $lang): ?>
                <div class="nirvana-lang-row" style="margin-bottom:10px;">
                    <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_LANGS); ?>[<?php echo $i; ?>][name]" value="<?php echo esc_attr($lang['name']); ?>" placeholder="语言名称（自定义）" />
                    <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_LANGS); ?>[<?php echo $i; ?>][code]" value="<?php echo esc_attr($lang['code']); ?>" placeholder="语言简码" />
                    <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_LANGS); ?>[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($lang['icon']); ?>" placeholder="图标 URL (可空)" />
                    <button type="button" class="button nirvana-remove-lang">删除</button>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button button-primary" id="nirvana-add-lang"><?php echo esc_html__('添加语言', 'nirvana-translate'); ?></button>

        <template id="nirvana-lang-template">
            <div class="nirvana-lang-row" style="margin-bottom:10px;">
                <input type="text" name="__NAME__" placeholder="语言名称" />
                <input type="text" name="__CODE__" placeholder="语言简码" />
                <input type="text" name="__ICON__" placeholder="图标 URL(可空)" />
                <button type="button" class="button nirvana-remove-lang">删除</button>
            </div>
        </template>
    </div>

    <script>
    (function(){
        var list = document.getElementById('nirvana-languages-list');
        var addBtn = document.getElementById('nirvana-add-lang');
        var tpl = document.getElementById('nirvana-lang-template').innerHTML;

        function reindex(){
            var rows = list.querySelectorAll('.nirvana-lang-row');
            rows.forEach(function(row, idx){
                row.querySelectorAll('input').forEach(function(input, i){
                    var field = ['name','code','icon'][i];
                    input.name = '<?php echo esc_js(NIRVANA_OPT_LANGS); ?>['+idx+']['+field+']';
                });
            });
        }

        addBtn.addEventListener('click', function(){
            var idx = list.children.length;
            var html = tpl.replace('__NAME__', '<?php echo esc_js(NIRVANA_OPT_LANGS); ?>['+idx+'][name]').replace('__CODE__', '<?php echo esc_js(NIRVANA_OPT_LANGS); ?>['+idx+'][code]').replace('__ICON__', '<?php echo esc_js(NIRVANA_OPT_LANGS); ?>['+idx+'][icon]');
            list.insertAdjacentHTML('beforeend', html);
        });

        list.addEventListener('click', function(e){
            if (e.target && e.target.classList.contains('nirvana-remove-lang')){
                e.target.closest('.nirvana-lang-row').remove();
                reindex();
            }
        });
    })();
    </script>
    <?php
}

function nirvana_translate_render_menu_name()
{
    $name = get_option(NIRVANA_OPT_MENU_NAME, '🌐 Language');
    printf('<input type="text" name="%1$s" value="%2$s" class="regular-text" placeholder="例如: 🌐 Language" />', esc_attr(NIRVANA_OPT_MENU_NAME), esc_attr($name));
    echo '<p class="description">' . esc_html__('设置在菜单中显示的翻译按钮名称，默认为 "🌐 Language"。', 'nirvana-translate') . '</p>';
}

// -------------------------------
// 前端：资源加载与初始化
// -------------------------------
function nirvana_translate_enqueue_frontend()
{
    $handle = 'nirvana-translate-js';
    wp_enqueue_script($handle, NIRVANA_TX_URL . 'translate.js', [], null, true);

    $inline = <<<JS
(function(){
  console.warn('Plugin niRvana-translate | Designed by michaelliunsky https://blog.mkliu.top');

  function boot(){
    try{
        translate.log = function(){};
        translate.selectLanguageTag.show = false;
        translate.service.use('client.edge');
        translate.request.api.init = '';
        translate.request.api.connectTest = '';
        translate.listener.start();
        translate.execute();
    }catch(e){
        console.warn('niRvana-translate: init failed', e);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
JS;
    wp_add_inline_script($handle, $inline, 'after');
}
add_action('wp_enqueue_scripts', 'nirvana_translate_enqueue_frontend');

// -------------------------------
// 菜单相关功能
// -------------------------------
function nirvana_translate_render_buttons() {
    $langs = get_option(NIRVANA_OPT_LANGS, []);
    if (empty($langs)) {
        return '<li class="menu-item nirvana-no-langs-message"><span class="ignore">' . __('未配置语言', 'nirvana-translate') . '</span></li>';
    }

    $items = [];
    foreach ($langs as $lang) {
        $name = esc_html($lang['name']);
        $code_js = esc_js($lang['code']);
        $icon = '';
        if (!empty($lang['icon'])) {
            $icon = '<img src="' . esc_url($lang['icon']) . '" alt="' . esc_attr($lang['name']) . '" class="nirvana-lang-icon ignore" />';
        }
        $items[] = '<li class="menu-item menu-item-type-custom menu-item-object-custom nirvana-lang-item"><a href="javascript:translate.changeLanguage(\'' . $code_js . '\');" class="ignore nirvana-lang-link">' . $icon . $name . '</a></li>';
    }
    return implode("\n", $items);
}

function nirvana_translate_register_nav_metabox() {
    add_meta_box(
        'nirvana-translate-metabox',
        __('niRvana翻译菜单', 'nirvana-translate'),
        'nirvana_translate_nav_metabox_cb',
        'nav-menus',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'nirvana_translate_register_nav_metabox');

function nirvana_translate_nav_metabox_cb()
{
    $menu_name = get_option(NIRVANA_OPT_MENU_NAME, '🌐 Language');
    ?>
    <div id="posttype-nirvana-translate" class="posttypediv">
        <div id="tabs-panel-nirvana-translate" class="tabs-panel tabs-panel-active">
            <ul id="nirvana-translate-checklist" class="categorychecklist form-no-clear">
                <li>
                    <label class="menu-item-title">
                        <input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1" /> <?php echo esc_html($menu_name); ?>
                    </label>
                    <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom" />
                    <input type="hidden" class="menu-item-object" name="menu-item[-1][menu-item-object]" value="custom" />
                    <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php echo esc_attr($menu_name); ?>" />
                    <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="#" />
                    <input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="nirvana-translate-menu" />
                    <input type="hidden" class="menu-item-status" name="menu-item[-1][menu-item-status]" value="publish" />
                </li>
            </ul>
        </div>
        <p class="button-controls">
            <span class="add-to-menu">
                <input type="submit" class="button-secondary submit-add-to-menu right" value="<?php echo esc_attr__('添加到菜单', 'nirvana-translate'); ?>" name="add-post-type-menu-item" id="submit-posttype-nirvana-translate" />
                <span class="spinner"></span>
            </span>
        </p>
    </div>
    <?php
}

function nirvana_translate_menu_output($item_output, $item, $depth, $args)
{
    if (in_array('nirvana-translate-menu', $item->classes)) {
        $menu_name = esc_html(get_option(NIRVANA_OPT_MENU_NAME, '🌐 Language'));
        $buttons = nirvana_translate_render_buttons();
        $item_output = '<a href="#" class="ignore">' . $menu_name . ' <i class="fa fa-caret-down" style="margin-left:3px;"></i></a><ul class="sub-menu">' . $buttons . '</ul>';
    }
    return $item_output;
}
add_filter('walker_nav_menu_start_el', 'nirvana_translate_menu_output', 10, 4);

function nirvana_translate_menu_classes($classes, $item, $args, $depth)
{
    if (in_array('nirvana-translate-menu', $classes)) {
        $classes[] = 'menu-item-has-children';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'nirvana_translate_menu_classes', 10, 4);