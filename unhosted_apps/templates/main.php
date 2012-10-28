<h1 id="caption">Unhosted apps: <input type="submit" value="edit" onclick="editApps()"></h1>
<div style="width:100%" id="icons"></div>
<script>
function editApps() {
  var elts = document.getElementsByClassName('remove_');
  for(var i=0; i<elts.length; i++) {
    elts[i].style.display='inline';
  }
}
  
function ajax(endpoint, params, cb) {
  var xhr = new XMLHttpRequest();
  var path = '/?app=unhosted_apps&getfile=ajax/'+endpoint;
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
  xhr.send(JSON.stringify(params));
}
function rsget(token, uid, path, cb) {
  var xhr = new XMLHttpRequest();
  var path = 'https://'+remoteStorageOrigin+'/?user='+encodeURIComponent(uid)+'&path='+encodeURIComponent('/'+path);
  xhr.open('GET', path, true);
  xhr.onreadystatechange = function() {
    if(xhr.readyState == 4) {
      if(xhr.status==200) {
        console.log('rsget success');
        cb(null, {
          contentType: xhr.getResponseHeader('Content-Type'),
          content: xhr.responseText
        });
      } else {
        console.log('rsget fail 1');
        cb(xhr.status);
      }
    }
  };
  xhr.setRequestHeader('Authorization', 'Bearer '+token);
  xhr.send();
}
function rsput(token, uid, path, contentType, content, cb) {
  var xhr = new XMLHttpRequest();
  var path = 'https://'+remoteStorageOrigin+'/?user='+encodeURIComponent(uid)+'&path='+encodeURIComponent('/'+path);
  xhr.open('PUT', path, true);
  xhr.onreadystatechange = function() {
    if(xhr.readyState == 4) {
      if(xhr.status==200) {
        console.log('rsput success');
        cb(null, {
          contentType: xhr.getResponseHeader('Content-Type'),
          content: xhr.responseText
        });
      } else {
        console.log('rsput fail 1');
        cb(xhr.status);
      }
    }
  };
  xhr.setRequestHeader('Authorization', 'Bearer '+token);
  xhr.setRequestHeader('Content-Type', contentType);
  xhr.send(content);
}
function rsdel(token, uid, path, cb) {
  var xhr = new XMLHttpRequest();
  var path = 'https://'+remoteStorageOrigin+'/?user='+encodeURIComponent(uid)+'&path='+encodeURIComponent('/'+path);
  xhr.open('DELETE', path, true);
  xhr.onreadystatechange = function() {
    if(xhr.readyState == 4) {
      if(xhr.status==200) {
        console.log('rsget success');
        cb(null, {
          contentType: xhr.getResponseHeader('Content-Type'),
          content: xhr.responseText
        });
      } else {
        console.log('rsget fail 1');
        cb(xhr.status);
      }
    }
  };
  xhr.setRequestHeader('Authorization', 'Bearer '+token);
  xhr.send();
}
function retrieve(url, cb) {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);
  xhr.onreadystatechange = function() {
    if(xhr.readyState == 4) {
      if(xhr.status==200) {
        console.log('retrieve success');
        cb(null, {
          contentType: xhr.getResponseHeader('Content-Type'),
          content: xhr.responseText
        });
      } else {
        console.log('retrieve fail 1');
        cb(xhr.status);
      }
    }
  };
  xhr.send();
}
function installDefaultApps() {
  retrieve(document.getElementById('appsource').value, function(err1, data1) {
    var apps = [];
    try {
      apps = JSON.parse(data1.content);
    } catch(e) {
    }
    for(var i=0; i<apps.length; i++) {
      installApp(apps[i]);
    }
  });
}
function installApp(manifestObj) {
    manifestObj.manifest_path = 'apps/'+manifestObj.slug+'/manifest.json';
    ajax('storemanifest.php', manifestObj, function(err1, data1) {
      if(err1) {
        console.log(err1, data1);
      } else {
        ajax('addapp.php', {
          manifest_path: 'apps/litewrite/manifest.json',
          scopes: JSON.stringify({r:['documents'], w:['documents']})
        }, function(err2, data2) {
          if(err2) {
            console.log(err2, data2);
          } else {
            ajax('addapp.php', {
              manifest_path: 'appsapp',
              scopes: JSON.stringify({r:['apps'], w:['apps']})
            }, function(err3, data3) {
              if(err3) {
                console.log(err3, data3);
              } else {
                render();
              }
          });
        }
      });
    }
  });
}
function remove(token) {
  ajax('removeapp.php', {
    token: token
  }, function(err, data) {
    render();
  });
}
function showApp(masterToken, uid, appToken, manifestPath) {
  console.log('showApp('+masterToken+', '+uid+', '+appToken+', '+manifestPath+');');
  rsget(masterToken, uid, manifestPath, function(err, data) {
    try {
      manifest=JSON.parse(data.content);
    } catch(e) {
      console.log(e);
    }
     
    document.getElementById('icons').innerHTML += '<div style="margin:0px auto;width:6em;border-style:solid;border-width:1px;border-radius:1em">'
      + '<a style="margin:1em" target="_blank" href="' + manifest.launch_url
      + '#storage_root='+encodeURIComponent('https://' + remoteStorageOrigin
        + '/?user=' + encodeURIComponent(uid) + '&path=')
      + '&storage_api=2011.04&access_token=' + encodeURIComponent(appToken)
      + '"> <img width="50px" height="50px" src="' + manifest.icon + '">'
      + '<span style="display:none" class="remove_" onclick="remove(\''+appToken+'\');">X</span>'
      + '<br> &nbsp;&nbsp;' + manifest.name + ' </a> </div>';
  });
}
function render() {
  var uid = 'admin';
  ajax('listapps.php', {}, function(err, data) {
    document.getElementById('icons').innerHTML = '';
    var content;
    try {
      content=JSON.parse(data.content);
    } catch(e) {
      console.log(e);
    }
    console.log(content);
    var masterToken;
    var haveApps=false;
    for(var i=0; i<content.apps.length; i++) {
      if(content.apps[i].manifest_path=='appsapp') {
        masterToken = content.apps[i].access_token;
        break;
      }
    }
    console.log(masterToken);
    for(var i=0; i<content.apps.length; i++) {
      if(content.apps[i].manifest_path!='appsapp') {
        console.log(content.apps[i]);
        showApp(masterToken, uid, content.apps[i].access_token, content.apps[i].manifest_path);
        haveApps=true;
      }
    }
    if(!haveApps) {
      document.getElementById('icons').innerHTML='<input type="submit" value="install default apps" onclick="installDefaultApps();"> Source: <input id="appsource" value="http://apps.unhosted.org/default.json">';
    }
  });
}

var remoteStorageOrigin = '<?php require_once 'public/config.php'; echo OCP\Config::getSystemValue('storage_origin'); ?>';

if((remoteStorageOrigin == '') || (window.location.host==remoteStorageOrigin)) {
  document.getElementById('icons').innerHTML = 'You need to point a second origin to your server, so a subdomain, or a port other than '+window.location.host+'. Then go to Setting -> Admin -> Unhosted apps and set the storage origin.';
} else {
  render();
}
</script>
