<?php

require_once __SOCIALPUBLISH_ROOT__ . '/repository/ISocialpublishAccountRepository.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/SocialpublishAccount.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/SocialpublishAccountDoesNotExistException.php';

class SocialpublishAccountWordpressRepository implements ISocialpublishAccountRepository
{
    protected static $instance;

    protected function __construct() {}

    public function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SocialpublishAccountWordpressRepository();
        }

        return self::$instance;
    }

    public function hasAccount() {
        return get_option('socialpublish_access_token') !== false;
    }

    public function getAccount($accessToken = null) {
        if ($accessToken === null) {
            if ($this->hasAccount()) {
                $account = new SocialpublishAccount(
                    get_option('socialpublish_access_token')
                );

                $o = json_decode(get_option('socialpublish_hubs'), true);
                $hubs = array();
                foreach ($o as $row) {
                    $hubs[] = new SocialpublishAccountHub($row['type'], $row['id'], $row['name']);
                }

                $account->setHubs($hubs);
            } else {
                throw new SocialpublishAccountDoesNotExistException();
            }
        } else {
            $account = new SocialpublishAccount($accessToken);
        }

        return $account;
    }

    public function deleteAccount() {
        delete_option('socialpublish_access_token');
    }

    public function save(SocialpublishAccount $account) {
        update_option('socialpublish_access_token', $account->getAccessToken());

        // make the Domain object serializable as JSON
        $hubs = array();
        foreach ($account->getHubs() as $hub) {
            $hubs[] = array('type' => $hub->getType(), 'id' => $hub->getId() . "", 'name' => $hub->getName());
        }

        update_option('socialpublish_hubs', json_encode($hubs));
    }
}

?>