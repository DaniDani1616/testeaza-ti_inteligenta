<?php
session_start();
require 'database.php';

$data = json_decode(file_get_contents('php://input'), true);
$shareId = $data['shareId'];
$userId = $data['userId'];

// Salvează în baza de date
$stmt = $mysqli->prepare("INSERT INTO share_links (user_id, share_id, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param('is', $userId, $shareId);
$stmt->execute();
$stmt->close();