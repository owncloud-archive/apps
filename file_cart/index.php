<?php

// Check if we are a user
OCP\User::checkLoggedIn();
session_start();
//OCP\App::setActiveNavigationEntry('cart');

//get the files which are in the cart
$items = array();
$cart = $_SESSION['cart'];
if($cart){
	$items = explode(',',$cart);
}

// create a new template to show the cart
$tmpl = new OCP\Template('file_cart', 'index', 'user');
$tmpl->assign('cart_files', $items);
$tmpl->printPage();