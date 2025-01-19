<?php
require_once "config.php";
require_once 'helpers.php';

function login($data) {
    if (!isset($data['email']) || !isset($data['password'])) {
        sendResponse(400, ["error" => "Email and password are required."]);
    }
    
    $email = trim($data['email']);
    $password = trim($data['password']);

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT id_user, firstname, lastname, password_hash, user_role,account_approved FROM users WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password , $user['password_hash'])) {
            sendResponse(200, [
                "message" => "successful",
                "user" => [
                    "id" => $user['id_user'],
                    "firstname" => $user['firstname'],
                    "lastname" => $user['lastname'],
                    "email" => $email,
                    "role" => $user['user_role'],
                    "account_approved" => $user['account_approved']
                ]
            ]);
        } else {
            sendResponse(200, ["message" => "Echec de connexion",
            "user" =>[]
        ]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["message" => "Database error: " . $e->getMessage()]);
    }
}

function signup($data) {
    if (!isset($data['firstname'], $data['lastname'], $data['email'], $data['password'])) {
        sendResponse(400, ["error" => "All fields are required."]);
    }

    $firstname = trim($data['firstname']);
    $lastname = trim($data['lastname']);
    $email = trim($data['email']);
    $mobile = trim($data['mobile']);
    $password = password_hash(trim($data['password']), PASSWORD_BCRYPT);

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, mobile, password_hash) VALUES (:firstname, :lastname, :email,:mobile, :password)");
        $stmt->bindParam(":firstname", $firstname);
        $stmt->bindParam(":lastname", $lastname);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":mobile", $mobile);
        $stmt->bindParam(":password", $password);

        if ($stmt->execute()) {
            sendResponse(201, ["message" => "User created successfully."]);
        } else {
            sendResponse(500, ["error" => "Failed to create user."]);
        }
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') { // Duplicate entry
            sendResponse(400, ["error" => "Email is already in use."]);
        } else {
            sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
        }
    }
}
?>
