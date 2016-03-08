<form id="user_oauth">
    <div class="section">
        <h2>OAuth</h2>
        <input type="text" name="introspectionEndpoint" id="introspectionEndpoint" value="<?php p($_['introspectionEndpoint']); ?>" placeholder="<?php p($l->t('Introspection endpoint'));?>" />
        <br />
        <span class="msg"><?php p($l->t('Provide the OAuth 2.0 Authorization Server introspection endpoint here.'));?></span>
    </div>
</form>
