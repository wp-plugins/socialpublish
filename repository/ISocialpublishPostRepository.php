<?php

interface ISocialpublishPostRepository
{
    /*
     *
     * @return SocialpublishPost
     */
    public function getPostById($postId);

    public function save(SocialpublishPost $post);
}

?>