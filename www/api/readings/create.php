<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get database connection
require_once '../config/core.php';
require_once '../config/database.php';
require_once '../objects/user.php';
require_once '../objects/counter.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// instantiate user object
$user = new User($db);

//validate call
if(!$user->validateCall($_SERVER['HTTP_X_AUTH_SIGNATURE'], $_SERVER['HTTP_X_AUTH_TOKEN'], $_SERVER['HTTP_X_AUTH_TIMESTAMP'])){
    print_r(json_encode(array("error" => $user->error)));
    exit;
}

$powerReading = new PowerCounter($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// make sure data is not empty
if(!empty($data->date) && !empty($data->value) && !empty($data->counter)){
    // set transaction property values
    $powerReading->date = $data->date;
    $powerReading->value = $data->value;
    $powerReading->counter = $data->counter;

    // create the transaction
    if($powerReading->addReading($user)){

        // set response code - 201 created
        http_response_code(201);

        // tell the user
        echo json_encode(array("error" => NULL, "message" => "Reading created."));
    }
//
//     // if unable to create the product, tell the user
//     else{
//
//         // set response code - 503 service unavailable
//         http_response_code(503);
//
//         // tell the user
//         echo json_encode(array("message" => "Unable to create product."));
//     }
}

// tell the user data is incomplete
else {

    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("error" => "Unable to create transaction. Data is incomplete."));
}
?>
