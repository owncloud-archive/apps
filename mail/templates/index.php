<?php
$accounts = OCA_Mail\App::getFolders(OCP\User::getUser());
if (count($accounts) == 0) {
	echo $this->inc("part.no-accounts");
} else {
	?>

<div id="leftcontent" class="leftcontent">
    <div id="mail-folders"></div>
    <div id="bottomcontrols">
        <button class="control settings" title="<?php echo $l->t('Settings'); ?>"></button>
    </div>
</div>
<div id="rightcontent" class="rightcontent">
</div>
<?php } ?>