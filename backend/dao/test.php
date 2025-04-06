<?php
require_once 'dao/UserDao.php';
require_once 'dao/OrderDao.php';

$userDao = new UserDao();
$orderDao = new OrderDao();

$userDao->insert([
   'firstName' => 'John',
   'lastName' => 'Doe',
   'email' => 'john@example.com',
   'password' => password_hash('password123', PASSWORD_DEFAULT)
]);

$orderDao->insert([
   'userId' => 1,
   'productId' => 1,
   'purchaseDate' => date('Y-m-d H:i:s')
]);

$users = $userDao->getAll();
print_r($users);


$orders = $orderDao->getAll();
print_r($orders);
?>