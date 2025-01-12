<?php
require_once '../config.php';
require_once '../helpers.php';

/**
 * ✅ Met à jour le profil utilisateur
 */
function update_profile($data) {
    // Valider les champs requis
    if (
        !isset($data['user_id']) ||
        !isset($data['firstname']) ||
        !isset($data['lastname']) ||
        !isset($data['email']) ||
        !isset($data['mobile'])
    ) {
        sendResponse(400, ["error" => "All fields are required (user_id, firstname, lastname, email, mobile)."]);
        return;
    }

    // Extraction des données
    $user_id = intval($data['user_id']);
    $firstname = trim($data['firstname']);
    $lastname = trim($data['lastname']);
    $email = trim($data['email']);
    $mobile = trim($data['mobile']);

    try {
        // Connexion à la base de données
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérifier si l'utilisateur existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            sendResponse(404, ["error" => "User not found."]);
            return;
        }

        // Mettre à jour les informations de l'utilisateur
        $stmt = $pdo->prepare("
            UPDATE users 
            SET firstname = :firstname, lastname = :lastname, email = :email, mobile = :mobile 
            WHERE id = :user_id
        ");
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            sendResponse(200, ["message" => "Profile updated successfully."]);
        } else {
            sendResponse(500, ["error" => "Failed to update profile."]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
    }
}
