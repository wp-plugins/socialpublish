    <div class="wrap">
        <div id="icon-options-general" class="icon32"><br></div>
        <h2><?php _e('SocialPublish Settings', 'socialpublish'); ?></h2>
        <?php if (isset($error_message)) { ?>
        <div class="error">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php } ?>
        <?php if (isset($success_message)) { ?>
        <div class="updated">
            <p><?php echo $success_message; ?></p>
        </div>
        <?php } ?>
        <form method="post">
            <?php
                if (function_exists('wp_nonce_field')) {
                    wp_nonce_field('socialpublish_update_access_token');
                }
            ?>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">access_token:</th>
                        <td>
                            <input name="access_token" type="text" class="regular-text">
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="hidden" name="action" value="update_access_token">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes">
            </p>
        </form>
        <?php if ($account !== null) { ?>
        <h3>Connected social media accounts</h3>
        <?php if (sizeof($account->getHubs()) === 0) { ?>
        <p>It seems you have not connected any social media account to your SocialPublish account. Please take the following three steps:</p>
        <ol>
            <li><?php printf(__('go to <a href="%s">socialpublish.io</a>'), SOCIALPUBLISH_URI); ?></li>
            <li>connect the social media accounts you want to distribute your posts to and make sure at least one social media account is enabled
            <li>come back, and reload your <code>access_token</code></li>
        </ol>
        <?php } else { ?>
        <p><?php printf(__('The following social media accounts are connected to your SocialPublish account. To add or delete accounts, please <a href="%s">visit the SocialPublish website</a>.'), SOCIALPUBLISH_URI); ?></p>
        <table class="wp-list-table widefat plugins" cellspacing="0">
            <thead>
                <tr>
                    <th scope="col" id="name" class="manage-column" style="width: 100px"><?php printf(__('Type', 'socialpublish'))?></th>
                    <th scope="col" id="description" class="manage-column" style=""><?php printf(__('Account Name', 'socialpublish'))?></th>
                </tr>
                <?php foreach($account->getHubs() as $hub) { ?>
                <tr>
                    <td><?php echo $hub->getType(); ?></td>
                    <td><?php echo $hub->getName(); ?></td>
                </tr>
                <?php } ?>
            </thead>
        </table>
        <?php } ?>
        <?php } ?>
    </div>