<?php
/*
 * Plugin Name:       niRvana-translate 多语言翻译插件
 * Plugin URI:        https://blog.mkliu.top/536.html
 * Description:       基于 translate.js 的 WordPress 多语言翻译插件.
 * Version:           1.0.0
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
define('NIRVANA_TX_PATH', plugin_dir_path(__FILE__));
define('NIRVANA_TX_URL', plugin_dir_url(__FILE__));
define('NIRVANA_OPT_GROUP', 'nirvana_translate_options');
define('NIRVANA_OPT_LANGS', 'nirvana_translate_languages');
define('NIRVANA_OPT_MENU_NAME', 'nirvana_translate_menu_name');
define('NIRVANA_OPT_SCRIPT_SRC', 'nirvana_translate_script_source');
define('NIRVANA_OPT_REMOTE_URL', 'nirvana_translate_remote_url');

// -------------------------------
// Activation / Deactivation
// -------------------------------
/**
 * 清理缓存
 *
 * @return void
 */
function nirvana_translate_clear_cache()
{
    wp_cache_flush();
}
register_activation_hook(__FILE__, 'nirvana_translate_clear_cache');
register_deactivation_hook(__FILE__, 'nirvana_translate_clear_cache');

// -------------------------------
// 插件链接：在插件列表中显示“设置”链接
// -------------------------------
/**
 * 在插件列表添加快速设置链接。
 *
 * @param array $links 现有链接
 * @return array $links 添加后的链接数组
 */
function nirvana_translate_settings_link($links)
{
    $settings_link = sprintf('<a href="%s">%s</a>', admin_url('options-general.php?page=nirvana-translate-plugin'), __('设置', 'nirvana-translate'));
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nirvana_translate_settings_link');

// -------------------------------
// 管理页面：菜单与设置注册
// -------------------------------
/**
 * 添加后台菜单。
 *
 * @return void
 */
function nirvana_translate_admin_menu()
{
    add_menu_page(
        __('niRvana 翻译设置', 'nirvana-translate'),
        __('多语言翻译', 'nirvana-translate'),
        'manage_options',
        'nirvana-translate-plugin',
        'nirvana_translate_settings_page',
        'dashicons-translation',
        61 // 放置在外观附近
    );
}
add_action('admin_menu', 'nirvana_translate_admin_menu');

/**
 * 注册设置项与字段。
 *
 * @return void
 */
function nirvana_translate_register_settings()
{
    register_setting(NIRVANA_OPT_GROUP, NIRVANA_OPT_LANGS, 'nirvana_translate_sanitize_languages');
    register_setting(NIRVANA_OPT_GROUP, NIRVANA_OPT_MENU_NAME, 'sanitize_text_field');
    register_setting(NIRVANA_OPT_GROUP, NIRVANA_OPT_SCRIPT_SRC, 'sanitize_text_field');
    register_setting(NIRVANA_OPT_GROUP, NIRVANA_OPT_REMOTE_URL, 'nirvana_translate_sanitize_remote_url');

    add_settings_section('nirvana_translate_section_main', __('语言与脚本设置', 'nirvana-translate'), null, 'nirvana-translate-plugin');

    add_settings_field(NIRVANA_OPT_LANGS, __('翻译语言设置', 'nirvana-translate'), 'nirvana_translate_render_languages', 'nirvana-translate-plugin', 'nirvana_translate_section_main');
    add_settings_field(NIRVANA_OPT_MENU_NAME, __('菜单显示名称', 'nirvana-translate'), 'nirvana_translate_render_menu_name', 'nirvana-translate-plugin', 'nirvana_translate_section_main');
    add_settings_field(NIRVANA_OPT_SCRIPT_SRC, __('translate.js 引入方式', 'nirvana-translate'), 'nirvana_translate_render_script_source', 'nirvana-translate-plugin', 'nirvana_translate_section_main');
}
add_action('admin_init', 'nirvana_translate_register_settings');

/**
 * 后台设置页面 HTML 输出。
 *
 * @return void
 */
function nirvana_translate_settings_page()
{
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('niRvana-theme 多语言翻译插件设置', 'nirvana-translate'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields(NIRVANA_OPT_GROUP);
            do_settings_sections('nirvana-translate-plugin');
            submit_button();
            ?>

            <div class="notice notice-info" style="margin-top:16px;padding:12px 16px;">
                <p style="margin:0 0 8px;"><strong><?php echo esc_html__('使用引导', 'nirvana-translate'); ?></strong></p>
                <ol style="margin:0 0 8px 20px;">
                    <li><?php echo esc_html__('在“翻译语言设置”添加语言并保存。', 'nirvana-translate'); ?></li>
                    <li><?php echo esc_html__('选择 translate.js 引入方式：本地（插件内置）或远程（自定义 URL，提供远程加载失败回退本地）。', 'nirvana-translate'); ?></li>
                    <li><?php echo esc_html__('到【外观 → 菜单】→ 左侧 niRvana翻译菜单 → 勾选并“添加到菜单”。', 'nirvana-translate'); ?></li>
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
/**
 * 校验自定义语言数组。
 * 每项必须包含 name 与 code，可选 icon（URL）。
 *
 * @param mixed $input
 * @return array
 */
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

/**
 * 校验远程 URL（只允许 http/https 且路径含 .js）。
 *
 * @param string $url
 * @return string
 */
function nirvana_translate_sanitize_remote_url($url)
{
    $url = trim($url);
    if (empty($url)) {
        return '';
    }
    $url = esc_url_raw($url);
    if (!preg_match('#^https?://#i', $url)) {
        return '';
    }
    $path = parse_url($url, PHP_URL_PATH);
    if ($path === null || strpos($path, '.js') === false) {
        return '';
    }
    return $url;
}

// -------------------------------
// 设置字段渲染：语言列表（ JS ）
// -------------------------------
/**
 * 输出可增删的语言配置 UI。
 *
 * @return void
 */
function nirvana_translate_render_languages()
{
    $langs = get_option(NIRVANA_OPT_LANGS, []);
    ?>
    <div id="nirvana-languages-wrap">
        <div id="nirvana-languages-list">
            <?php if (!empty($langs) && is_array($langs)): foreach ($langs as $i => $lang): ?>
                <div class="nirvana-lang-row" style="margin-bottom:10px;">
                    <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_LANGS); ?>[<?php echo $i; ?>][name]" value="<?php echo esc_attr($lang['name']); ?>" placeholder="语言名称（自定义）" />
                    <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_LANGS); ?>[<?php echo $i; ?>][code]" value="<?php echo esc_attr($lang['code']); ?>" placeholder="语言简码" />
                    <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_LANGS); ?>[<?php echo $i; ?>][icon]" value="<?php echo esc_attr($lang['icon']); ?>" placeholder="图标 URL (可空)" />
                    <button type="button" class="button nirvana-remove-lang">删除</button>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <button type="button" class="button button-primary" id="nirvana-add-lang"><?php echo esc_html__('添加语言', 'nirvana-translate'); ?></button>

        <!-- 模板 -->
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
            var div = document.createElement('div');
            div.innerHTML = html;
            list.appendChild(div.firstElementChild);
        });

        // 事件委托处理删除
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

/**
 * 菜单显示名称字段渲染
 *
 * @return void
 */
function nirvana_translate_render_menu_name()
{
    $name = get_option(NIRVANA_OPT_MENU_NAME, '🌐 Language');
    printf('<input type="text" name="%1$s" value="%2$s" class="regular-text" placeholder="例如: 🌐 Language" />', esc_attr(NIRVANA_OPT_MENU_NAME), esc_attr($name));
    echo '<p class="description">' . esc_html__('设置在菜单中显示的翻译按钮名称，默认为 “🌐 Language”。', 'nirvana-translate') . '</p>';
}

/**
 * 脚本来源字段渲染
 *
 * @return void
 */
function nirvana_translate_render_script_source()
{
    $src = get_option(NIRVANA_OPT_SCRIPT_SRC, 'local');
    $remote = get_option(NIRVANA_OPT_REMOTE_URL, 'https://cdn.staticfile.net/translate.js/3.18.0/translate.js');
    ?>
    <fieldset>
        <label><input type="radio" name="<?php echo esc_attr(NIRVANA_OPT_SCRIPT_SRC); ?>" value="local" <?php checked($src, 'local'); ?> /> <?php echo esc_html__('本地（插件内置 translate.js）', 'nirvana-translate'); ?></label><br />
        <label><input type="radio" name="<?php echo esc_attr(NIRVANA_OPT_SCRIPT_SRC); ?>" value="remote" <?php checked($src, 'remote'); ?> /> <?php echo esc_html__('远程（自定义 URL）', 'nirvana-translate'); ?></label><br />
        <input type="text" name="<?php echo esc_attr(NIRVANA_OPT_REMOTE_URL); ?>" value="<?php echo esc_attr($remote); ?>" class="regular-text code" placeholder="https://.../translate.js" style="max-width:480px;" />
        <p class="description"><?php echo esc_html__('建议使用可信、可稳定访问的 HTTPS 源。远程加载失败时自动回退到本地。', 'nirvana-translate'); ?></p>
    </fieldset>
    <?php
}

// -------------------------------
// 前端：资源加载与初始化
// -------------------------------
/**
 * 在前端加载 translate.js（远程优先并回退本地）
 *
 * @return void
 */
function nirvana_translate_enqueue_frontend()
{
    $src = get_option(NIRVANA_OPT_SCRIPT_SRC, 'local');
    $remote = get_option(NIRVANA_OPT_REMOTE_URL, 'https://cdn.staticfile.net/translate.js/3.18.0/translate.js');
    $handle = 'nirvana-translate-js';

    if ($src === 'remote' && !empty($remote)) {
        wp_enqueue_script($handle, esc_url($remote), [], null, true);
    } else {
        wp_enqueue_script($handle, NIRVANA_TX_URL . 'translate.js', [], '1.0.0', true);
    }

    // 初始化脚本：如果远程未定义 translate，则动态加载本地并执行 boot
    $fallback = NIRVANA_TX_URL . 'translate.js';
    $inline = <<<JS
(function(){
  // Plugin info
  console.warn('Plugin niRvana-translate | Designed by michaelliunsky https://blog.mkliu.top');

  function boot(){
    try{
      translate.selectLanguageTag.show=false;
      translate.service.use('client.edge');
      translate.listener.start();
      translate.execute();
    }catch(e){}
  }
  document.addEventListener('DOMContentLoaded', function(){
    if (typeof window.translate === 'undefined'){
      var s = document.createElement('script');
      s.src = '{$fallback}';
      s.onload = boot;
      document.head.appendChild(s);
    } else { boot(); }
  });
})();
JS;
    wp_add_inline_script($handle, $inline);

    wp_register_style('nirvana-translate-inline', false);
    wp_enqueue_style('nirvana-translate-inline');
    $css = 
        '.menu-item-language{position:relative}.menu-item-language>.sub-menu{display:none;position:absolute;left:0;top:100%;z-index:9999;min-width:180px;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.08)}.menu-item-language:hover>.sub-menu{display:block}.menu-item-language .sub-menu li a{display:block;padding:8px 12px}';
    wp_add_inline_style('nirvana-translate-inline', $css);
}
add_action('wp_enqueue_scripts', 'nirvana_translate_enqueue_frontend');

/**
 * 生成语言按钮 HTML，可返回下拉项或并列按钮列表
 *
 * @param string $type 'dropdown'|'inline'
 * @return string
 */
function nirvana_translate_render_buttons($type = 'dropdown')
{
    $langs = get_option(NIRVANA_OPT_LANGS, []);
    if (empty($langs)) {
        return '<li class="menu-item"><span class="ignore" style="display:block;padding:8px 12px;opacity:.7;">未配置语言</span></li>';
    }

    $items = [];
    foreach ($langs as $lang) {
        $name = esc_html($lang['name']);
        $code = esc_attr($lang['code']);
        $icon = !empty($lang['icon']) ? '<img src="' . esc_url($lang['icon']) . '" alt="' . $name . '" style="width:20px;height:15px;vertical-align:middle;margin-right:8px;border:1px solid #ddd;" class="ignore" />' : '';
        if ($type === 'dropdown') {
            $items[] = '<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="javascript:translate.changeLanguage(\'' . $code . '\');" class="ignore">' . $icon . $name . '</a></li>';
        } else {
            $items[] = '<li style="display:flex;align-items:center;list-style:none;padding:0 5px;margin:0;"><a href="javascript:translate.changeLanguage(\'' . $code . '\');" style="display:flex;align-items:center;padding:3px 8px;background-color:#0073aa;color:#fff;border-radius:5px;text-decoration:none;" class="ignore">' . $icon . $name . '</a></li>';
        }
    }

    if ($type === 'dropdown') {
        return implode("\n", $items);
    }
    return '<ul style="list-style:none;padding:0;margin:0;display:flex;gap:15px;flex-wrap:wrap;justify-content:center;">' . implode("\n", $items) . '</ul>';
}

/**
 * 在 nav-menus 页面添加自定义 metabox，允许一键添加翻译按钮到菜单
 * 使用 admin_head-nav-menus.php 钩子保证只在菜单管理页执行
 */
function nirvana_translate_register_nav_metabox()
{
    add_meta_box('nirvana-translate-metabox', __('niRvana翻译菜单', 'nirvana-translate'), 'nirvana_translate_nav_metabox_cb', 'nav-menus', 'side', 'default');
}
add_action('admin_head-nav-menus.php', 'nirvana_translate_register_nav_metabox');

/**
 * metabox 内容回调
 */
function nirvana_translate_nav_metabox_cb()
{
    $menu_name = esc_attr(get_option(NIRVANA_OPT_MENU_NAME, '🌐 Language'));
    ?>
    <div id="posttype-nirvana-translate" class="posttypediv">
        <div id="tabs-panel-nirvana-translate" class="tabs-panel tabs-panel-active">
            <ul id="nirvana-translate-checklist" class="categorychecklist form-no-clear">
                <li>
                    <label class="menu-item-title">
                        <input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1" /> <?php echo $menu_name; ?>
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

/**
 * 当菜单项包含特定类时，输出语言下拉 HTML（hook 于 walker_nav_menu_start_el）
 *
 * @param string $item_output
 * @param object $item
 * @param int $depth
 * @param object $args
 * @return string
 */
function nirvana_translate_menu_output($item_output, $item, $depth, $args)
{
    if (is_array($item->classes) && in_array('nirvana-translate-menu', $item->classes)) {
        $menu_name = esc_html(get_option(NIRVANA_OPT_MENU_NAME, '🌐 Language'));
        $buttons = nirvana_translate_render_buttons('dropdown');
        $item_output = '<a href="#" class="ignore">' . $menu_name . '</a><ul class="sub-menu">' . $buttons . '</ul>';
    }
    return $item_output;
}
add_filter('walker_nav_menu_start_el', 'nirvana_translate_menu_output', 10, 4);

/**
 * 为菜单项添加必要的 class，触发主题对子菜单样式的处理
 */
function nirvana_translate_menu_classes($classes, $item, $args, $depth)
{
    if (is_array($classes) && in_array('nirvana-translate-menu', $classes)) {
        $classes[] = 'menu-item-has-children';
        $classes[] = 'menu-item-language';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'nirvana_translate_menu_classes', 10, 4);

/**
 * 为所有页面添加 no-cache 头以避免翻译缓存问题
 *
 * @param array $headers
 * @return array
 */
function nirvana_translate_prevent_cache($headers)
{
    $headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    $headers['Pragma'] = 'no-cache';
    $headers['Expires'] = '0';
    return $headers;
}
add_filter('wp_headers', 'nirvana_translate_prevent_cache');
