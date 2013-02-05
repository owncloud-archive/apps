<fieldset class="personalblock">
    <legend><strong>Advanced Search</strong></legend>
    <p>
        <input id="lucene-enabled" type="checkbox" <?php echo ($_['lucene_enabled']) ? 'checked="checked"' : ''; ?>/> Enable Lucene Indexing
        <input id="lucene-reindex" type="button" value="Re-index All Objects" <?php echo ($_['lucene_enabled']) ? '' : 'disabled="disabled"'; ?>/>
    </p>
    <?php if ($_['lucene_enabled']): ?>
        <?php if ($_['index_created']): ?>
            <p>
                Index size: <span id="index-size"><?php echo $_['index_size']; ?></span>. 
                <span id="index-message" style="display:none">
                    Indexing in progress: <span id="index-count"></span>/<span id="objects-count"></span> <span id="index-file"></span>
                </span>
            </p>
        <?php else: ?>
            <p>
                Error: the Lucene index should have been created but wasn't; contact the application developer.
            </p>
        <?php endif; ?>
    <?php endif; ?>
</fieldset>
