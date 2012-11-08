<?php header('Access-Control-Allow-Origin', '*'); ?>
{
  "links":[{
    "href": "https://<?php require_once 'public/config.php'; echo OCP\Config::getAppValue('unhosted_apps', 'storage_origin'); ?>/?user=admin&path=",
    "rel": "remoteStorage",
    "type": "https://www.w3.org/community/rww/wiki/read-write-web-00#simple",
    "properties": {
      "auth-method": "https://tools.ietf.org/html/draft-ietf-oauth-v2-26#section-4.2",
      "auth-endpoint": "<?php echo ($_SERVER['HTTPS']=='on'?'https://':'http://').$_SERVER['SERVER_NAME'] .':'. $_SERVER['SERVER_PORT']; ?>/?app=unhosted_apps&getfile=main.php"
    }
 }]
}
