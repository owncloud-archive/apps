<form id="user_oauth">
    <fieldset class="personalblock">
        <strong>OAuth</strong><br />
        <input type="text" name="tokenInfoEndpoint" id="tokenInfoEndpoint" value="<?php p($_['tokenInfoEndpoint']); ?>" placeholder="<?php p($l->t('Token Info Endpoint'));?>" />
        <br />
        <span class="msg">Provide the OAuth Authorization Server Token Info Endpoint here.</span>
    </fieldset>
</form>
