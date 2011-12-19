# Hybrid 
A set of class that extends the functionality of FuelPHP without affecting the standard workflow when the application doesn't actually utilize Hybrid feature.

## Key Features

* ACL class support unlimited roles and resources, type using either `deny`, `view`, `create`, `edit`, `delete` or `all` access type.
* Auth class support normal user authentication or authentication using OAuth, OAuth2 or OpenID without any hassle, code inspired by [NinjAuth Package](https://github.com/happyninjas/fuel-ninjauth).
* 6 predefined Controller class for you to choose from, you can choose from either a Quick and Dirty, Template, Restful or Hybrid (Template + Restful) support.
* Chart collection class using [Google Visualization Library](http://code.google.com/apis/chart/).
* Pagination are now easier than ever, you can choose the default URI segment, querystring or custom route with a simple configuration, the code will handle the rest.
* Request class support Restful + HMVC structure, a cool way to avoid use of cURL in your cool FuelPHP app.
* Parser class support text filtering to either Markdown, Textile or BBCode.
* Swiftmail class a good alternative for SwiftMailer lover, restructure to cope with FuelPHP coding standard.
* Template class give you the option to follow **MVC** or move **V** into a full theme-like experience inside your `public` folder complete with `assets` subfolder (if you choose to).

## Other Features

* Curl class
* Currency class
* Tabs class

## Contributors

* Mior Muhammad Zaki 
* Arif Azraai
* Ignacio Muñoz Fernandez

## Documentation

Hybrid for FuelPHP documentation is available at <http://codenitive.github.com/fuel-hybrid> and included on each download archive.