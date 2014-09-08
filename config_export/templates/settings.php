<form id="export" action="#" method="post">
    <div class="section">
        <h2><?php p($l->t('Export configuration'));?></h2>
        <input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" id="requesttoken">
        <input type="submit" name="export" value="<?php p($l->t('Go')); ?>" />
    </div>
</form>