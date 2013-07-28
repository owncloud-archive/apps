<style> .square { border-style: solid; border-width: 2px; float: left; width: 160px; height: 120px; display: block; overflow: hidden; text-align: center; border-radius: 5px } </style>
<div style="width:100%" id="icons">
<?php
  if(isset($_['launch_app'])) {
    $obj = $_['apps'][$_['launch_app']];
    echo '<input id="autoLaunch" '
      .' data-launch-url="'.$obj['launch_url'].'"'
      .' data-name="'.$obj['name'].'"'
      .' data-token="'.$obj['token'].'"'
      .' data-useraddress="'.$_['user_address'].'"'
      .' data-scope="'.$obj['scope'].'"'
      .' />';
  } else {
    foreach($_['apps'] as $id => $obj) {
      if(isset($_['scope_diff_id']) && $_['scope_diff_id']==$id) {
        echo '<div class="square" style="border-style:dotted;height:160px">'
          . '<p>' . $obj['name'] . '</p>'
          . '<p>wants '.$_['scope_diff_add']['human']
          .'. <input type="submit" value="Allow & launch" id="allowBtn"'
          .'  data-launch-url="'.$obj['launch_url'].'"'
          .'  data-name="'.$obj['name'].'"'
          .'  data-useraddress="'.$_['user_address'].'"'
          .'  data-scope="'.$_['scope_diff_add']['normalized'].'"'
          .' /></p>';
      } else {
        echo '<div class="square">'
          . '<a target="_blank" href="' . $obj['launch_url']
          . '#remotestorage=' . urlencode($_['user_address'])
          . '&access_token=' . urlencode($obj['token'])
          . '&scope=' . urlencode($obj['scope'])
          . '">'
          . '<p>' . $obj['name'] . '</p>'
          . '<input type="submit" value="Launch" />'
          . '</a>'
          . '<input type="submit" value="Remove" class="removeBtn"'
          .'  data-id="'.$id.'"'
          .' />'
          . ' </div>';
      }
    }
    if(isset($_['adding_id']) && $_['adding_id']) {
      echo '<div class="square" style="border-style:dotted;height=160px">'
        . '<p>' . $_['adding_name'] . '</p>'
        . '<p> wants '.$_['adding_scope']['human']
        .'. <input type="submit" value="Add & launch" id="allowBtn"'
          .' data-launch-url="'.$_['adding_launch_url'].'"'
          .' data-useraddress="'.$_['user_address'].'"'
          .' data-name="'.$_['adding_name'].'"'
          .' data-scope="'.$_['adding_scope']['normalized'].'"'
        .' /></p></div>';
    }
  }
?>
</div>
<script src="<?php echo OC::$WEBROOT; ?>/apps/open_web_apps/js/main.js"></script>
