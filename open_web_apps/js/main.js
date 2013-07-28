function ajax(endpoint, params, cb) {
  var xhr = new XMLHttpRequest();
  var path = OC.filePath('open_web_apps', 'ajax', endpoint);
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

function addApp(launchUrl, userAddress, name, scope) {
  ajax('addapp.php', {
    launch_url: launchUrl,
    name: name,
    scope: scope
  }, function(err, data) {
    var res;
    try {
      res = JSON.parse(data.content);
      window.location = launchUrl
        + '#remotestorage=' + encodeURIComponent(userAddress)
        + '&access_token=' + encodeURIComponent(res.token)
        + '&scope=' + encodeURIComponent(scope);
    } catch(e) {
      console.log('could not launch app', err, data);
    }
  });
}

function removeApp(id) {
  ajax('removeapp.php', {
    id: id
  }, function() {
   window.location = '';
  });
}

//.

var autoLaunch = document.getElementById('autoLaunch');
if(autoLaunch) {
  window.location = autoLaunch.getAttribute('data-launch-url')
    + '#remotestorage=' + encodeURIComponent(autoLaunch.getAttribute('data-useraddress'))
    + '&access_token=' + encodeURIComponent(autoLaunch.getAttribute('data-token'))
    + '&scope=' + encodeURIComponent(autoLaunch.getAttribute('data-scope'));
} else {
  var allowBtn = document.getElementById('allowBtn');
  if(allowBtn) {
    allowBtn.onclick = function() {
      addApp(
        allowBtn.getAttribute('data-launch-url'),
        allowBtn.getAttribute('data-useraddress'),
        allowBtn.getAttribute('data-name'),
        allowBtn.getAttribute('data-scope')
      );
    };
  }
  var removeBtns = document.getElementsByClassName('removeBtn');
  for(var i=0; i<removeBtns.length; i++) {
    removeBtns[i].onclick = (function(btn) {
      return function() {
        removeApp(btn.getAttribute('data-id'));
      };
    })(removeBtns[i]);
  }
  var addManifestBtn = document.getElementById('addManifestBtn');
  if(addManifestBtn) {
    addManifestBtn.onclick = addManifest;
  }
}

