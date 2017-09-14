# Northstar [![wercker status](https://app.wercker.com/status/109bce734be9a06703562876265f5bd9/s/dev "wercker status")](https://app.wercker.com/project/byKey/109bce734be9a06703562876265f5bd9) [![StyleCI](https://styleci.io/repos/26884886/shield?style=flat-rounded)](https://styleci.io/repos/26884886)

This is __Northstar__, the DoSomething.org user & identity service. It's our single "source of truth" for member information.
Northstar is built using [Laravel 5.3](https://laravel.com/docs/5.3) and [MongoDB](https://www.mongodb.com).

### Getting Started

Check out the [API Documentation](https://github.com/DoSomething/northstar/blob/dev/documentation/README.md) to start using
Northstar! :sparkles:

### Contributing

Fork and clone this repository, and [add it to your Homestead](https://github.com/DoSomething/communal-docs/blob/master/Homestead/readme.md).

```sh
# Make public & private RSA keys:
$ openssl genrsa -out storage/keys/private.key 1024
$ openssl rsa -in storage/keys/private.key -pubout -out storage/keys/public.key
$ chmod 600 storage/keys/{public,private}.key

# Install dependencies:
$ composer install && npm install
    
# Copy the default environment variables:
$ cp .env.example .env

# Make app key & run database migrations:
$ php artisan key:generate
$ php artisan migrate

# And finally, build the frontend assets:
$ npm run build
```

You can seed the database with test data:

    $ php artisan db:seed

You may run unit tests locally using PHPUnit:

    $ vendor/bin/phpunit
    
We follow [Laravel's code style](http://laravel.com/docs/5.3/contributions#coding-style) and automatically
lint all pull requests with [StyleCI](https://styleci.io/repos/26884886). Be sure to configure
[EditorConfig](http://editorconfig.org) to ensure you have proper indentation settings.

Consider [writing a test case](http://laravel.com/docs/5.3/testing) when adding or changing a feature.
Most steps you would take when manually testing your code can be automated, which makes it easier for
yourself & others to review your code and ensures we don't accidentally break something later on!

### Security Vulnerabilities
We take security very seriously. Any vulnerabilities in Northstar should be reported to [security@dosomething.org](mailto:security@dosomething.org),
and will be promptly addressed. Thank you for taking the time to responsibly disclose any issues you find.

### License
&copy;2017 DoSomething.org. Northstar is free software, and may be redistributed under the terms specified
in the [LICENSE](https://github.com/DoSomething/northstar/blob/dev/LICENSE) file. The name and logo for
DoSomething.org are trademarks of Do Something, Inc and may not be used without permission.
