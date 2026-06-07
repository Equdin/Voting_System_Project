<?php
/*
 * Project: Organisation Voting System
 * PHP: 8.2.12 | Server: XAMPP Linux
 * File: includes/auth.php
 * Purpose: Session-based authentication and role management
 *
 * Requirements (Copilot must follow ALL of these):
 *
 * 1. startSecureSession()
 *    - Start session only if not already started
 *    - Set session cookie params: httponly=true, samesite=Strict
 *    - session_regenerate_id(true) on every fresh login to prevent fixation
 *
 * 2. loginUser(PDO $pdo, string $email, string $password): bool
 *    - Fetch user by email using prepared statement
 *    - Check status === 'approved' before allowing login
 *    - Use password_verify() to check password against hash
 *    - On success: store id, full_name, email, role in $_SESSION
 *    - On failure: return false — never reveal why (no "wrong password" vs "not found")
 *
 * 3. isLoggedIn(): bool
 *    - Return true only if $_SESSION['user_id'] is set
 *
 * 4. isAdmin(): bool
 *    - Return true only if logged in AND $_SESSION['role'] === 'admin'
 *
 * 5. requireLogin(string $redirectTo = '/Voting_System_Project/voter/login.php'): void
 *    - If not logged in, redirect and exit
 *
 * 6. requireAdmin(string $redirectTo = '/Voting_System_Project/admin/login.php'): void  
 *    - If not admin, redirect and exit
 *
 * 7. logoutUser(): void
 *    - Unset all session variables
 *    - Destroy session
 *    - Delete session cookie
 *
 * 8. getSessionUser(): array
 *    - Return array of current session user data (id, full_name, email, role)
 *    - Return empty array if not logged in
 *
 * Security rules:
 *    - No raw SQL — all queries use PDO prepared statements
 *    - Never expose specific auth failure reasons to the caller
 *    - Always call startSecureSession() at top of this file
 */

<?php
declare(strict_types=1);

// Ensure secure session is started for any auth operations
startSecureSession();

function startSecureSession(): void
{
	if (session_status() === PHP_SESSION_NONE) {
		$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

		$cookieParams = session_get_cookie_params();

		session_set_cookie_params([
			'lifetime' => $cookieParams['lifetime'] ?? 0,
			'path' => $cookieParams['path'] ?? '/',
			'domain' => $cookieParams['domain'] ?? '',
			'secure' => $secure,
			'httponly' => true,
			'samesite' => 'Strict',
		]);

		session_start();
	}
}

function loginUser(PDO $pdo, string $email, string $password): bool
{
	try {
		$stmt = $pdo->prepare('SELECT id, full_name, email, password, role, status FROM users WHERE email = :email LIMIT 1');
		$stmt->execute([':email' => $email]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			return false;
		}

		if (!isset($user['status']) || $user['status'] !== 'approved') {
			return false;
		}

		if (!isset($user['password']) || !password_verify($password, $user['password'])) {
			return false;
		}

		session_regenerate_id(true);

		$_SESSION['user_id'] = (int)$user['id'];
		$_SESSION['full_name'] = $user['full_name'] ?? '';
		$_SESSION['email'] = $user['email'] ?? '';
		$_SESSION['role'] = $user['role'] ?? 'user';

		return true;
	} catch (PDOException $e) {
		// Do not reveal DB errors to caller
		return false;
	}
}

function isLoggedIn(): bool
{
	return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
	return isLoggedIn() && (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function requireLogin(string $redirectTo = '/Voting_System_Project/voter/login.php'): void
{
	if (!isLoggedIn()) {
		header('Location: ' . $redirectTo);
		exit();
	}
}

function requireAdmin(string $redirectTo = '/Voting_System_Project/admin/login.php'): void
{
	if (!isAdmin()) {
		header('Location: ' . $redirectTo);
		exit();
	}
}

function logoutUser(): void
{
	if (session_status() === PHP_SESSION_NONE) {
		startSecureSession();
	}

	// Unset session variables
	$_SESSION = [];

	// Delete session cookie
	$params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		[
			'expires' => time() - 42000,
			'path' => $params['path'] ?? '/',
			'domain' => $params['domain'] ?? '',
			'secure' => $params['secure'] ?? false,
			'httponly' => true,
			'samesite' => 'Strict',
		]
	);

	// Destroy session
	session_destroy();
}

function getSessionUser(): array
{
	if (!isLoggedIn()) {
		return [];
	}

	return [
		'id' => (int)($_SESSION['user_id'] ?? 0),
		'full_name' => (string)($_SESSION['full_name'] ?? ''),
		'email' => (string)($_SESSION['email'] ?? ''),
		'role' => (string)($_SESSION['role'] ?? ''),
	];
}

