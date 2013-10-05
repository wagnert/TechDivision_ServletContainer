# Introduction

The servlet container, may also be called web container, will provide you with a fully HTTP 1.1 compatible web server. The actual container implementation allows you to serve all types of HTTP request by interacting with so called servlets that, in our case, are pure PHP classes implementing the Servlet inferface.

Instead of writing a bootstrapper file, index.php in most cases, a servlet is a class, that provides methods that will be automatically invoked by the servlet container when a request, your servlet maps to the request URI, came in. As [HTTP 1.1](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html) specifies eight methods the Servlet interface defines one method for each of them. In most cases you will implement the GET and/or the POST method.

# 3 steps to your first servlet

The following example will give you a short introduction what steps you need to make to implement your first servlet.

## 1st step - folder structure
As the servlet container is built to handle mutliple applications the 1st step is to create a folder in the `webapps` folder of your application server installation root directory, that should be `/opt/appserver` by default. For example we'll create a folder named `myapp`.
```
root@debian:~# cd /opt/appserver/webapps
root@debian:~# mkdir myapp
root@debian:~# mkdir myapp/WEB-INF
root@debian:~# mkdir myapp/WEB-INF/classes
root@debian:~# mkdir myapp/WEB-INF/classes/My
root@debian:~# mkdir myapp/WEB-INF/classes/My/App
```
That's all folders we need, pretty easy, right?

## 2nd step - create a mapping file

In the second step you'll create a file named `web.xml` that declares your servlet and maps the routes to your servlet. To make it easy, our first servlet should match all incoming requests. So your `web.xml` should have the following structure.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<web-app version="1.0">

    <display-name>A simple servlet example</display-name>
    <description>My first web application</description>

    <!-- declares your first serlvet -->
    <servlet>
        <description><![CDATA[My first servlet.]]></description>
        <display-name>MyServlet</display-name>
        <servlet-name>MyServlet</servlet-name>
        <servlet-class>\My\App\Servlets\MyServlet</servlet-class>
    </servlet>
    
    <!-- matches URL http://127.0.0.1:8586/myapp -->
    <servlet-mapping>
        <servlet-name>MyServlet</servlet-name>
        <url-pattern>/</url-pattern>
    </servlet-mapping>
    
    <!-- matches URL http://127.0.0.1:8586/myapp/* -->
    <servlet-mapping>
        <servlet-name>MyServlet</servlet-name>
        <url-pattern>/*</url-pattern>
    </servlet-mapping>
    
</web-app>
```

Save this file as `/opt/appserver/webapps/myapp/WEB-INF/web.xml`.

## 3rd step - implement your servlet

The last step is the servlet class itself. The simples servlet implementation will be a really simple class that extends the `HttpServlet` class delivered with the servlet container. Our first servlet will only handle a GET request by adding the famous `Hello World`, sourrounded with a `<p>` tag, as content to the response.

```php
<?php

namespace My\App\Servlets;

use TechDivision\ServletContainer\Servlets\HttpServlet;

class MyServlet extends HttpServlet
{

    /**
     * Will be invoked by the servlet container when a GET request came in.
     * 
     * @return void
     * @see HttpServlet::doGet(Request $req, Response $res)
     */
    public function doGet(Request $req, Response $res)
    {
        $res->setContent('<p>Hello World</p>');
    }
}

```

After saving the file in the folder `/opt/appserver/webapps/myapp/WEB-INF/classes/My/App/Servlets/MyServlet.php` and restarting the application server with the init script

```
root@debian:~# /etc/init.d/appserver restart
```

you can open your favourite browser and enter the URL `http://127.0.0.1:8586/myapp`. Voilà, you should see your `Hello World`.

I hope this short example give's you an impression how the servlet container works and where you can start to implement your application. As the routing will be done by the `web.xml` file you can understand a servlet as some kind of controller class that implement's your actions if you'll see it in a MVC context.
