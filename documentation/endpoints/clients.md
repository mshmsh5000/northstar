# Client Endpoints

## Retrieve All Clients
Retrieves all valid OAuth clients. This requires either the `admin` scope, or `role:admin` with an admin user.

```
GET /v2/clients
```

**Example Request:**
```sh
curl -X GET \
  -H "Authorization: ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v2/clients
```

**Example Response:**
```js
// 200 OK

{
  "data": [
    {
      "title": "Trusted Test Client",
      "description": "A trusted example client.",
      "client_id": "trusted-test-client",
      "client_secret": "Mq3kXQZldCXmDKs2XxJvC2qsuzfUusdQ",
      "scope": [
        "admin",
        "user",
        "role:admin"
      ],
      "refresh_tokens": 28,
      "updated_at": "2016-07-07T15:46:21+0000",
      "created_at": "2016-07-06T18:26:04+0000"
    },
    {
      "title": "Untrusted Test Client",
      "description": "A untrusted example client.",
      "client_id": "untrusted-test-client",
      "client_secret": "qZRBJiOXsE657sUuvYcRzHAMNjHUdjkH",
      "scope": [
        "user"
      ],
      "refresh_tokens": 16,
      "updated_at": "2016-07-06T18:26:04+0000",
      "created_at": "2016-07-06T18:26:04+0000"
    }
  ],
  "meta": {
    "pagination": {
      "total": 2,
      "count": 2,
      "per_page": 20,
      "current_page": 1,
      "total_pages": 1,
      "links": []
    }
  }
}
```

## Create a Client
Creates a new OAuth client. This requires either the `admin` scope, or `role:admin` with an admin user.

```
POST /v2/clients
```

**Parameters:**

```js
{
  /* The application's title. */
  title: String

  /* (optional) The description for this application. */
  description: String
  
  /* Application ID for the new key */
  client_id: String

  /* Whitelisted client scope(s) */
  scope: Array
}
```

**Example Request:**
```sh
curl -X POST \
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title": "Test Application", "description: "An example app.", "client_id": "test-application", "scope": ["user"]}' \
  https://northstar.dosomething.org/v2/clients
```


**Example Responses:**
```js
// 200 OK

{
  "data": {
    "title": "Test Application",
    "description": "An example app.",
    "client_id": "test-application",
    "client_secret": "1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8",
    "scope": [
      "user"
    ],
    "refresh_tokens": 0,
    "updated_at": "2015-05-19 17:10:37",
    "created_at": "2015-05-19 17:10:37",
  }
}
```

## Retrieve a Client
View details for an OAuth client. This requires either the `admin` scope, or `role:admin` with an admin user.

```
GET /v2/clients/:client_id
```

**Example Request:**
```sh
curl -X GET\
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  https://northstar.dosomething.org/v2/clients/test-application
```


**Example Responses:**
```js
// 200 OK

{
  "data": {
    "title": "Test Application",
    "description": "An example app.",
    "client_id": "testapplication",
    "client_secret": "1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8",
    "scope": [
      "admin",
      "user"
    ],
    "refresh_tokens": 32,
    "updated_at": "2015-05-19 17:10:37",
    "created_at": "2015-05-19 17:10:37",
  }
}
```

## Update a Client
Updates an existing OAuth client's ID or scope(s). This requires either the `admin` scope, or `role:admin` with an admin user.

```
PUT /v2/clients/:client_id
```

**Parameters:**

```js
{
  /* (optional) Change this application's title. */
  title: String

  /* (optional) Change the description for this application. */
  description: String

  /* (optional) Change the whitelisted scope(s) for this application. */
  scope: Array
}
```

**Example Request:**
```sh
curl -X PUT \
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"scope": ["admin", "user"]}' \
  https://northstar.dosomething.org/v2/clients/test-application
```


**Example Responses:**
```js
// 200 OK

{
  "data": {
    "title": "Test Application",
    "description": "An example app.",
    "client_id": "testapplication",
    "client_secret": "1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8",
    "scope": [
      "admin",
      "user"
    ],
    "refresh_tokens": 32,
    "updated_at": "2015-05-19 17:10:37",
    "created_at": "2015-05-19 17:10:37",
  }
}
```


## Delete a Client 
Delete an OAuth client. This will invalidate all refresh tokens that have been created by that client. This requires
either the `admin` scope, or `role:admin` with an admin user.

```
DELETE /v2/clients/:client_id
```


**Example Request:**
```sh
curl -X DELETE \
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  https://northstar.dosomething.org/v2/clients/test-application
```


**Example Responses:**
```js
// 200 OK

{
  "success": {
    "code": 200,
    "message": "Deleted client."
  }
}
```

## Retrieve All Client Scopes
Retrieves all valid scopes and a short description of each.

```
GET /v2/scopes
```

**Example Request:**
```sh
curl -X GET https://northstar.dosomething.org/v2/scopes
```

**Example Response:**
```js
// 200 OK

{
  "role:admin": {
    "description": "Allows this client to act as an administrator if the user has that role."
  },
  "role:staff": {
    "description": "Allows this client to act as a staff member if the user has that role."
  },
  "admin": {
    "description": "Grant administrative privileges to this token, whether or not the user has the admin role.",
    "warning": true
  },
  "user": {
    "description": "Allows actions to be made on a user's behalf."
  }
}
```


## Retrieve Public Key
Retrieves the public key which can be used to verify issued JWT access tokens. This endpoint requires either the `admin` scope,
or `role:admin` with an admin user.

```
GET /v2/key
```

**Example Request:**
```sh
curl -X GET https://northstar.dosomething.org/v2/key \
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
```

**Example Response:**
```js
// 200 OK

{
  "algorithm": "RS256",
  "issuer": "http://northstar.dosomething.org",
  "public_key": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----\n"
}
```

