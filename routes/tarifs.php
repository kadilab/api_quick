<?php
require_once "config.php";
require_once 'helpers.php';

function get_tarifs()
{

    try {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM `tariffs` INNER JOIN car ON tariffs.car_type = car.car_id");
        $stmt->execute();

        $tarifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($tarifs) {
            sendResponse(200, [
                "message" => "Login successful.",
                "tarifs" => $tarifs
            ]);
        } else {
            sendResponse(401, ["message" => "Invalid email or password."]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["message" => "Database error: " . $e->getMessage()]);
    }

}