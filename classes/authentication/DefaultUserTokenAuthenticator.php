<?php

namespace SRAG\PegasusHelper\authentication;

use ilAuthSession;
use SRAG\PegasusHelper\entity\UserToken;

/**
 * Class UserTokenAuthenticator
 *
 * The user token authenticator is responsible of validating and destroying
 * the auth token as well as authenticate a user by auth-token.
 *
 * The auth-tokens are generated by the "/v2/ilias-app/auth-token" rest route.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
final class DefaultUserTokenAuthenticator implements UserTokenAuthenticator {

	/**
	 * Authenticates the user with the given token.
	 * The token will always be deleted, regardless of the authentication result.
	 *
	 * @param int       $userId The id of the user which should be logged in.
	 * @param string    $token  The token which should be used to authenticate the user with the given id.
	 *
	 * @return void
	 */
	public function authenticate($userId, $token) {

		global $DIC;
		/**
		 * @var $ilAuthSession ilAuthSession
		 */
		$ilAuthSession = $DIC['ilAuthSession'];
		$user = $DIC->user();


		if ($this->isTokenValid($userId, $token)) {

			// log in user
			$ilAuthSession->regenerateId();
			$ilAuthSession->setUserId($userId);
			$ilAuthSession->setAuthenticated(true, $userId);

			$user->setId($userId);
			$user->read();
		}

		$this->deleteToken($userId, $token);
	}


	/**
	 * @param int       $userId The user id which belongs to the given token.
	 * @param string    $token  The auth token which should be validated.
	 *
	 * @return bool     True if the token is valid, otherwise false.
	 */
	private function isTokenValid($userId, $token) {

		/**
		 * @var $persistentToken UserToken
		 */
		$persistentToken = UserToken::find($userId);

		if ($persistentToken == NULL) {
			return false;
		}

		if ($persistentToken->getToken() !== $token) {
			return false;
		}

		$now = time();
		$expires = strtotime($persistentToken->getExpires());

		return $now < $expires;
	}


	/**
	 * Deletes the token if it exists.
	 *
	 * @param int $userId   The user id which should be used to destroy the token.
	 * @return void
	 */
	private function deleteToken($userId, $token) {
		$token = UserToken::where(['userId' => $userId, 'token' => $token])->first();
		if ($token !== NULL) {
			$token->delete();
		}
	}

}