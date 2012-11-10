<input type="submit" value="test" onclick="test();">
<ul id="result"></ul>
<script src="/remote.php/core.js"> </script>
<script>
  var remoteStorageOrigin = '<?php require_once 'public/config.php'; echo OCP\Config::getAppValue('unhosted_apps', 'storage_origin'); ?>';
  if(window.location.host==remoteStorageOrigin) {
    console.log('set the remoteStorage origin to something else than '+window.location.host+' please! Go to Settings->Admin->Unhosted apps');
  }
  function ajax(endpoint, params, cb) {
    var xhr = new XMLHttpRequest();
    var path = '/?app=unhosted_apps&getfile=ajax/'+endpoint;
    xhr.open('POST', path, true);
    xhr.onreadystatechange = function() {
      if(xhr.readyState == 4) {
        if(xhr.status==200) {
          var result = {};
          try {
            result = JSON.parse(xhr.responseText);
            if(result.status=='success') {
              console.log('ajax success');
              cb(null, result);
            } else {
              console.log('ajax fail 1');
              cb(result);
            }
          } catch(e) {
            console.log('ajax fail 2 '+e.message);
            cb(xhr.responseText);
          }
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
    var path = 'https://'+remoteStorageOrigin+'/?user='+encodeURIComponent(uid)+'&path=/'+path;
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
    var path = 'https://'+remoteStorageOrigin+'/?user='+encodeURIComponent(uid)+'&path=/'+path;
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
    var path = 'https://'+remoteStorageOrigin+'/?user='+encodeURIComponent(uid)+'&path=/'+path;
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
  var tests = [
    function(cb) { 
      ajax('addapp.php', {
        manifest_path: 'a',
        scopes: 'b'
      }, function(err1, data1) {
        if(err1) {
          cb(JSON.stringify(err1)+' while adding');
        } else {
          ajax('listapps.php', {
          }, function(err2, data2) {
            if(err2) {
              cb(err2+' while first listing');
            } else {
              if(data2.apps.length!=1) {
                cb('not one app');
              } else if(data2.apps[0].manifest_path != 'a') {
                cb('wrong manifest path');
              } else if(data2.apps[0].scopes != 'b') {
                cb('wrong scopes');
              } else if(data2.apps[0].access_token != data1.token) {
                cb('wrong token');
              } else {
                ajax('removeapp.php', {
                  token: data1.token
                }, function(err3, data3) {
                  if(err3) {
                    cb(err3+' while removing');
                  } else {
                    ajax('listapps.php', {
                    }, function(err4, data4) {
                      if(!data4.apps || data4.apps.length != 0) {
                        cb('not zero apps');
                      } else {
                        cb(err4);
                      }
                    });
                  }
                });
              }
            }
          });
        }
      });
    },
    function(cb) {
      ajax('storemanifest.php', {
        origin: 'http://litewrite.net',
        launch_path: '/',
        icons: {'128': 'http://litewrite.net/img/litewrite-touch.png'},
        name: 'Litewrite',
        permissions: { documents: {access: 'readwrite'}},
        manifest_path: 'apps/litewrite/manifest.json'
      }, function(err1, data1) {
        if(err1) {
          cb('fail 1'+JSON.stringify(err1));
        } else {
          ajax('addapp.php', {
            manifest_path: 'apps/litewrite/manifest.json',
            scopes: JSON.stringify({r: ['apps', 'documents'], w: ['documents']}) 
          }, function(err2, data2) {
            if(err2) {
              cb('fail 2'+JSON.stringify(err2));
            } else {
              rsget(data2.token, 'admin', 'apps/litewrite/manifest.json', function(err3, data3) {
                if(err3) {
                  cb('fail 4'+JSON.stringify(err3));
                } else {
                  try {
                    data3 = JSON.parse(data3.content);
                  } catch(e) {
                    cb('fail 3'+JSON.stringify(data3));
                    cb(data3);
                  }
                  if(data3.icons['128'] != 'http://litewrite.net/img/litewrite-touch.png') {
                    cb('wrong icon');
                  } else if(data3.name != 'Litewrite') {
                    cb('wrong name'+JSON.stringify(data3));
                  } else if(data3.origin != 'http://litewrite.net') {
                    cb('wrong launch url');
                  } else {
                    ajax('removeapp.php', {
                      token: data2.token
                    }, function(err4, data4) {
                      cb((err4?'fail 5'+JSON.stringify(err4):err4));
                    });
                  }
                }
              });
            }
          });
        }
      });
    },
    function(cb) {
      ajax('addapp.php', {
        manifest_path: 'azz',
        scopes: JSON.stringify({r:['foo', 'baz'], w:['baz', 'bar']})
      }, function(err1, data1) {
        if(err1) {
          cb(err1);
        } else {
          rsput(data1.token, 'admin', 'baz/zam', 'zi', 'zo', function(err2, data2) {
            if(err2) {
              cb(err2);
            } else {
              rsget(data1.token, 'admin', 'baz/zam', function(err3, data3) {
                console.log(data3);
                if(err3) {
                  cb(err3);
                } else if(data3.contentType.split(';')[0] != 'zi') {
                  cb('wrong content-type');
                } else if(data3.content != 'zo') {
                  cb('wrong content');
                } else {
                  rsdel(data1.token, 'admin', 'baz/zam', function(err4, data4) {
                    cb(err4);
                  });
                }
              });
            }
          });
        }
      });
    },
  ];
  var step;
  function test() {
    step=0;
    runTheRest();
  }
  function runTheRest() {
    if(step==tests.length) {
      return;
    }
    tests[step](function(err) {
      if(err === null) {
        document.getElementById('result').innerHTML += '<li>'+step+' - PASS</li>';
      } else {
        document.getElementById('result').innerHTML += '<li>'+step+' - FAIL: '+err+'</li>';
      }
      step++;
      runTheRest();
    });
  }
</script>
