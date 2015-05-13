ECOS采用pathinfo做资源定位，所以要求`$_SERVER`环境变量中必须要有PATHINFO或则ORGI\_PATHINFO

一般在apache或者iis下都没有什么问题，但是在nginx下需要对配置文件做一些设置才可以

把下面的代码保存为pathinfo.conf文件，存放在nginx的conf目录下

```
set $real_script_name $fastcgi_script_name;
if ($fastcgi_script_name ~ "(.+?\.php)(/.+)") {
    set $real_script_name $1;
    set $path_info $2;
}
fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
fastcgi_param SCRIPT_NAME $real_script_name;
fastcgi_param PATH_INFO $path_info;
```

在站点的php引擎调用段的最下面包含这个文件就行了。下面提供一个真实的样例：

### 主配置文件 ###

```
server
{
    listen       80;
    server_name  192.168.6.141;
    index index.html index.htm index.php;
    root  /srv/http;
    autoindex off;

    location ~ .*\.php
    {
      include php_fcgi.conf;
      include pathinfo.conf;
    }

    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf)$
    {
      expires      30d;
    }

    location ~ .*\.(js|css)$
    {
      expires      1h;
    }

    access_log off;
    location /nginx_status {
        stub_status on;
        access_log   off;
 
   }
}
```

这个地方有一个地方需要注意，就是对php文件的捕获必须是这样的形式
```
location ~ .*\.php
```

以往的
```
location ~ .*\.php$
```

形如/index.php/shopadmin/xxxx/yyyy这样的请求将不会进入php fastcgi处理器。


### rewrite ###

如果想把index.php也隐藏掉，可用下面的rewrite规则

```

location / {
    if (!-e $request_filename) {
        rewrite ^/(.+\.(html|xml|json|htm|php|jsp|asp|shtml))$ /index.php/$1 last;
    }
}

location ~ .*\.php
{
     include php_fcgi.conf;
     include pathinfo.conf;
}

```

### Q&A ###

**1)如何测试我的设置是正确的？**

请用下面的文件

http://code.google.com/p/shopexts/source/browse/trunk/ecos/svinfo.php

放到根目录进行测试，形如

http://www.xxx.com/svinfo.php/test

正常情况下，应该能看到这样的输出：

**2)为什么我带上pathinfo后是返回404**

nginx爆过pathinfo解析错误的漏洞，一般的解决方案是在php.ini中将`cgi.fix_pathinfo`设置为0，这样的话，phpinfo就不能用了，所以必须把`cgi.fix_pathinfo`设置为1，服务器安全性，请用“可写目录，不可执行php”的原则去防范。