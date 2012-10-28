<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::encodedPrint((bool)OCP\Config::getUserValue(OCP\User::getUser(), 'notify', 'autorefresh', true));
