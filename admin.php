<?php
add_action('admin_menu', 'socialpublish_config_page');

// When the plugin is being deactivated, the registered options will be removed...
// The access_token will be removed on deactivation.
register_deactivation_hook(__SOCIALPUBLISH_FILE__, 'socialpublish_deactivate'); // 2.0 compatible

function socialpublish_deactivate() {
    delete_option('socialpublish_hubs');
    delete_option('socialpublish_access_token');
}

function socialpublish_config_page() {
	if (function_exists('add_submenu_page')) { // unknown when introduced?
		add_submenu_page('plugins.php', __('Socialpublish Configuration', 'socialpublish'), __('Socialpublish Config', 'socialpublish'), 'manage_options', 'socialpublish-key-config', 'socialpublish_conf');
	}
}

function socialpublish_access_token_warning() {
	echo "<div class='updated fade'><p><strong>".__('Socialpublish is almost ready.', 'socialpublish')."</strong> ".sprintf(__('You must <a href="%1$s">enter your Socialpublish access_token</a> for it to work.', 'socialpublish'), "plugins.php?page=socialpublish-key-config")."</p></div>";
}

// If no access token is set yet, add a message to every admin page requesting the user to configurate the plugin
if (!get_option('socialpublish_access_token') && $_POST['action'] !== 'update_access_token') {
	add_action('admin_notices', 'socialpublish_access_token_warning');
}

add_action('add_meta_boxes', 'socialpublish_initialize_meta_boxes');

function socialpublish_initialize_meta_boxes() {
    add_meta_box(
        'socialpublish_post_fields',
        __( 'Socialpublish', 'myplugin_textdomain' ),
        'socialpublish_post_fields_renderer',
        'post'
    );
}

function socialpublish_post_fields_renderer($post) {
    $hubs = get_option('socialpublish_hubs');
    if ($hubs !== false) {
        $hubs = json_decode($hubs, true);

        if (sizeof($hubs) === 0) {
            $ms[] = 'no_hubs';
        }
    }
}

function socialpublish_conf() {
    $ms = array();

    if (isset($_POST['submit'])) {

        if ($_POST['action'] === 'update_access_token') {

            // Check intention to change the access_token.
            // @see: http://markjaquith.wordpress.com/2006/06/02/wordpress-203-nonces/
            check_admin_referer('socialpublish_update_access_token');

            $accessToken = $_POST['access_token'];

            $valid = false;

            if ($accessToken === '') {
                delete_option('socialpublish_access_token');
                delete_option('socialpublish_hubs');
            } else {
                if (!preg_match('/^[A-Za-z0-9]+$/', $accessToken)) {
                    $ms[] = 'access_token_invalid';
                } else {
                    $sp = new Socialpublish($accessToken);
                    $result = $sp->api('/hubs', 'GET');

                    if ($result['success'] === true) {
                        update_option('socialpublish_hubs', json_encode($result));
                        update_option('socialpublish_access_token', $accessToken);

                        $valid = true;

                        $ms[] = 'access_token_updated';
                    } else {
                        $ms[] = 'access_token_invalid';
                    }
                }
            }

            if (!$valid) {
                add_action('admin_notices', 'socialpublish_access_token_warning');
            }
        }

    }

    $hubs = get_option('socialpublish_hubs');
    if ($hubs !== false) {
        $hubs = json_decode($hubs, true);

        if (sizeof($hubs) === 0) {
            $ms[] = 'no_hubs';
        }
    }

    $accessToken = get_option('socialpublish_access_token');

    if ($accessToken === false) {
        $accessToken = '';
        $ms[] = 'access_token_empty';
    }

	$messages = array(
	    'no_hubs' => array('color' => 'FEE', 'border' => 'F00', 'text' => sprintf(__('Although your <code>access_token</code> is valid, it seems you have not attached any social media platform to your account yet. You can <a href="%1$s">manage your account</a> by visiting socialpublish.io.'), SOCIALPUBLISH_URI)),
		'access_token_empty' => array('color' => 'ffd', 'border' => '990', 'text' => __('Please enter your <code>access_token</code>.', 'socialpublish')),
		'access_token_updated' => array('color' => 'dfd', 'border' => '090', 'text' => __('Your <code>access_token</code> has been updated. Happy blogging!', 'socialpublish')),
		'access_token_invalid' => array('color' => 'FEE', 'border' => 'F00', 'text' => __('The <code>access_token</code> you provided is not valid. An <code>access_token</code> may only contain numbers or letters.', 'socialpublish'))
	);

?>
<div class="wrap">
    <h2><?php _e('Socialpublish.io Configuration'); ?></h2>

    <div class="narrow">
    <div style="margin: auto; width: 400px;">
        <form method="post">
        <?php
            if (function_exists('wp_nonce_field')) {
                wp_nonce_field('socialpublish_update_access_token');
            }
        ?>
        <p><?php printf(__('<a href="%1$s">Socialpublish</a> greatly simplifies the publishing of your blog posts to the social media platforms of your choosings. If you don\'t have a Socialpublish account yet, you first have to <a href="%1$s">create an account</a>.', 'socialpublish'), SOCIALPUBLISH_URI); ?></p>

            <h3><label for="key"><?php _e('Socialpublish access_token'); ?></label></h3>
            <?php foreach ($ms as $m) { ?>
                <p style="padding: .5em; background-color: #<?php echo $messages[$m]['color']; ?>; border: 1px solid #<?php echo $messages[$m]['border']; ?>; color: #000"><?php echo $messages[$m]['text']; ?></p>
            <?php } ?>
            <p>
                access_token: <input id="access_token" name="access_token" value="<?php echo $accessToken; ?>" type="text" style="font-family: 'Courier New', Courier, mono; font-size: 1.5em;">
            </p>
            <p class="submit">
                <input type="hidden" name="action" value="update_access_token">
                <input type="submit" name="submit" value="Save access_token »">
            </p>
        </form>

        <?php if ($hubs !== false && sizeof($hubs['hubs']) > 0) { ?>

            <p><?php printf(__('The following social media platform accounts are attached to your Socialpublish account. You can <a href="%1$s">manage your account</a> by visiting socialpublish.io.', 'socialpublish'), SOCIALPUBLISH_URI)?></p>

            <table class="wp-list-table widefat plugins" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col" id="name" class="manage-column" style="width: 100px"><?php printf(__('Type', 'socialpublish'))?></th>
                        <th scope="col" id="description" class="manage-column" style=""><?php printf(__('Account Name', 'socialpublish'))?></th>
                    </tr>
                    <?php foreach($hubs['hubs'] as $hub) { ?>
                    <tr>
                        <td><?php echo $hub['type']; ?></td>
                        <td><?php echo $hub['name']; ?></td>
                    </tr>
                    <?php } ?>
                </thead>
            </table>
         <?php } ?>
    </div>
    </div>
</div>
<?php
}
?>