<?php

// Check if we are a user
OC_JSON::checkLoggedIn();

OC_JSON::success(array('data' => OC_Preferences::getValue(OC_User::getUser(),'tattoo','wallpaper','none')));