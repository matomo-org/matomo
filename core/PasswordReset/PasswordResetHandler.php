<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\PasswordReset;

class PasswordResetHandler {

const TOKEN_HASH_ALGO='sha3-512';

	public static function hash($str) {
		return hash(self::TOKEN_HASH_ALGO,$str,false);
	}
}
