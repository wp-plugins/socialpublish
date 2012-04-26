<?php if ($account === null) { ?>
<div>
    <p><?php echo __('It seems you have not setup your SocialPublish <code>access_token</code> yet.') . ' ' . sprintf(__('You must <a href="%1$s">enter your SocialPublish access_token</a> for it to work.', 'socialpublish'), "options-general.php?page=socialpublish-key-config") ?></p>
</div>
<?php } else { ?>
<p>Your blog post will be shared on the social media accounts you select below when the post is published the first time.</p>
<p>
    Hashtags <span style="font-size: .8em">(optional)</span>: <input type="text" name="socialpublish_hashtags" class="newtag form-input-tip" size="16" autocomplete="off" value="<?php echo $post->getHashTags(); ?>"> hashtags will be appended to your message when published to Twitter
</p>
<p>
    Message <span style="font-size: .8em">(optional)</span>: <input type="text" name="socialpublish_message" class="newtag form-input-tip" style="width: 100%; max-width: 400px" autocomplete="off" value="<?php echo $post->getMessage(); ?>">
</p>
<table class="wp-list-table widefat plugins" cellspacing="0">
    <thead>
        <tr>
            <th style="width: 10px"></th>
            <th scope="col" id="name" class="manage-column" style="width: 100px"><?php printf(__('Type', 'socialpublish'))?></th>
            <th scope="col" id="description" class="manage-column" style=""><?php printf(__('Account Name', 'socialpublish'))?></th>
        </tr>
        <?php foreach($account->getHubs() as $hub) { ?>
        <tr>
            <?php
                $checked = false;
                foreach ($post->getHubs() as $ph) {
                    if ($ph->getKey() === $hub->getKey()) {
                        $checked = true;
                    }
                }
            ?>
            <td><input type="checkbox" <?php echo $checked ? 'checked' : '' ?> name="socialpublish_hubs[]" value="<?php echo $hub->getKey(); ?>"></td>
            <td><?php echo $hub->getType(); ?></td>
            <td><?php echo $hub->getName(); ?></td>
        </tr>
        <?php } ?>
    </thead>
</table>
<?php } ?>