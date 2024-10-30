<?php

class KwizzmeAutoButtonAdmin
{
    private $options = array();
    private $buttons;

    public function __construct($buttons)
    {
        $this->buttons = $buttons;

        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'create_menu'));
    }

    public function init()
    {
        $this->options = get_option('ab_settings');

        add_settings_section('default', null, array($this, 'create_section'), 'kwizzme_ab');

        add_settings_field('token', __('Publisher Token', 'kab'), array($this, 'create_token_field'), 'kwizzme_ab');
        add_settings_field('type', __('Default Button Type', 'kab'), array($this, 'create_type_field'), 'kwizzme_ab');
        add_settings_field('hint', __('Button Hint', 'kab'), array($this, 'create_hint_field'), 'kwizzme_ab');
        add_settings_field('auto', __('Show Button', 'kab'), array($this, 'create_show_field'), 'kwizzme_ab');
        
        register_setting('kwizzme_ab', 'ab_settings');
    }

    public function create_menu()
    {
        add_menu_page(
            __('Kwizzme Automatic Button', 'kab'),
            __('Kwizzme Button', 'kab'),
            'level_10', 'kwizzme-automatic-button',
            array($this, 'create_page'),
            plugin_dir_url(__FILE__) . '/menu_icon.png'
        );
    }

    public function create_page()
    {
        ?>
            <div class="wrap">
                <h2><?php _e('Kwizzme Automatic Button', 'kab') ?></h2>
                <form method="post" action="options.php">
                    <?php
                        settings_fields('kwizzme_ab');
                        do_settings_sections('kwizzme_ab');

                        ?>
                            <p class="description">
                                <?php _e('Please <a href="mailto:johannes.leisch@kwizzme.com">contact kwizzme</a> for more ways to display the button.', 'kab'); ?>
                            </p>
                        <?php

                        if(function_exists('submit_button')) {
                            submit_button();
                        } else {
                            ?>
                                <p class="submit">
                                    <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>">
                                </p>
                            <?php
                        }
                    ?>
                </form>
            </div>
        <?php
    }

    public function create_section()
    {
        ?>
            <style>
                .kab-input {
                    width: 350px;
                }

                img.kab-preview {
                    display: block;
                    background: #fff;
                    padding: 25px;
                    margin: 25px 1px 1px 1px;
                    border: 1px solid #ddd;
                    -webkit-box-shadow: inset 0 0 1px rgba(0, 0, 0, 0.1);
                    box-shadow: inset 0 0 1px rgba(0, 0, 0, 0.1);
                }
            </style>
        <?php
    }

    public function create_show_field()
    {
        ?>
            <input type="checkbox" id="kab_auto" name="ab_settings[auto]" value="1" <?php checked($this->options['auto'], 1); ?>>
            <label for="kab_auto"><?php _e('Show button in all posts', 'kab') ?></label><br/>
            <p class="description">
                <?php _e('The button will be automatically added to the bottom of all posts.', 'kab') ?><br/>
            </p><br/>
            <input type="checkbox" id="kab_index" name="ab_settings[index]" value="1" <?php checked($this->options['index'], 1); ?>>
            <label for="kab_index"><?php _e('Show the button in post list', 'kab') ?></label><br/>
            <p class="description">
                <?php _e('The button will be displayed in post list.', 'kab') ?><br/>
            </p>

            <script>
                (function($) {
                    function update() {
                        if($('#kab_auto').is(':checked')) {
                            $('#kab_index').removeAttr('disabled');
                        } else {
                            $('#kab_index').attr('disabled', 'disabled').removeAttr('checked');
                        }
                    }

                    $(document).ready(update);
                    $('#kab_auto').change(update);
                })(jQuery);
            </script>
        <?php
    }

    public function create_token_field()
    {
        ?>
            <input maxlength="32" type="text" id="kab_token" name="ab_settings[token]" value="<?php echo isset($this->options['token']) ? esc_attr($this->options['token']) : ''; ?>" class="kab-input"/><br/>
            <p class="description"><?php _e('Paste the 32 character code you received from Kwizzme Team.', 'kab') ?></p>
        <?php
    }

    public function create_hint_field()
    {
        ?>
            <input type="checkbox" id="kab_hint" name="ab_settings[hint]" value="1" <?php checked($this->options['hint'], 1); ?>>
            <label for="kab_hint"><?php _e('Show button hint', 'kab') ?></label><br/>
            <p class="description">
                <?php _e('Show explanation hint above the button.', 'kab') ?><br/>
            </p>
        <?php
    }

    public function create_type_field()
    {
        ?>
            <select id="kab_type" name="ab_settings[type]" class="kab-input">
                <?php foreach($this->buttons as $id => $pictureUrl): ?>
                    <option value="<?php echo $id; ?>" data-url="<?php echo $pictureUrl ?>" <?php if($id == $this->options['type']) echo 'selected'; ?>>
                        <?php echo $id; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <img id="kab_preview" class="kab-preview"/>
            <script>
                (function($) {
                    function kab_show_button_preview() {
                        var field = $('#kab_type'),
                            url = field.find('option:selected').attr('data-url'),
                            image = $('#kab_preview');

                        image.attr('src', url);
                    }

                    kab_show_button_preview();

                    $('#kab_type').change(kab_show_button_preview);
                })(jQuery);
            </script>
        <?php
    }
}

