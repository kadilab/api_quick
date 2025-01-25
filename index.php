<?php
require_once 'helpers.php';

// Route mapping
$routes = [
    'login' => './routes/auth.php',
    'signup' => './routes/auth.php',
    'add_order' => './routes/orders.php',
    'get_user_orders' => './routes/orders.php',
    'get_driver_orders' => './routes/orders.php',
    'update_profile' => './routes/users.php', // Ajout de la route pour modifier le profil
    'get_tarif' => './routes/tarifs.php', // Ajout de la route pour modifier le profil
    'get_notif' => './routes/notif.php', // Ajout de la route pour modifier le profil
    'accept_ride' => './routes/notif.php', // Ajout de la route pour modifier le profil
    'cancel_ride' => './routes/notif.php', // Ajout de la route pour modifier le profil
    'update_location' => './routes/location.php', // Ajout de la route pour modifier le profil
    'update_status' => './routes/location.php', // Ajout de la route pour modifier le profil
    'today_trip' => './routes/orders.php', // Ajout de la route pour modifier le profil
];

// Parse the request
$route = isset($_GET['route']) ? $_GET['route'] : null;

if (!$route || !isset($routes[$route])) {
    sendResponse(404, ["error" => "Endpoint not found."]);
}

// Include the appropriate route file
require_once $routes[$route];

// Call the appropriate function based on the route
switch ($route) {
    case 'login':
        $data = getJsonInput();
        login($data);
        break;

    case 'signup':
        $data = getJsonInput();
        signup($data);
        break;

    case 'add_order':
        $data = getJsonInput();
        add_order($data);
        break;

    case 'get_user_orders':
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        if (!$user_id) {
            sendResponse(400, ["error" => "User ID is required"]);
        }
        get_user_orders($user_id);
        break;
    case 'get_driver_orders':
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        if (!$user_id) {
            sendResponse(400, ["error" => "User ID is required"]);
        }
        get_driver_orders($user_id);
        break;

    case 'update_profile':
        $data = getJsonInput();
        update_profile($data);
        break;
    case "get_tarif":
        get_tarifs();
        break;
    case "get_notif":
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        get_notif($user_id);
        break;
    case "update_location":
        $data = getJsonInput();
        update_location($data);
        break;
    case "update_status":
        $data = getJsonInput();
            update_status($data);
            break;
    case "accept_ride":
    
        $data = getJsonInput();
        // var_dump($data);
        accept_ride($data);
        break;
    case "cancel_ride":
    
        $data = getJsonInput();
        // var_dump($data);
        cancel_ride($data);
        break;
    case 'today_trip':
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
        $status= $_GET['status'];
         
        if (!$user_id ) {
                sendResponse(400, ["error" => "User ID is required and status"]);
        }
        // sendResponse(200,$_GET);
        today_trip($user_id,$status);
        break;
    default:
        sendResponse(404, ["error" => "Route not handled."]);
        break;
}
?>