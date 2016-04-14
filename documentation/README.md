# Northstar

This is __Northstar__, the DoSomething.org user & activity API. It's our single "source of truth" for member
information. Northstar is currently only available for use by registered DoSomething.org services.

## Authentication
See [Authentication](authentication.md) for details on authorizing your requests.
<br>

## Endpoints 
#### Authentication
Endpoint                  | Functionality                                                | Required Scope
------------------------- | ------------------------------------------------------------ | --------------
`POST /auth/token`        | [Create Auth Token](endpoints/auth.md#create-token)          | `user`
`POST /auth/verify`       | [Verify Credentials](endpoints/auth.md#verify-credentials)   | `user`
`POST /auth/invalidate`   | [Invalidate Auth Token](endpoints/auth.md#invalidate-token)  | `user`
`POST /auth/register`     | [Register User](endpoints/auth.md#register-user)             | `user`

> :construction: New [OAuth endpoints](endpoints/oauth.md) are under construction and will be the preferred way to authenticate clients
> across all services once they're shipped. Stay tuned!

#### Users
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET /users`                             | [Retrieve All Users](endpoints/users.md#retrieve-all-users) |
`POST /users`                            | [Create a User](endpoints/users.md#create-a-user) | `admin`
`GET /users/:term/:identifier`           | [Retrieve a User](endpoints/users.md#retrieve-a-user) 
`PUT /users/:term/:id`                   | [Update a User](endpoints/users.md#update-a-user) | `admin`
`DELETE /users/:user_id`                 | [Delete a User](endpoints/users.md#delete-a-user) | `admin`
`POST /users/:user_id/avatar`            | [Set User Avatar](endpoints/users.md#set-user-avatar) | `user`

#### Profile
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET /profile`                           | [Get Authenticated User's Profile](endpoints/profile.md#get-profile) | `user`
`POST /profile`                          | [Update Authenticated User's Profile](endpoints/profile.md#post-profile) | `user`
`GET /profile/signups`                   | [Get Authenticated User's Signups](endpoints/profile.md#get-authenticated-users-signups) | `user`
`GET /profile/reportbacks`               | [Get Authenticated User's Reportbacks](endpoints/profile.md#get-authenticated-user-reportbacks) | `user`

> __Note:__ The signups & reportbacks endpoints are lightweight proxies to their Phoenix equivalents.

#### Signups
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET /signups`                           | [Retrieve All Signups](endpoints/signups.md#retrieve-all-signups) |
`GET /signups/:signup_id`                | [Retrieve a Signup](endpoints/signups.md#retrieve-a-signup)  |
`POST /signups`                          | [Create a Signup](endpoints/signups.md#create-a-signup)      | `user`

> __Note:__ These endpoints are lightweight proxies to their Phoenix equivalents.

#### Reportbacks
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET /reportbacks`                       | [Retrieve All Reportbacks](endpoints/reportbacks.md#retrieve-all-reportbacks) |
`GET /reportbacks/:reportback_id`        | [Retrieve a Reportback](endpoints/reportbacks.md#retrieve-a-reportback) |
`POST /reportbacks`                      | [Create a Reportback](endpoints/reportbacks.md#create-a-reportback) | `user`

> __Note:__ These endpoints are lightweight proxies to their Phoenix equivalents.

#### Keys
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET /keys`                                  | [Retrieve All API Keys](endpoints/keys.md#retrieve-all-api-keys)  | `admin`
`POST /keys`                                 | [Create an API Key](endpoints/keys.md#create-an-api-key) | `admin`
`GET /keys/:api_key`                         | [Retrieve An API Key](endpoints/keys.md#retrieve-an-api-key) | `admin`
`PUT /keys/:api_key`                         | [Update An API Key](endpoints/keys.md#update-an-api-key) | `admin`
`DELETE /keys/:api_key`                      | [Delete an API Key](endpoints/keys.md#delete-an-api-key) | `admin`
`GET /scopes`                                | [Retrieve All API Key Scopes](endpoints/keys.md#retrieve-all-api-key-scopes) |

<br>
> :bulb: __Did you know?__ We also have a shared [Paw Collection](endpoints.paw) for testing these endpoints against your local environment.  

<br>

## Responses

We provide standard response formatting for all resource types using [Transformers](https://github.com/DoSomething/northstar/tree/dev/app/Http/Transformers).

### Resources
All resources are returned within a `data` property on the response. For endpoints that return a collection, this property
will be an array. Responses will include all properties available to the given client/user, specified as `null` if they
do not exist on that particular item.

Pagination & other meta-information may be provided in a `meta` key on the response. For example:

```js
{
    "data": [
      { /* ... */ },
      { /* ... */ },
    ],
    "meta": {
        "pagination": {
            "total": 60,
            "count": 20,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 3,
            "links": {
                "next": "http://northstar.dosomething.org/v1/users?page=2"
            }
    }
}
```

### Errors & Status Codes
Northstar returns standard HTTP status codes to indicate how a request turned out. In general, `2xx` codes are returned
on successful requests, `4xx` codes indicate an error in the request, and `5xx` error codes indicate an unexpected 
problem on the API end.

Code | Meaning
---- | -------
200  | __Okay__ – Everything is awesome.
401  | __Unauthorized__ – Your API key is wrong or not authorized for that action.
403  | __Forbidden__ – The authenticated user doesn't have the proper privileges.
404  | __Not Found__ – The specified resource could not be found.
418  | __I'm a teapot__ – The user [needs more caffeine](https://www.ietf.org/rfc/rfc2324.txt).
422  | __Unprocessable Entity__ – The request couldn't be completed due to validation errors. See the `error.fields` property on the response.
500  | __Internal Server Error__ – Northstar has encountered an internal error. Please [make a bug report](https://github.com/DoSomething/northstar/issues/new) with as much detail as possible about what led to the error!
503  | __Service Unavailable__ – Northstar is temporarily unavailable.

We return a standard `error` response on all errors that should provide a human-readable explanation
of the problem:

```js
{
    "error": {
        "code": 418,
        "message": "Tea. Earl Grey. Hot."
        
        // For 422 Unprocessable Entity, the "fields" object has specific validation errors:
        "fields": {
          "email": ["The email must be a valid email address."],
          "mobile": ["The mobile has already been taken."]
        }
    },
    // When running locally, debug information will be included in the response:
    "debug": {
        "file": "/home/vagrant/sites/northstar/app/Http/Controllers/UserController.php",
        "line": 115
    }
}
```


## Libraries
We have a [PHP API client](https://github.com/DoSomething/northstar-php) for simplified usage of the API in PHP clients.
