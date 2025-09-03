<?php
// api.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$filename = "data.json";

// If the file doesn't exist or is empty, populate with default users
if (!file_exists($filename) || filesize($filename) === 0) {
    $defaultUsers = [
        ["id" => 1, "name" => "Joshua", "age" => 20, "address" => "Cebu City"],
        ["id" => 2, "name" => "Maria", "age" => 22, "address" => "Manila"]
    ];
    file_put_contents($filename, json_encode($defaultUsers, JSON_PRETTY_PRINT));
}

// Load existing users from file
$users = json_decode(file_get_contents($filename), true);

// Handle GET request (fetch all users)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($users);
}

// Handle POST request (create a new user)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data && isset($data["name"], $data["age"], $data["address"])) {
        $newUser = [
            "id" => end($users)["id"] + 1,  // Get the last user's ID + 1
            "name" => $data["name"],
            "age" => $data["age"],
            "address" => $data["address"]
        ];

        $users[] = $newUser;
        file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));

        echo json_encode(["message" => "User created!", "data" => $newUser]);
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }
}

// Handle PUT request (update user)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data && isset($data["id"])) {
        $found = false;
        foreach ($users as &$user) {
            if ($user["id"] == $data["id"]) {
                $user["name"] = $data["name"] ?? $user["name"];
                $user["age"] = $data["age"] ?? $user["age"];
                $user["address"] = $data["address"] ?? $user["address"];
                $found = true;
                break;
            }
        }

        if ($found) {
            file_put_contents($filename, json_encode($users, JSON_PRETTY_PRINT));
            echo json_encode(["message" => "User updated!", "updated_user" => $data]);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
    } else {
        echo json_encode(["error" => "Invalid input, missing ID"]);
    }
}

// Handle DELETE request (delete user by ID)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data && isset($data["id"])) {
        $originalCount = count($users);
        $users = array_filter($users, function($user) use ($data) {
            return $user["id"] != $data["id"];
        });

        if (count($users) < $originalCount) {
            file_put_contents($filename, json_encode(array_values($users), JSON_PRETTY_PRINT));
            echo json_encode([
                "message" => "User deleted!",
                "deleted_user_id" => $data["id"]
            ]);
        } else {
            echo json_encode(["error" => "User not found"]);
        }
    } else {
        echo json_encode(["error" => "Invalid input, missing ID"]);
    }
}
