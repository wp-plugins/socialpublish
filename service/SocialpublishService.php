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
        if (static::$instance === null) {
            static::$instance = new SocialpublishService();
        }

        return static::$instance;
    }

    // can be used to Stub the base API URI, needed in development mode
    // when Socialpublish is listening on port 8080.
    public static function setBaseUri($uri) {
        static::$URI = $uri;
    }

    /*
     * @param ISocialpublishAccountRepository $accountRepository The AccountRepository instance used
     *        by the service
     */
    public static function setAccountRepository(ISocialpublishAccountRepository $accountRepository) {
        static::$accountRepository = $accountRepository;
    }

    /*
     * @param ISocialpublishPostRepository $postRepository The PostRepository instance used
     *        by the service
     */
    public static function setPostRepository(ISocialpublishPostRepository $postRepository) {
        static::$postRepository = $postRepository;
    }

    public function validate($accessToken) {

        if (!preg_match('/^[A-Za-z0-9]+$/', $accessToken)) {
            throw new SocialpublishInvalidAccessTokenException();
        }

        $result = $this->api('/hubs', 'GET', $accessToken);

        if ($result['success'] !== true) {
            throw new SocialpublishInvalidAccessTokenException();
        }

        $account = static::$accountRepository->getAccount($accessToken);

        $hubs = array();
        foreach ($result['hubs'] as $row) {
            $hubs[] = new SocialpublishAccountHub($row['type'], $row['name']);
        }

        $account->setHubs($hubs);

        static::$accountRepository->save($account);

        return $account;
    }

    public function publish($postId) {
        if (static::$accountRepository->hasAccount()) {

            $post = static::$postRepository->getPostById($postId);

            // Don't distribute this post to Socialpublish if it has been published already...
            if ($post->isPublished()) {
                return null;
            }

            $post->publish();
            // Store the post is published...
            static::$postRepository->save($post);

            $hubs = array();

            foreach ($post->getHubs() as $hub) {
                $hubs[] = $hub->getKey();
            }

            $ret = $this->api(
            	'/publish',
            	'POST',
                static::$accountRepository->getAccount()->getAccessToken(),
                $a = array(
            		'title' => $post->getTitle(),
                    'message' => $post->getMessage(),
            		'link' => $post->getLink(),
                    'link_description' => $post->getLinkDescription(),
                    'hashtags' => $post->getHashTags(),
                    'hubs' => $hubs
                )
            );

            return $ret;
        } else {
            return null;
        }
    }

    public function getPostById($postId) {
        return static::$postRepository->getPostById($postId);
    }

    public function hasAccount() {
        return static::$accountRepository->hasAccount();
    }

    public function getAccount() {
        return static::$accountRepository->getAccount();
    }

    public function deleteAccount() {
        static::$accountRepository->deleteAccount();
    }

    public function savePost(SocialpublishPost $post) {
        static::$postRepository->save($post);
    }

    protected function api($query, $method, $accessToken, $body = null) {
        $cparams = array(
            'http' => array(
                'method' => $method,
                'ignore_errors' => true
            )
        );

        $params = array(
            "access_token" => $accessToken
        );

        if ($body !== null) {
            $params['body'] = json_encode($body);
        }

        $params = http_build_query($params);

        if ($method === 'POST') {
            $cparams['http']['content'] = $params;
        } else {
            $query .= '?' . $params;
        }

        $context = stream_context_create($cparams);
        $fp = @fopen(static::$URI . $query, 'rb', false, $context);
        if (!$fp) {
            $res = false;
        } else {
            $res = stream_get_contents($fp);
        }

        if ($res === false) {
            return false;
        }

        return json_decode($res, true);
    }
}

?>