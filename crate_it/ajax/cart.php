<?php
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('crate_it');
session_start();

$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

//get file and story it in session
$cart = $_SESSION['cart'];
$action = $_GET['action'];
switch ($action) {
	case 'add':
		if ($cart) {
			$cart .= ','.$dir.$file; //TODO check if this file is already in the cart
		} else {
			$cart = $dir.$file;
		}
		break;
	case 'delete':
		if ($cart) {
			$items = explode(',',$cart);
			$newcart = '';
			foreach ($items as $item) {
				if ($dir.$file != $item) {
					if ($newcart != '') {
						$newcart .= ','.$item;
					} else {
						$newcart = $item;
					}
				}
			}
			$cart = $newcart;
		}
		break;
}
$_SESSION['cart'] = $cart;

print "cart completed";
