<?php
require_once "config.php";
require_once "helpers.php";

function add_order($data)
{
    // Validate required fields
    if (
        !isset($data['user_id']) ||
        !isset($data['car_type']) ||
        !isset($data['service_type']) ||
        !isset($data['hours']) ||
        !isset($data['price']) ||
        !isset($data['lat']) ||
        !isset($data['lon'])
    ) {
        sendResponse(400, ["error" => "All fields are required."]);
        return;
    }

    // Extract data
    $user_id = intval($data['user_id']);
    $car_type = trim($data['car_type']);
    $service_type = trim($data['service_type']);
    $hours = intval($data['hours']);
    $price = floatval($data['price']);
    $lat = floatval($data['lat']);
    $lon = floatval($data['lon']);
   


    try {
        // Connect to the database
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the order into the database
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, car_type, service_type, hours, price, lat, lon)
            VALUES (:user_id, :car_type, :service_type, :hours, :price, :lat, :lon)
        ");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":car_type", $car_type);
        $stmt->bindParam(":service_type", $service_type);
        $stmt->bindParam(":hours", $hours);
        $stmt->bindParam(":price", $price);
        $stmt->bindParam(":lat", $lat);
        $stmt->bindParam(":lon", $lon);

        if ($stmt->execute()) {
            $car_id = get_car_id($car_type);
            $driver_id = get_driver_id($car_id);
            $order_id = $pdo->lastInsertId();

            add_notif($driver_id,$order_id);

            sendResponse(201, ["message" => "Order created successfully."]);
        } else {
            sendResponse(500, ["error" => "Failed to create order."]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
    }
}

function get_user_orders($user_id)
{
    if (!isset($user_id) || empty($user_id)) {
        sendResponse(400, ["error" => "User ID is required."]);
        return;
    }

    try {
        // Connect to the database
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch orders for the given user ID
        $stmt = $pdo->prepare("
            SELECT *
            FROM orders 
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($orders) {
            sendResponse(200, ["orders" => $orders]);
        } else {
            sendResponse(200, ["orders" => $orders]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
    }
}
function get_driver_orders($user_id)
{
    if (!isset($user_id) || empty($user_id)) {
        sendResponse(400, ["error" => "User ID is required."]);
        return;
    }

    try {
        // Connect to the database
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch orders for the given user ID
        $stmt = $pdo->prepare("
            SELECT * FROM orders  INNER JOIN users ON orders.user_id = users.id_user WHERE driver = :user_id ORDER BY orders.created_at DESC
        ");
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($orders) {
            sendResponse(200, ["orders" => $orders]);
        } else {
            sendResponse(200, ["orders" => $orders]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Database error: " . $e->getMessage()]);
    }
}

function get_driver_id($car_id)
{
    try {
        // Connexion à la base de données
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête
        $stmt = $pdo->prepare("
            SELECT id_user FROM `users` WHERE driver_car = :car_id LIMIT 1
        ");

        $stmt->bindParam(":car_id", $car_id, PDO::PARAM_INT);
        $stmt->execute();

        // Récupération du résultat
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification si un chauffeur a été trouvé
        if ($driver) {
            return $driver['id_user'];
        } else {
            return null; // Aucun chauffeur trouvé
        }

    } catch (PDOException $e) {
        // Afficher ou enregistrer l'erreur
        error_log("Erreur PDO: " . $e->getMessage());
        return null; // Retourner null en cas d'erreur
    }
}


function get_car_id($model)
{
    try {
        // Connexion à la base de données
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête
        $stmt = $pdo->prepare("
            SELECT car_id FROM `car` WHERE model = :model LIMIT 1
        ");

        $stmt->bindParam(":model", $model, PDO::PARAM_STR);
        $stmt->execute();

        // Récupération du résultat
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification si un chauffeur a été trouvé
        if ($driver) {
            return $driver['car_id'];
        } else {
            return null; // Aucun chauffeur trouvé
        }

    } catch (PDOException $e) {
        // Afficher ou enregistrer l'erreur
        error_log("Erreur PDO: " . $e->getMessage());
        return null; // Retourner null en cas d'erreur
    }
}



function add_notif($driver_id, $order_id)
{
    try {
        // Connexion à la base de données
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparation de la requête SQL
        $stmt = $pdo->prepare("
            INSERT INTO `notification` (`order_id`, `driver`)
            VALUES (:order_id, :driver_id)
        ");

        // Liaison des paramètres
        $stmt->bindParam(":order_id", $order_id, PDO::PARAM_INT); // Vous devrez définir une logique pour l'ID de commande
        $stmt->bindParam(":driver_id", $driver_id, PDO::PARAM_INT);

        // Exécuter la requête
        if ($stmt->execute()) {
            sendResponse(201, ["message" => "Notification ajoutée avec succès."]);
        } else {
            sendResponse(500, ["error" => "Échec de l'ajout de la notification."]);
        }
    } catch (PDOException $e) {
        sendResponse(500, ["error" => "Erreur de base de données : " . $e->getMessage()]);
    }
}

