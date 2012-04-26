<?php

require_once __SOCIALPUBLISH_ROOT__ . '/mvc/template/SocialpublishTemplate.php';

class SocialpublishBootstrap
{
    private static $instance;

    protected $service;

    public static function loadPlugin(SocialpublishService $service) {
        if (self::$instance === null) {
            self::$instance = new SocialpublishBootstrap($service);
        }

        return self::$instance;
    }

    protected function __construct(SocialpublishService $service) {
        $this->service = $service;

        // Assumption: all the actions added in the callback on_initialize are only called
        // if the user has the right to do so... If not this is a security issue that needs
        // to be resolved.
        add_action('init', array($this, 'on_initialize'));

        if (is_admin()) {
            // at least these actions should only appear with administrator rights
            add_action('admin_menu', array($this, 'on_admin_menu'));

            if (!$this->service->hasAccount()) {
                add_action('admin_notices', array($this, 'on_admin_notices'));
            }

            if (!SocialpublishHTTP::getInstance()->hasStrategy()) {
                add_action('admin_notices', array($this, 'on_no_http_strategy_admin_notices'));
            }
        }
    }

    function on_admin_notices() {
        // refactor to template?
        echo "<div class='updated fade'><p><strong>".__('SocialPublish is almost ready.', 'socialpublish')."</strong> ".sprintf(__('You must <a href="%1$s">enter your Socialpublish access_token</a> for it to work. You can find your access_token on your Dashboard on the SocialPublish website.', 'socialpublish'), "options-general.php?page=socialpublish-key-config")."</p></div>";
    }

    function on_no_http_strategy_admin_notices() {
        // refactor to template?
        echo "<div class='updated fade'><p>".__('SocialPublish can not be used on your server configuration. Error: no_http_strategy_available', 'socialpublish')."</p></div>";
    }

    public function on_initialize() {
        /*
         * @event: socialpublish_publish_post
         *
         * The event gets triggered when a post is newly published, either immediately,
         * or when it is scheduled. It won't be triggered on updating a post which has
         * been published already.
         */
        add_action('socialpublish_publish_post', array($this, 'on_socialpublish_publish_post'), 10, 1);
        add_action('socialpublish_render_settings', array($this, 'on_socialpublish_render_settings'), 10, 1);
        add_action('socialpublish_add_meta_box', array($this, 'on_socialpublish_add_meta_box'), 10, 1);

        // make sure we save the post first, before calling the on_publish_post callback
        // 100 < 200, so on_social_publish_save_post will be called before on_publish_post
        add_action('publish_post', array($this, 'on_socialpublish_save_post'), 100, 1);
        add_action('publish_post', array($this, 'on_publish_post'), 200, 1);
        add_action('publish_future_post', 'on_publish_future_post', 10, 1);

        add_action('add_meta_boxes', array($this, 'on_add_meta_boxes'));
        add_action('save_post', array($this, 'on_save_post'), 10, 2 );
        add_action('socialpublish_save_post', array($this, 'on_socialpublish_save_post'), 10, 2);

        register_deactivation_hook(__SOCIALPUBLISH_FILE__, array($this, 'on_socialpublish_cleanup'));
        register_activation_hook  (__SOCIALPUBLISH_FILE__, array($this, 'on_socialpublish_cleanup'));
    }

    /*
     * This callback is triggered when the publish_post event is fired.
     * WordPress triggers the publish_post event whenever a post is published,
     * or if it is edited while its status is 'published'.
     *
     * Socialpublish is supposed to publish only new content to the social media
     * platforms. This means we have to make sure we only distribute the blog
     * post if it is new, and not when it is edited!
     *
     * == How to make sure a post is brand new? ==
     * Well, there seems to be a trick; if a post is published for the first time
     * the post_modified field is equal to the post_date field.
     */
    public function on_publish_post($id) {
        $post = get_post($id);

        if($post->post_modified === $post->post_date) {
            // trigger the socialpublish_publish_post event, notifying all event
            // handlers a post will be published to Socialpublish.
            do_action('socialpublish_publish_post', $id);
        }
    }

    public function on_publish_future_post($id) {
        do_action('socialpublish_publish_post', $id);
    }

    /*
     * This callback is triggered when the socialpublish_publish_post is fired.
     * The plugin uses this callback to actually publish the blog post to the
     * selected social media platforms.
     */
    public function on_socialpublish_publish_post($postId) {
        $this->service->publish($postId);
    }

    public function on_socialpublish_cleanup() {
        // Why? WHY? WHYYYYYY? :'(
        // Okey, the admin decided this plugin sucks and wants to deactivate it,
        // so ask the service layer to remove the account; clean up the mess...
        $this->service->deleteAccount();
    }

    /*
     * Create an option in the admin menu
     */
    public function on_admin_menu() {
    	if (function_exists('add_submenu_page')) { // unknown when introduced?
    	    add_submenu_page(
    	    	'options-general.php',
    	        __('SocialPublish Configuration', 'socialpublish'),
    	        __('SocialPublish', 'socialpublish'),
    	        'manage_options',
    	        'socialpublish-key-config',
    	        array($this, 'on_add_submenu_page')
    	    );
    	}
    }

    /*
     * This callback is triggered when the user requests the Socialpublish
     * settings page. The callback fires a socialpublish_render_settings
     * event. The on_initialize defines a default callback for this event.
     */
    public function on_add_submenu_page() {
        do_action('socialpublish_render_settings');
    }

    /*
     * This callback is triggered when the socialpublish_render_settings
     * event is fired.
     */
    public function on_socialpublish_render_settings() {
        // MVC container *barf*

        $ms = array();

        $template = new SocialpublishTemplate(__SOCIALPUBLISH_ROOT__ . '/template/SocialpublishSettingsTemplate.php');

        if (isset($_POST['submit'])) {

            // Check intention to change the access_token.
            // @see: http://markjaquith.wordpress.com/2006/06/02/wordpress-203-nonces/
            if (function_exists('check_admin_referer')) {
                check_admin_referer('socialpublish_update_access_token');
            }

            if ($_POST['action'] === 'update_access_token') { // Looks like a controller, doesn't it :D
                $accessToken = $_POST['access_token'];

                try {
                    $account = $this->service->validate($accessToken);

                    // Gosh, if the account does not have Hubs, the user needs to update his Socialpublish account
                    // and connect at least one social media platform
                    $hasHubs = sizeof($account->getHubs()) > 0;

                    if ($hasHubs === true) {
                        $message = __('Hooraa, your <code>access_token</code> is valid, happy blogging! Don\'t forget to enable the SocialPublish screen option when you create a new Post (upper right corner of the page where you edit/add a Post).');
                    } else {
                        $message = __('Although your <code>access_token</code> is valid, you have no social media accounts connected to your SocialPublish account yet. Please read the instructions below carefully!');
                    }

                    $template->setAttribute('success_message', $message);
                } catch (SocialpublishInvalidAccessTokenException $exception) {
                    $template->setAttribute('error_message', __('There seems to be a problem with your <code>access_token</code>: "' . $exception->getMessage() . '"'));
                } catch (SocialpublishHTTPException $exception) {
                    $template->setAttribute('error_message', __('There seems to be a problem connecting to the SocialPublish server. Please try again later.'));
                }
            }
        }

        $template->setAttribute('account', $this->service->hasAccount() ? $this->service->getAccount() : null);

        echo $template->render();
    }

    /*
     * This callback is triggered when the add_meta_boxes Wordpress
     * event is fired.
     *
     * see: http://wp.smashingmagazine.com/2011/10/04/create-custom-post-meta-boxes-wordpress/
     */
    public function on_add_meta_boxes() {
        do_action('socialpublish_add_meta_box');
    }

    public function on_socialpublish_add_meta_box() {
        add_meta_box(
            'socialpublish_post_fields',
            __( 'SocialPublish', 'socialpublish'),
            array($this, 'on_socialpublish_add_meta_box_renderer'),
            'post'
        );
    }

    public function on_socialpublish_add_meta_box_renderer($post) {
        $template = new SocialpublishTemplate(__SOCIALPUBLISH_ROOT__ . '/template/SocialpublishMetaBoxTemplate.php');

        $template->setAttribute('account', $this->service->hasAccount() ? $this->service->getAccount() : null);

        // Note, the Service returns a SocialpublishPost object, not a Wordpress post object.
        // It is a Domain Object containing the Socialpublish settings of the post
        $template->setAttribute('post', $this->service->getPostById($post->ID));

        echo $template->render();
    }

    public function on_save_post($postId) {

        do_action('socialpublish_save_post', $postId);
    }

    public function on_socialpublish_save_post($postId) {
        if ($this->service->hasAccount()) {

            // A check to see if the Socialpublish screen option is active
            if (isset($_POST['socialpublish_message'])) {
                $account = $this->service->getAccount();

                $post = $this->service->getPostById($postId);

                $post->setMessage($_POST['socialpublish_message'] !== '' ? $_POST['socialpublish_message'] : '');
                $post->setHashTags($_POST['socialpublish_hashtags'] !== '' ? $_POST['socialpublish_hashtags'] : '');

                $hubs = array();
                // only add the submitted hubs that are actually connected with the Socialpublish account...
                if (isset($_POST['socialpublish_hubs'])) {
                    foreach ($_POST['socialpublish_hubs'] as $key) {
                        foreach ($account->getHubs() as $hub) {
                            if ($key === $hub->getKey()) {
                                $hubs[] = $hub;
                                break;
                            }
                        }
                    }

                }
                $post->setHubs($hubs);

                // Ask the Service layer to save the post. The Service layer is just delegating it
                // to the PostRepository.
                $this->service->savePost($post);
            }
        }
    }
}