<?php

require_once __SOCIALPUBLISH_ROOT__ . '/repository/ISocialpublishPostRepository.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/SocialpublishPost.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/SocialpublishPostDoesNotExistException.php';

class SocialpublishPostWordpressRepository implements ISocialpublishPostRepository
{
    protected static $instance;
    protected static $accountRepository;

    protected function __construct() {}

    public function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SocialpublishPostWordpressRepository();
        }

        return self::$instance;
    }

    /*
     * @param ISocialpublishAccountRepository $accountRepository The Repository instance used
     *        by the service
     */
    public static function setAccountRepository(ISocialpublishAccountRepository $accountRepository) {
        self::$accountRepository = $accountRepository;
    }

    protected function isCompatiblePost($json) {
        return $json !== null && isset($json['published']) && isset($json['hashtags']) && isset($json['hubs']) && is_array($json['hubs']);
    }

    protected function isCompatibleHub($json) {
        return isset($json['type']) && isset($json['id']) && isset($json['name']);
    }


    public function getPostById($postId) {
        $post = get_post($postId);
        $ret = new SocialpublishPost($postId, $post->post_title, get_permalink($postId));
        $ret->setLinkDescription(substr(strip_tags($post->post_content), 0, 160));
        $ret->setHubs(self::$accountRepository->getAccount()->getHubs());

        $meta = get_post_meta($postId, 'socialpublish', true);
        if ($meta !== false && $meta !== '') {
            $json = json_decode($meta, true);

            if ($this->isCompatiblePost($json)) {
                $ret->setIsPublished($json['published']);

                $ret->setMessage($json['message']);
                $ret->setHashTags($json['hashtags']);

                $hubs = array();
                foreach ($json['hubs'] as $hub) {
                    if ($this->isCompatibleHub($hub)) {
                        $hubs[] = new SocialpublishAccountHub($hub['type'], $hub['id'], $hub['name']);
                    }
                }

                $ret->setHubs($hubs);
            } else {
                // incompatible contents...
                delete_post_meta($postId, 'socialpublish');
            }
        }

        return $ret;
    }

    public function save(SocialpublishPost $post) {
        $hubs = array();
        foreach ($post->getHubs() as $hub) {
            $hubs[] = array('type' => $hub->getType(), 'id' => $hub->getId(), 'name' => $hub->getName());
        }

        $o = array(
            'title' => $post->getTitle(),
            'link' => $post->getLink(),
            'link_description' => $post->getLinkDescription(),
            'message' => $post->getMessage(),
            'hashtags' => $post->getHashTags(),
            'published' => $post->isPublished(),
            'hubs' => $hubs
        );

        update_post_meta($post->getId(), 'socialpublish', json_encode($o));
    }
}

?>