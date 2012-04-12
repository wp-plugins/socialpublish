<?php

/**
 * @package Socialpublish
 */

/*
Plugin Name: Socialpublish
Plugin URI: http://socialpublish.io/plugins/wordpress
Description: SocialPublish is an easy to use service that automatically shares your blog post on Facebook and Twitter at the moment you publish it!
Version: 0.0.1
Author: Socialpublish.io <Jorgen Horstink>
Author URI: http://socialpublish.io
*/

define('SOCIALPUBLISH_URI', 'http://socialpublish.io');

define('__SOCIALPUBLISH_FILE__', __FILE__);
define('__SOCIALPUBLISH_ROOT__', dirname(__FILE__));

require_once __SOCIALPUBLISH_ROOT__ . '/service/SocialpublishService.php';

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
SocialpublishService::setBaseUri(SOCIALPUBLISH_URI . '/api/0.1');

// Load the plugin \o/, some sort of closure...
SocialpublishBootstrap::loadPlugin(SocialpublishService::getInstance());

?>