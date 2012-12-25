<!--[if IE 8]><style>input[type="checkbox"]{padding:0;}table td{position:static !important;}</style><![endif]-->

<!-- search form -->
<div id="controls">
    <form id="search-form" action="<?php echo OCP\Util::linkTo('search', 'index.php'); ?>" method="get">
        <input type="text" name="query" id="search_query" value="<?php echo $_['breadcrumb']; ?>">
        <button class="button search_button">Search</button>
    </form>
</div>
<div id="file_action_panel"></div>
<div id='notification'></div>

<?php if (empty($_['files'])): ?>
    <div id="emptyfolder"><?php echo $l->t('Nothing found.') ?></div>
<?php endif; ?>

<!-- results list -->
<table class="resultsList">
    <thead>
        <tr>
            <th id='headerName'>
                <input type="checkbox" id="select_all" />
                <span class='name'><?php echo $l->t('Name'); ?></span>
                <span class='selectedActions'>
                    <?php if ($_['allowZipDownload']) : ?>
                        <a href="" class="download"><img class='svg' alt="Download" src="<?php echo OCP\image_path("core", "actions/download.svg"); ?>" /> <?php echo $l->t('Download') ?></a>
                    <?php endif; ?>
                </span>
            </th>
            <th id="headerSize"><?php echo $l->t('Size'); ?></th>
            <th id="headerDate">
                <span id="modified"><?php echo $l->t('Modified'); ?></span>
            </th>
        </tr>
    </thead>
    <tbody id="fileList">
        <?php echo($_['fileList']); ?>
    </tbody>
</table>
<div id="editor"></div>

<!-- config hints for javascript -->
<input type="hidden" name="allowZipDownload" id="allowZipDownload" value="<?php echo $_['allowZipDownload']; ?>" />
