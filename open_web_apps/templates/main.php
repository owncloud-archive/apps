<style> .square { border-style: solid; border-width: 2px; float: left; width: 160px; height: 160px; display: block; overflow: hidden; text-align: center; border-radius: 5px } </style>
<div style="width:100%" id="icons">
<?php
  foreach($_['apps'] as $id => $obj) {
    if($_['scope_diff_id']==$id) {
      echo '<div class="square" style="border-style:dotted">'
        . '<img width="128px" height="128px" src="'.$obj['icon_url'].'">'
        . '<p>' . $obj['name'] . '</p></div>'
        . '<p>wants '.$_['scope_diff_add']['human']
        .'. <input type="submit" value="Allow" onclick="addApp(\''
        .$obj['launch_url'].'\', \''
        .$obj['name'].'\', \''
        .$_['scope_diff_add']['normalized'].'\');" /></p>';
    } else {
      echo '<div class="square">'
        . '<a href="' . $obj['launch_url']
        . '#remotestorage=' . urlencode($_['user_address'])
        . '&access_token=' . urlencode($obj['token'])
        . '&scope=' . urlencode($obj['scope']['normalized'])
        . '">'
        . '<img width="128px" height="128px" src="' . $obj['icon_url']
        . '">'
        . '<p>' . $obj['name'] . '</p>'
        . '</a> </div>';
    }
  }
  if($_['adding_id']) {
    echo '<div class="square" style="border-style:dotted">'
      . '<img width="128px" height="128px" src="">'
      . '<p>' . $_['adding_name'] . '</p></div>'
      . '<p>wants '.$_['adding_scope']['human']
      .'. <input type="submit" value="Install" onclick="addApp(\''
      .$_['adding_launch_url'].'\', \''
      .$_['adding_name'].'\', \''
      .$_['adding_scope']['normalized'].'\');" /></p>';
  }
?>
</div>
<div style=" clear: left ">
  Manifest: <input id="manifestUrl" value="http://music.michiel.5apps.com/michiel_music.webapp" style="width:20em" />
  <input type="submit" value="add manifest" onclick="addManifest();" />
</div>
<script>
  function ajax(endpoint, params, cb) {
    var xhr = new XMLHttpRequest();
    var path = '/?app=open_web_apps&getfile=ajax/'+endpoint;
    xhr.open('POST', path, true);
    xhr.onreadystatechange = function() {
      if(xhr.readyState == 4) {
        if(xhr.status==200) {
          result = {
            contentType: xhr.getResponseHeader('Content-Type'),
            content: xhr.responseText
          };
          cb(null, result);
        } else {
          console.log('ajax fail 3');
          cb(xhr.status);
        }
      }
    };
    xhr.setRequestHeader('requesttoken', oc_requesttoken);
    xhr.send(JSON.stringify(params));
  }

  function addManifest() {
    var manifestUrl = document.getElementById('manifestUrl').value;
    ajax('addmanifest.php', {
      manifest_url_dirty: manifestUrl
    }, function() {
     window.location = '';
    });
  }

  function addApp(launchUrl, name, scope) {
    ajax('addapp.php', {
      launch_url: launchUrl,
      name: name,
      scope: scope
    }, function() {
     window.location = '';
    });
  }

  function removeApp(token) {
    ajax('removeapp.php', {
      id: id
    }, function() {
     window.location = '';
    });
  }
</script>
