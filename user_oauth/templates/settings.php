<form id="user_oauth">
    <fieldset class="personalblock">
        <strong>OAuth</strong><br />
        <input type="text" name="introspectionEndpoint" id="introspectionEndpoint" value="<?php p($_['introspectionEndpoint']); ?>" placeholder="<?php p($l->t('Introspection endpoint'));?>" />
        <br />
        <span class="msg">Provide the OAuth 2.0 Authorization Server introspection endpoint here.</span>
    </fieldset>
</form>
