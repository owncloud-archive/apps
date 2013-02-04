<!-- results list -->
<h3 class="search-type"><?php echo $l->t($_['title']); ?></h3>
<table id="search-results">
    <thead>
        <tr>
            <?php echo $_['properties']; ?>
        </tr>
    </thead>
    <tbody>
        <?php echo($_['results']); ?>
    </tbody>
</table>