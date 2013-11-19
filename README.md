wampserver-vhost-manager
========================

create, edit or delete virtualhost under wamp.

## Install

   Create a directory to set virtual host config files there :
  
   c:\wamp\vhost
   
   Then add those lignes in your httpd.conf :
   
```   
   NameVirtualHost *:80
   Include c:/wamp/vhost/*.conf
```

  Last thing add the directory in parameters.ymp file :
  
```    
  vhost_dir: 'c:/wamp/vhost/'
```  

## License

  This bundle is available under the [MIT license](LICENSE).
