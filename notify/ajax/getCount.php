<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
OCP\JSON::encodedPrint(OC_Notify::getUnreadNumber());
