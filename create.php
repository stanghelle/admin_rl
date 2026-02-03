<?php
/**
 * Create User API Endpoint (Admin only)
 *
 * POST /api/admin/users/create.php
 * Body: { name, username, email?, phone?, role, password }
 */

require_once __DIR__ . '/../app/bingo/config/config.php';


$data = getPostData();

// Validate input
$validator = validate($data)
    ->required('name', 'Navn')
    ->minLength('name', 2, 'Navn')
    ->maxLength('name', 100, 'Navn')
    ->required('username', 'Brukernavn')
    ->minLength('username', 3, 'Brukernavn')
    ->maxLength('username', 50, 'Brukernavn')
    ->unique('username', 'us_users', 'username', null, 'Brukernavn')
    ->required('role', 'Rolle')
    ->required('password', 'Passord')
    ->password('password', 'Passord');

if (isset($data['email']) && !empty($data['email'])) {
    $validator->email('email', 'E-post')
              ->unique('email', 'us_users', 'email', null, 'E-post');
}

if ($validator->fails()) {
    errorResponse($validator->firstError());
}

// Validate role
if (!in_array($data['role'], ['admin', 'user'])) {
    errorResponse('Ugyldig rolle');
}

try {
    $db = db();

    $userId = $db->insert('us_users', [
        'name' => sanitize($data['name']),
        'username' => sanitize($data['username']),
        'email' => !empty($data['email']) ? sanitize($data['email']) : null,
        'phone' => !empty($data['phone']) ? sanitize($data['phone']) : null,
        'role' => $data['role'],
        'password' => hashPassword($data['password']),
        'password_changed' => 0
    ]);

    successResponse([
        'user_id' => $userId
    ], 'Bruker opprettet');

} catch (Exception $e) {
    logError('Create user failed: ' . $e->getMessage());
    errorResponse('Kunne ikke opprette brukeren', 500);
}
