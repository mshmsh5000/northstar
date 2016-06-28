# Key Endpoints

## Retrieve All API Keys
Retrieves all valid application IDs and API keys. This must be done using an API key with `admin` scope.

```
GET /v1/keys
```

**Example Request:**
```sh
curl -X GET \
  -H "Authorization: ${ACCESS_TOKEN}" \
  https://northstar.dosomething.org/v1/keys
```

**Example Response:**
```js
// 200 OK

{
  "data": [
    {
      "app_id": "appid1",
      "api_key": "apikey1",
      "scope": [
        "admin",
        "user"
      ],
      "updated_at": "2015-05-19 15:47:08",
      "created_at": "2015-05-19 15:47:08"
    },
    {
      "app_id": "appid2",
      "api_key": "apikey2",
      "scope": [
        "user"
      ],
      "updated_at": "2015-05-19 15:47:08",
      "created_at": "2015-05-19 15:47:08"
    },
    {
      "app_id": "appid3",
      "api_key": "apikey3",
      "scope": [],
      "updated_at": "2015-05-19 17:10:37",
      "created_at": "2015-05-19 17:10:37"
    }
  ]
}
```

## Create an API Key
Creates an API key. This must be done using an API key with `admin` scope.

```
POST /v1/keys
```

**Parameters:**

```js
{
  /* Application name registering for the new key */
  app_id: String

  /* API key scope(s) */
  scope: Array
}
```

**Example Request:**
```sh
curl -X POST \
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"app_id": "test application"}' \
  https://northstar.dosomething.org/v1/keys
```


**Example Responses:**
```js
// 200 OK

{
  "data": {
    "app_id": "testapplication",
    "api_key": "1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8",
    "scope": [],
    "updated_at": "2015-05-19 17:10:37",
    "created_at": "2015-05-19 17:10:37",
  }
}
```

## Retrieve an API Key
View an API key. This must be done using an API key with `admin` scope.

```
GET /v1/keys/:api_key
```

**Example Request:**
```sh
curl -X GET\
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  https://northstar.dosomething.org/v1/keys/1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8
```


**Example Responses:**
```js
// 200 OK

{
  "data": {
    "app_id": "testapplication",
    "api_key": "1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8",
    "scope": [
      "admin",
      "user"
    ],
    "updated_at": "2015-05-19 17:10:37",
    "created_at": "2015-05-19 17:10:37",
  }
}
```

## Update an API Key
Updates an existing API key's ID or scope(s). This must be done using an API key with `admin` scope.

```
PUT /v1/keys/:api_key
```

**Parameters:**

```js
{
  /* (optional) New application name */
  app_id: String

  /* (optional) API key scope(s) */
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
  https://northstar.dosomething.org/v1/keys/1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8
```


**Example Responses:**
```js
// 200 OK

{
  "data": {
    "app_id": "testapplication",
    "api_key": "1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8",
    "scope": [
      "admin",
      "user"
    ],
    "updated_at": "2015-05-19 17:10:37",
    "created_at": "2015-05-19 17:10:37",
  }
}
```


## Delete an API Key
Deletes an API key. This must be done using an API key with `admin` scope.

```
DELETE /v1/keys/:api_key
```


**Example Request:**
```sh
curl -X DELETE \
  -H "Authorization: ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  https://northstar.dosomething.org/v1/keys/1laEQhhKtQEaPK0qpESdXHm2EbdLu5sRIRLcRtF8
```


**Example Responses:**
```js
// 200 OK

{
  "success": {
    "code": 200,
    "message": "Deleted key."
  }
}
```

## Retrieve All API Key Scopes
Retrieves all valid API scopes and a short description of each.

```
GET /v1/scopes
```

**Example Request:**
```sh
curl -X GET https://northstar.dosomething.org/v1/scopes
```

**Example Response:**
```js
// 200 OK

{
  "admin": "Allows \"administrative\" actions that should not be user-accessible, like deleting user records.",
  "user": "Allows actions to be made on a user's behalf."
}
```


