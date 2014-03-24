# Introduction

The servlet container, may also be known as web container, will provide you with a fully HTTP 1.1 compatible web server. The actual container implementation allows you to serve all types of HTTP request by interacting with so called servlets. That, in our case, are pure PHP classes implementing the Servlet inferface. The servlet container is not a standalone daemone, instead it is a component of appserver.io and needs the application server [runtime](https://github.com/techdivision/TechDivision_Runtime) and [base](https://github.com/techdivision/TechDivision_ApplicationServer) component to work.

Instead of writing a bootstrapper file, index.php in most cases, a servlet is a class, that provides methods that will be automatically invoked by the servlet container when a request, your servlet maps to the request URI, came in. As [HTTP 1.1](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html) specifies eight methods the Servlet interface defines one method for each of them. In most cases you will implement the GET and/or the POST method.

For further readings on servlets and how we use them you may have a look at the following documentation entries.

- *New WebServer project* : Have a look [here](<https://github.com/techdivision/TechDivision_WebServer>)

- *A separate ServletEngine* : Is documented [here](https://github.com/techdivision/TechDivision_ServletEngine)

- *How to write Servlets* : Have a look [here](<https://github.com/techdivision/TechDivision_ApplicationServerProject>)