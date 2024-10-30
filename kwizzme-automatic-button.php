<?php
/**
 * Plugin Name: Kwizzme Automatic Button
 * Plugin URI: http://kwizzme.com/ab/doc/kwizzme_auto_button.html
 * Description: A plugin for embedding the Kwizzme Automatic Button.
 * Version: 1.1.4
 * Author: kwizzme GmbH
 * Author URI: http://kwizzme.com
 * License: GPL2
 */

require_once(plugin_dir_path( __FILE__ )  . 'admin.php');

class KwizzmeAutoButtonPlugin
{
    private $admin;
    private $defaultOptions = array(
        'type' => 'full-v1',
        'auto' => true,
        'index' => true,
        'hint' => true,
    );
    private $options = array();
    private $baseUrl = 'http://kwizzme.com';
    private $buttonPath = '/ab/buttons/';
    private $buttons = array(
        'button-16',
        'button-24',
        'button-32',
        'button-48',
        'content-v1',
        'content-v2',
        'content-v3',
        'full-v1',
        'full-v2',
        'full-v3',
        'half-v1',
        'half-v2',
        'half-v3',
        'icon-16',
        'icon-24',
        'icon-32',
        'icon-48',
        'icon-64',
        'skyscraper-v1',
        'skyscraper-v2',
        'skyscraper-v3',
        'square-v1',
        'square-v2',
        'square-v3',
        'vertical-a-v1',
        'vertical-a-v2',
        'vertical-a-v3',
        'vertical-b-v1',
        'vertical-b-v2',
        'vertical-b-v3',
    );

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'));
    }

    public function on_plugins_loaded()
    {
        load_plugin_textdomain('kab', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function init()
    {
        add_option('ab_settings', $this->defaultOptions, null, true);

        /* array_merge in case sb updated the plugins and array may be missing some options */
        $this->options = get_option('ab_settings');

        if(!array_key_exists('index', $this->options)) {
            $this->options['index'] = false;
        }

        if(!array_key_exists('auto', $this->options)) {
            $this->options['auto'] = false;
        }

        if(!array_key_exists('hint', $this->options)) {
            $this->options['hint'] = false;
        }

        add_action('wp_footer', array($this, 'render_js_widget'));

        if(is_admin()) {
            $this->admin = new KwizzmeAutoButtonAdmin($this->get_buttons());
        }

        if($this->is_in_auto_mode() && $this->is_enabled()) {
            add_filter('the_content', array($this, 'content_filter'));
        }

        add_shortcode('kwizzme-button', array($this, 'render_shortcode'));
    }

    public function render_js_widget()
    {
        if(!$this->is_enabled()) {
            echo '<!-- ' . __('No token set for Kwizzme Auto Button!', 'kab') . ' -->';
            return;
        }

        ?>
            <script>
                (function () {
                    window.kwizzme = {
                        accessToken: '<?php echo $this->options['token']; ?>',
                        baseUrl: '<?php echo $this->baseUrl; ?>'
                    };
                    var kwz = document.createElement('script');
                    kwz.type = 'text/javascript';
                    kwz.async = true;
                    kwz.src = '<?php echo $this->baseUrl; ?>/ab/button.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(kwz, s);
                })();
            </script>
        <?php
    }

    public function get_buttons()
    {
        $buttons = array();

        foreach($this->buttons as $buttonId) {
            $buttons[$buttonId] = $this->baseUrl . $this->buttonPath . $buttonId . '.png';
        }

        return $buttons;
    }

    protected function render_button($type)
    {
        return sprintf('<span data-kwizzme-post-url="%s" data-kwizzme-post-title="%s" data-kwizzme-ad-type="%s" data-kwizzme-post-date="%s" data-kwizzme-show-hint="%s"></span>',
            get_permalink(),
            get_the_title(),
            $type,
            get_the_date('d.m.Y'),
            $this->options['hint'] ? 'yes' : 'no'
        );
    }

    public function content_filter($content)
    {
        if(!$this->options['index'] && !is_single()) {
            return $content;
        }

        return $content . '<div style="margin: 5px 0;" class="kwizzme-want-button-box">' . $this->render_button($this->options['type']) . '</div>';
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'type' => $this->options['type'],
        ), $atts);

        if(!in_array($atts['type'], $this->buttons)) {
            return sprintf('<span style="color:#f00;">' . __('Unknown button type "%s" in shortcode [kwizzme-button]!', 'kab') . '</span>', $atts['type']);
        }

        if(!in_the_loop()) {
            return '<span style="color:#f00;">' . __('The [kwizzme-button] shortcode works only in posts!', 'kab') . '</span>';
        }

        return $this->render_button($atts['type']);
    }

    protected function is_enabled()
    {
        return isset($this->options['token']) || !empty($this->options['token']);
    }

    protected function is_in_auto_mode()
    {
        return isset($this->options['auto']) && $this->options['auto'];
    }
}

$kab_plugin = new KwizzmeAutoButtonPlugin();

