<h1>OpenID Authentication</h1>
<p>A site identifying as
<a href="<?php p($_['site']);?>">
<?php p($_['site']);?>
</a>
has asked us for confirmation that
<a href="<?php p($_['openid']);?>">
<?php p($_['openid']);?>
</a>
is your identity URL.
</p>
<form method="post">
<input type="checkbox" name="forever">
<label for="forever">forever</label><br>
<input type="hidden" name="openid_action" value="trust">
<input type="submit" name="allow" value="Allow">
<input type="submit" name="deny" value="Deny">
</form>