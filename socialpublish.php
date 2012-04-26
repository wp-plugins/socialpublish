<?php

/**
 * @package Socialpublish
 */

/*
Plugin Name: Socialpublish
Plugin URI: http://socialpublish.io/plugins/wordpress
Description: SocialPublish is an easy to use service that automatically shares your blog post on Facebook and Twitter at the moment you publish it!
Version: 1.1.1
Author: Socialpublish.io <Jorgen Horstink>
Author URI: http://socialpublish.io
*/

define('DEV', false);

define ('SOCIALPUBLISH_PLUGIN_VERSION', '1.1.1');
define ('SOCIALPUBLISH_API_VERSION', '0.2');

if (DEV) {
    define('SOCIALPUBLISH_URI', 'http://dev.socialpublish.io:8080');
    define('__SOCIALPUBLISH_FILE__', 'socialpublish/socialpublish.php');
} else {
    define('SOCIALPUBLISH_URI', 'http://socialpublish.io');
    define('__SOCIALPUBLISH_FILE__', __FILE__);
}

define('__SOCIALPUBLISH_ROOT__', dirname(__FILE__));

require_once __SOCIALPUBLISH_ROOT__ . '/service/SocialpublishService.php';

require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/SocialpublishHTTP.php';

require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/strategy/SocialpublishHTTPSocketStrategy.php';
require_once __SOCIALPUBLISH_ROOT__ . '/domain/http/strategy/SocialpublishHTTPFopenStrategy.php';

SocialpublishHTTP::getInstance()->addStrategy(SocialpublishHTTPSocketStrategy::getInstance());
SocialpublishHTTP::getInstance()->addStrategy(SocialpublishHTTPFopenStrategy::getInstance());

/**
 * Martin Fowler defines a Repository in his book Patterns of Enterprise
 * Application Architecture as:
 *
 * '[a Repository] Mediates between the domain and data mapping layers
 * using a collection-like interface for accessing domain objects.'
 *
 * see: http://martinfowler.com/eaaCatalog/repository.html
 *
 * The Socialpublish plugin uses the Repository pattern to separate the
 * domain model, and the data store/mapping layer. The domain model
 * does not know how the data is saved and retrieved; it just asks the
 * Repository to fetch or save a Domain Object.
 *
 * In the case of this Wordpress plugin it uses a
 * SocialPublishAccountWordpressRepository. In Joomla! it would be a
 * SocialPublishAccountJoomlaRepository, implementing the exact same
 * interface.
 *
 */

require_once __SOCIALPUBLISH_ROOT__ . '/repository/SocialpublishAccountWordpressRepository.php';
require_once __SOCIALPUBLISH_ROOT__ . '/repository/SocialpublishPostWordpressRepository.php';
require_once __SOCIALPUBLISH_ROOT__ . '/SocialpublishBootstrap.php';

// The PostRepository needs a reference to the AccountRepository... If a Post does not exist
// it uses all the Hubs attached to the Account.
SocialpublishPostWordpressRepository::setAccountRepository(SocialpublishAccountWordpressRepository::getInstance());

SocialpublishService::setAccountRepository(SocialpublishAccountWordpressRepository::getInstance());
SocialpublishService::setPostRepository(SocialpublishPostWordpressRepository::getInstance());
SocialpublishService::setBaseUri(SOCIALPUBLISH_URI . '/api/' . SOCIALPUBLISH_API_VERSION);

// Load the plugin \o/, some sort of closure...
SocialpublishBootstrap::loadPlugin(SocialpublishService::getInstance());

?>