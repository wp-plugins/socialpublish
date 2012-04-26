<?php

require_once __SOCIALPUBLISH_ROOT__ . '/domain/SocialpublishInvalidAccessTokenException.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/SocialpublishAccountHub.php';

// This service layer mediates between the MVC layer and the Data Store layer (Repositories)

class SocialpublishService
{
    protected $accessToken;

    // the default socialpublish API URI
    protected static $URI = 'http://socialpublish.io/api/0.1';

    protected static $instance;
    protected static $accountRepository;
    protected static $postRepository;

    protected function __construct() {}

    public function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SocialpublishService();
        }

        return self::$instance;
    }

    // can be used to Stub the base API URI, needed in development mode
    // when Socialpublish is listening on port 8080.
    public static function setBaseUri($uri) {
        self::$URI = $uri;
    }

    /*
     * @param ISocialpublishAccountRepository $accountRepository The AccountRepository instance used
     *        by the service
     */
    public static function setAccountRepository(ISocialpublishAccountRepository $accountRepository) {
        self::$accountRepository = $accountRepository;
    }

    /*
     * @param ISocialpublishPostRepository $postRepository The PostRepository instance used
     *        by the service
     */
    public static function setPostRepository(ISocialpublishPostRepository $postRepository) {
        self::$postRepository = $postRepository;
    }

    public function validate($accessToken) {

        if (!preg_match('/^[A-Za-z0-9]+$/', trim($accessToken))) {
            throw new SocialpublishInvalidAccessTokenException();
        }

        $result = SocialpublishHTTP::getInstance()->get(self::$URI . '/hubs', array('access_token' => $accessToken));
        $result = json_decode($result, true);

        if ($result['success'] !== true) {
            throw new SocialpublishInvalidAccessTokenException($result['message']);
        }

        $account = self::$accountRepository->getAccount($accessToken);

        $hubs = array();
        foreach ($result['hubs'] as $row) {
            $hubs[] = new SocialpublishAccountHub($row['type'], $row['id'], $row['name']);
        }

        $account->setHubs($hubs);

        self::$accountRepository->save($account);

        return $account;
    }

    public function publish($postId) {
        if (self::$accountRepository->hasAccount()) {

            $post = self::$postRepository->getPostById($postId);

            // Don't distribute this post to Socialpublish if it has been published already...
            if ($post->isPublished()) {
                return null;
            }

            $post->publish();
            // Store the post is published...
            self::$postRepository->save($post);

            $hubs = array();

            foreach ($post->getHubs() as $hub) {
                $hubs[] = $hub->getKey();
            }

            $ret = SocialpublishHTTP::getInstance()->post(
                self::$URI . '/publish',
                array(
                    'access_token' => self::$accountRepository->getAccount()->getAccessToken(),
                    'body' => json_encode(
                        array(
            				'title' => $post->getTitle(),
                    		'message' => $post->getMessage(),
            				'link' => $post->getLink(),
                    		'link_description' => $post->getLinkDescription(),
                    		'hashtags' => $post->getHashTags(),
                    		'hubs' => $hubs
                        )
                    )
                )
            );
            return json_decode($ret, true);
        } else {
            return null;
        }
    }

    public function getPostById($postId) {
        return self::$postRepository->getPostById($postId);
    }

    public function hasAccount() {
        return self::$accountRepository->hasAccount();
    }

    public function getAccount() {
        return self::$accountRepository->getAccount();
    }

    public function deleteAccount() {
        self::$accountRepository->deleteAccount();
    }

    public function savePost(SocialpublishPost $post) {
        self::$postRepository->save($post);
    }
}

?>