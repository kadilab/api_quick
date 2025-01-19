<?php
require_once "config.php";
require_once 'helpers.php';

function update_location($data)
{
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!isset($data['user_id'], $data['latitude'], $data['longitude'])) {
            sendResponse(400, ["message" => "Missing required fields: user_id, latitude, longitude."]);
            return;
        }

        $user_id = $data['user_id'];
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];

        // Vérifier si une localisation existe déjà pour cet utilisateur
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id_user = :user_id");
        $checkStmt->bindParam(':user_id', $user_id);
        $checkStmt->execute();

        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            // Mettre à jour la localisation existante
            $updateStmt = $pdo->prepare("UPDATE users SET latitude = :latitude, longitude = :longitude, updated_at = NOW() WHERE id_user = :user_id");
            $updateStmt->bindParam(':latitude', $latitude);
            $updateStmt->bindParam(':longitude', $longitude);
            $updateStmt->bindParam(':user_id', $user_id);

            if ($updateStmt->execute()) {
                sendResponse(200, ["message" => "Location updated successfully."]);
            } else {
                sendResponse(500, ["message" => "Failed to update location."]);
            }
        } else {
            // Insérer une nouvelle localisation
            $insertStmt = $pdo->prepare("INSERT INTO users (id_user, latitude, longitude, created_at, updated_at) VALUES (:user_id, :latitude, :longitude, NOW(), NOW())");
            $insertStmt->bindParam(':user_id', $user_id);
            $insertStmt->bindParam(':latitude', $latitude);
            $insertStmt->bindParam(':longitude', $longitude);

            if ($insertStmt->execute()) {
                sendResponse(201, ["message" => "Location created successfully."]);
            } else {
                sendResponse(500, ["message" => "Failed to create location."]);
            }
        }
    } catch (PDOException $e) {
        sendResponse(500, ["message" => "Database error: " . $e->getMessage()]);
    }
}


function update_status($data)
{
    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (!isset($data['user_id'], $data['status'])) {
            sendResponse(400, ["message" => "Missing required fields: user_id, status."]);
            return;
        }

        $user_id = $data['user_id'];
        $status = $data['status'];

        $stmt = $pdo->prepare("UPDATE users SET driver_active_status = :status WHERE id_user = :user_id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            sendResponse(200, ["message" => "Status updated successfully."]);
        } else {
            sendResponse(500, ["message" => "Failed to update status."]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["message" => "Database error: " . $e->getMessage()]);
    }
}
