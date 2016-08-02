# Northstar

This is __Northstar__, the DoSomething.org user & identity API. It's our single "source of truth" for member
information. Northstar is currently only available for use by registered DoSomething.org services.

## Authentication
See [Authentication](authentication.md) for details on authorizing your requests.
<br>

## Endpoints 

#### Authentication
Endpoint                  | Functionality                                                      | Required Scope
------------------------- | ------------------------------------------------------------------ | --------------
`POST v2/auth/token`      | [Create Auth Token (Password Grant)](endpoints/auth.md#create-token-password-grant) | 
`POST v2/auth/token`      | [Create Auth Token (Client Credentials Grant)](endpoints/auth.md#create-token-client-credentials-grant) | 
`POST v2/auth/token`      | [Create Auth Token (Refresh Token Grant)](endpoints/auth.md#create-token-refresh-token-grant) | 
`DELETE v2/auth/token`    | [Invalidate Auth Token](endpoints/auth.md#revoke-token) | 

> :memo: There's also the [legacy authentication endpoints](endpoints/legacy/auth.md), but those are deprecated so don't get too attached!

#### Users
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET v1/users`                               | [Retrieve All Users](endpoints/users.md#retrieve-all-users) | `role:admin` or `admin`
`POST v1/users`                              | [Create a User](endpoints/users.md#create-a-user) | `role:admin` or `admin`
`GET v1/users/:term/:identifier`             | [Retrieve a User](endpoints/users.md#retrieve-a-user) 
`PUT v1/users/:term/:id`                     | [Update a User](endpoints/users.md#update-a-user) | `role:admin` or `admin`
`DELETE v1/users/:user_id`                   | [Delete a User](endpoints/users.md#delete-a-user) | `role:admin` or `admin`
`POST v1/users/:user_id/avatar`              | [Set User Avatar](endpoints/users.md#set-user-avatar) | `user`

#### Profile
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET v1/profile`                             | [Get Authenticated User's Profile](endpoints/profile.md#get-profile) | `user`
`POST v1/profile`                            | [Update Authenticated User's Profile](endpoints/profile.md#post-profile) | `user`
`GET v1/profile/signups`                     | [Get Authenticated User's Signups](endpoints/profile.md#get-authenticated-users-signups) | `user`
`GET v1/profile/reportbacks`                 | [Get Authenticated User's Reportbacks](endpoints/profile.md#get-authenticated-user-reportbacks) | `user`

> __Note:__ The signups & reportbacks endpoints are lightweight proxies to their Phoenix equivalents.

#### Signups
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET v1/signups`                             | [Retrieve All Signups](endpoints/signups.md#retrieve-all-signups) |
`GET v1/signups/:signup_id`                  | [Retrieve a Signup](endpoints/signups.md#retrieve-a-signup)  |
`POST v1/signups`                            | [Create a Signup](endpoints/signups.md#create-a-signup)      | `user`

> __Note:__ These endpoints are lightweight proxies to their Phoenix equivalents.

#### Reportbacks
Endpoint                                     | Functionality                                            | Required Scope
-------------------------------------------- | -------------------------------------------------------- | --------------
`GET v1/reportbacks`                         | [Retrieve All Reportbacks](endpoints/reportbacks.md#retrieve-all-reportbacks) |
`GET v1/reportbacks/:reportback_id`          | [Retrieve a Reportback](endpoints/reportbacks.md#retrieve-a-reportback) |
`POST v1/reportbacks`                        | [Create a Reportback](endpoints/reportbacks.md#create-a-reportback) | `user`

> __Note:__ These endpoints are lightweight proxies to their Phoenix equivalents.

#### Clients
Endpoint                                     | Functionality                                                       | Required Scope
-------------------------------------------- | ------------------------------------------------------------------- | --------------
`GET v2/clients`                             | [Retrieve All Clients](endpoints/clients.md#retrieve-all-clients)   | `role:admin` or `admin`
`POST v2/clients`                            | [Create a Client](endpoints/clients.md#create-a-client)             | `role:admin` or `admin`
`GET v2/clients/:client_id`                  | [Retrieve a Client](endpoints/clients.md#retrieve-a-client)         | `role:admin` or `admin`
`PUT v2/clients/:client_id`                  | [Update a Client](endpoints/clients.md#update-a-client)             | `role:admin` or `admin`
`DELETE v2/clients/:client_id`               | [Delete a Client](endpoints/clients.md#delete-a-client)             | `role:admin` or `admin`
`GET v2/scopes`                              | [Retrieve All Client Scopes](endpoints/clients.md#retrieve-all-client-scopes) |
`GET v2/key`                                 | [Retrieve Public Key](endpoints/clients.md#retrieve-public-key)     | `role:admin` or `admin`

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
400  | __Bad Request__ – The request has incorrect syntax.
401  | __Unauthorized__ – The given credentials are invalid or you are not authorized to view that resource.
403  | __Forbidden__ – (For legacy authentication _only_.) The authenticated user doesn't have the proper privileges.
404  | __Not Found__ – The specified resource could not be found.
418  | __I'm a teapot__ – The user [needs more caffeine](https://www.ietf.org/rfc/rfc2324.txt).
422  | __Unprocessable Entity__ – The request couldn't be completed due to validation errors. See the `error.fields` property on the response.
429  | __Too Many Requests__ – The user/client has sent too many requests in the past hour. See [Rate Limiting](#rate-limiting).
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

OAuth authentication errors are formatted slightly differently (to conform to [the OAuth spec](https://tools.ietf.org/html/rfc6749#section-5.2)):

```js
{
  // A machine-readable error code.
  "error": "access_denied", "invalid_request", "invalid_client", "invalid_grant", "unauthorized_client", "unsupported_grant_type", "invalid_scope",
  
  // A human readable explanation of the problem.
  "message": "...",
  
  // Optionally, more specific details on the issue.
  "hint": "..."
}
```

## Rate Limiting
Unauthenticated clients may make up to 50 requests per hour from a single IP address. Authenticated users may make up
up to 5000 requests per hour. In special cases, a client with the `unlimited` scope may opt out of rate limiting.

The currently applied rate limit and remaining requests are returned as headers on each response:

Header                  | Description
----------------------- | -------------------------------------------------------------------------
`X-RateLimit-Limit`     |	The maximum number of requests that this client may make per hour.
`X-RateLimit-Remaining` |	The number of requests remaining of your provided limit.
`Retry-After`           | If rate limit is exceeded, this is the amount of time until you may make another request.


## Libraries
We have a [PHP API client](https://github.com/DoSomething/northstar-php) for simplified usage of the API in PHP clients.
