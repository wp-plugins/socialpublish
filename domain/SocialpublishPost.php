<?php

class SocialpublishPost
{
    protected $postId;

    protected $title; // title of the page
    protected $message; // additional optional message
    protected $link;
    protected $linkDescription; // facebook

    protected $hashTags; // twitter
    protected $hubs;

    /*
     * Flag for determining if this post has been published and sent to Socialpublish
     */
    protected $published;

    public function __construct($postId, $title, $link) {
        $this->postId    = $postId;
        $this->title     = $title;
        $this->link      = $link;
        $this->published = false;
        $this->hubs      = array();
    }

    public function getId() {
        return $this->postId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getLink() {
        return $this->link;
    }

    public function setLinkDescription($linkDescription) {
        $this->linkDescription = $linkDescription;
    }

    public function getLinkDescription() {
        return $this->linkDescription;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setHashTags($hashTags) {
        $this->hashTags = $hashTags;
    }

    public function getHashTags() {
        return $this->hashTags;
    }

    public function getHubs() {
        return $this->hubs;
    }

    public function setHubs($hubs) {
        $this->hubs = $hubs;
    }

    public function publish() {
        $this->published = true;
    }

    public function isPublished() {
        return $this->published;
    }

    public function setIsPublished($published) {
        $this->published = $published;
    }
}