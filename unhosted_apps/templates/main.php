<style> .square { border-style:solid; border-width:1px; float:left; width:200px; height:200px; display:block; overflow:hidden; } </style>
<style id="editMode"> .remove_ { display: inline; } </style>
<div style="width:100%" id="icons">
</div>
<input id="mainButton" type="submit" value="edit" onclick="changeMode()">
<div id="updateDiv">
  <input type="submit" value="install default apps" onclick="installDefaultApps();">
  Source: <input id="appsource" value="https://apps.unhosted.org/default.json" style="width:20em">
</div>
<div id="addDiv"></div>
<script>
  var parsedParams = { };
  function addApp() {
    console.log('installing:');
    console.log(parsedParams);
    installApp(parsedParams);
  }
  function checkForAdd() {
    var rawParams = location.search.substring(1).split('&');
    for(var i=0; i<rawParams.length; i++) {
      var parts = rawParams[i].split('=');
      if(parts[0]=='response_type' && parts[1]=='token') {
        parsedParams.addRequested = true;
      } else if(parts[0]=='redirect_uri') {
        var parser = document.createElement('a');
        parser.href = decodeURIComponent(parts[1]);
        parsedParams.name = parser.hostname;
        parsedParams.origin = parser.protocol + '//' + parser.host;
        parsedParams.launch_path = parser.pathname;
      } else if(parts[0]=='scope') {
        parsedParams.permissions = {};
        var scopeParts = decodeURIComponent(parts[1]).split(' ');
        for(var j=0; j<scopeParts.length; j++) {
          var scopePartParts = scopeParts[j].split(':');
          if(scopePartParts[0]=='') {
            scopePartParts[0]='root';
          }
          parsedParams.permissions[scopePartParts[0].replace(/[^a-z]/, '')] = {
            description: 'Requested by the app in the OAuth dialog',
            access: (scopePartParts[1]=='r'?'readonly':'readwrite')
          };
        }
      }
    }
    if(parsedParams.addRequested) {
      var str = 'Give '+parsedParams.name+' access to '
      for(var i in parsedParams.permissions) {
        if(i=='root') {
          str += 'everything';
        } else if(i=='apps') {
          str += 'which apps you have installed';
        } else {
          str += i;
        }
        if(parsedParams.permissions[i]=='readonly') {
          str += ' (read only)';
        }
        str += ', ';
      }
      str = str.substring(0, str.length -2)+'.';
      document.getElementById('addDiv').innerHTML = str;
      mode='add';
      showMode();
    }
  }
  function changeMode() {
    if(mode=='main') {
      mode = 'edit';
    } else if(mode=='edit') {
      mode = 'main';
    } else {//mode=='add'
      addApp();
      mode = 'main';
    }
    showMode();
  }
  function showMode() {
    document.getElementById('mainButton').value = (mode=='edit'?'done':(mode=='add'?'add':'edit'));
    document.getElementById('addDiv').style.display = (mode=='add'?'block':'none');
    document.getElementById('updateDiv').style.display = (mode=='edit'?'block':'none');
    document.getElementById('editMode').innerHTML='.remove_ {display:'+(mode=='edit'?'inline':'none')+';}';
  }
  var mode = 'main';
  showMode();
  checkForAdd(); 
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
  xhr.setRequestHeader('requesttoken', oc_requesttoken);
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
    manifestObj.slug = manifestObj.name.toLowerCase().replace(/[^a-z0-9\ ]/, '').replace(' ', '-');
    manifestObj.manifest_path = 'apps/'+manifestObj.slug+'/manifest.json';
    ajax('storemanifest.php', manifestObj, function(err1, data1) {
      if(err1) {
        console.log(err1, data1);
      } else {
        var scopesObj = {r:[], w:[]};
        for(var i in manifestObj.permissions) {
          scopesObj.r.push(i);
          if(manifestObj.permissions[i]!='readonly') {
            scopesObj.w.push(i);
          }
        }
        ajax('addapp.php', {
          manifest_path: 'apps/'+manifestObj.slug+'/manifest.json',
          scopes: JSON.stringify(scopesObj)
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
var rendering=0;
function showApp(masterToken, uid, appToken, manifestPath) {
  rendering++;
  console.log('showApp('+masterToken+', '+uid+', '+appToken+', '+manifestPath+');');
  rsget(masterToken, uid, manifestPath, function(err, data) {
    rendering--;
    try {
      manifest=JSON.parse(data.content);
    } catch(e) {
      console.log(e);
    }
     
    document.getElementById('icons').innerHTML += '<div class="square" style="margin:1em;border-radius:1em">'
      + '<a target="_blank" href="' + manifest.orign + manifest.launch_path
      + '#storage_root='+encodeURIComponent('https://' + remoteStorageOrigin
        + '/?user=' + encodeURIComponent(uid) + '&path=')
      + '&storage_api=2011.04&access_token=' + encodeURIComponent(appToken)+'">'
      + '<img width="50px" height="50px" src="' + manifest.icons['128'] + '">'
      + '</a>'
      + '<span class="remove_" onclick="remove(\''+appToken+'\');">X</span>'
      + '<a style="margin:1em" target="_blank" href="' + manifest.origin + manifest.launch_path
      + '#storage_root='+encodeURIComponent('https://' + remoteStorageOrigin
        + '/?user=' + encodeURIComponent(uid) + '&path=')
      + '&storage_api=2011.04&access_token=' + encodeURIComponent(appToken)+'">'
      + '<br> &nbsp;&nbsp;' + manifest.name + ' </a> </div>';
  });
}
function render() {
  if(rendering) {
    return;
  }
  var uid = 'admin';
  rendering++;
  ajax('listapps.php', {}, function(err, data) {
    rendering --;
    document.getElementById('icons').innerHTML = '';
    var content;
    try {
      content=JSON.parse(data.content);
    } catch(e) {
      console.log(e);
    }
    console.log(content);
    var masterToken;
    for(var i=0; i<content.apps.length; i++) {
      if(content.apps[i].manifest_path=='appsapp') {
        masterToken = content.apps[i].access_token;
        break;
      }
    }
    console.log(masterToken);
    document.getElementById('icons').innerHTML = '';
    for(var i=0; i<content.apps.length; i++) {
      if(content.apps[i].manifest_path!='appsapp') {
        console.log(content.apps[i]);
        showApp(masterToken, uid, content.apps[i].access_token, content.apps[i].manifest_path);
        console.log('added another app, showing mode '+mode);
        showMode();
      }
    }
  });
}

var remoteStorageOrigin = '<?php require_once 'public/config.php'; echo OCP\Config::getAppValue('unhosted_apps', 'storage_origin'); ?>';

if((remoteStorageOrigin == '') || (window.location.host==remoteStorageOrigin)) {
  document.getElementById('icons').innerHTML = 'You need to point a second origin to your server, so a subdomain, or a port other than '+window.location.host+'. Then go to Setting -> Admin -> Unhosted apps and set the storage origin.';
} else {
  render();
}
</script>
