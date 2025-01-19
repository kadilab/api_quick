<?php
require_once "config.php";
require_once "helpers.php";

/**
 * ✅ Récupérer les notifications d'un utilisateur
 * 
 * @param int $user_id
 * @return void
 */
function get_notif($user_id)
{
    // 🛡️ Valider le paramètre
    if (!isset($user_id) || !is_numeric($user_id)) {
        sendResponse(400, ["error" => "Invalid or missing user_id."]);
        return;
    }

    try {
        // ✅ Connexion à la base de données
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ✅ Requête SQL pour récupérer les notifications
        $stmt = $pdo->prepare("
            SELECT *
            FROM notification 
            INNER JOIN orders ON notification.order_id = orders.id
            WHERE notification.driver = :user_id 
            AND notification.etat = 0
            ORDER BY notification.created_at DESC
        ");

        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // ✅ Récupérer les résultats
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($notifications) {
            sendResponse(200, ["notifications" => $notifications]);
        } else {
            sendResponse(200, ["notifications" => $notifications]);        }
    } catch (PDOException $e) {
        // 🚨 Gestion des erreurs PDO
        sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
    }
}


function accept_ride($data)

{
    // 🛡️ Valider les paramètres
    if (!isset($data['id_notif']) || !is_numeric($data['id_notif'])) {
        sendResponse(400, ["error" => "Invalid or missing id_notif."]);
        return;
    }
    if (!isset($data['id_order']) || !is_numeric($data['id_order'])) {
        sendResponse(400, ["error" => "Invalid or missing id_order."]);
        return;
    }
    if (!isset($data['id_user']) || !is_numeric($data['id_user'])) {
        sendResponse(400, ["error" => "Invalid or missing id_driver."]);
        return;
    }

    $id_driver = $data['id_user'];
    $id_notif = $data['id_notif'];
    $id_order = $data['id_order'];
    

    try {
        // ✅ Connexion à la base de données
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // ✅ Commencer une transaction
        $pdo->beginTransaction();

        // 📝 1. Mettre à jour le statut de la notification
        $stmtNotif = $pdo->prepare("
            UPDATE notification 
            SET etat = 1 
            WHERE id_notif = :id_notif
        ");
        $stmtNotif->bindParam(":id_notif", $id_notif, PDO::PARAM_INT);
        $stmtNotif->execute();

        // 📝 2. Mettre à jour le statut de la commande
        $stmtOrder = $pdo->prepare("
            UPDATE orders 
            SET status = 'confirmed', driver = :id_driver 
            WHERE id = :id_order
        ");
        $stmtOrder->bindParam(":id_order", $id_order, PDO::PARAM_INT);
        $stmtOrder->bindParam(":id_driver", $id_driver, PDO::PARAM_INT);
        $stmtOrder->execute();

        // ✅ Valider la transaction
        $pdo->commit();

        // ✅ Réponse en cas de succès
        sendResponse(200, ["message" => "Ride accepted successfully."]);
    } catch (PDOException $e) {
        // 🚨 Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
    }
}
