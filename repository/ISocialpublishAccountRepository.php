<?php

interface ISocialpublishAccountRepository
{
    /*
     *
     * @return boolean
     */
    public function hasAccount();

    /*
     * @return SocialpublishAccount
     */
    public function getAccount($accessToken = null);

    /*
     * @return void
     */
    public function deleteAccount();

    /*
     * @return void
     */
    public function save(SocialpublishAccount $account);
}

?>