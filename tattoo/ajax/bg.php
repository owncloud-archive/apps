<?php

// Check if we are a user
OCP\JSON::checkLoggedIn();

OCP\JSON::success(array('data' => OCP\Config::getUserValue(OCP\User::getUser(),'tattoo','wallpaper','none')));
