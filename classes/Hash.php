<?php
class Hash {
	/**
	 * Create a secure password hash using bcrypt
	 * @param string $password The plain text password
	 * @return string The hashed password
	 */
	public static function make($password) {
		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Verify a password against a hash
	 * @param string $password The plain text password to verify
	 * @param string $hash The stored password hash
	 * @return bool True if password matches, false otherwise
	 */
	public static function verify($password, $hash) {
		return password_verify($password, $hash);
	}

	/**
	 * Check if a password hash needs to be rehashed
	 * (useful for upgrading old passwords on login)
	 * @param string $hash The password hash to check
	 * @return bool True if needs rehash, false otherwise
	 */
	public static function needsRehash($hash) {
		return password_needs_rehash($hash, PASSWORD_DEFAULT);
	}

	/**
	 * Generate a random string for filenames or tokens
	 * @param int $length Not used, kept for compatibility
	 * @return string A unique random string
	 */
	public static function salt($length = 32) {
		return bin2hex(random_bytes(16));
	}

	/**
	 * Generate a unique hash for tokens/sessions
	 * @return string A unique hash string
	 */
	public static function unique() {
		return bin2hex(random_bytes(32));
	}

	/**
	 * Legacy method for verifying old SHA-256 passwords
	 * Used during migration period to verify old passwords
	 * @param string $password Plain text password
	 * @param string $hash Stored hash
	 * @param string $salt Stored salt
	 * @return bool True if password matches
	 * @deprecated Use verify() for new passwords
	 */
	public static function verifyLegacy($password, $hash, $salt) {
		return hash('sha256', $password . $salt) === $hash;
	}
}
