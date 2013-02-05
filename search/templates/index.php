<?php OCP\Util::addStyle('search', 'list-view');  ?>
<!-- search form -->
<div id="controls">
    <form id="search-form" action="<?php echo OCP\Util::linkTo('search', 'index.php'); ?>" method="get">
        <input type="text" name="query" value="<?php echo $_['query']; ?>">
        <button class="button search_button">Search</button>
    </form>
</div>

<!-- results -->
<?php echo $_['html']; ?>
