# Authentication Endpoints

## Create Token

This will verify a user's credentials and create an authentication token, which can be used to sign future requests on the user's behalf. If invalid credentials are provided, this endpoint will return a `401 Unauthorized` error.

```
POST /auth/token
```

**Parameters:**

In addition to the password, either mobile number or email is required.
```js
// Content-Type: application/json
 
{
  /* Shortcut for either 'email' or 'mobile', inferred by format */
  username: String,

  /* Required if 'username' or 'mobile' are not provided */
  email: String,

  /* Required if 'username' or 'email' are not provided */
  mobile: String,

  /* Required */
  password: String,
}
```

**Example Request:**
```
curl -X POST \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username": "test@example.com", "password": "${PASSWORD}"}' \
  https://northstar.dosomething.org/v1/auth/token
```

**Example Response:**
```js
// 200 OK

{
  "data": {
    "key": "FOf9C0lkY3wAQBCwdCqxPJrCX3XZDQ87",
    "user": {
      "data": {
         "id": "5430e850dt8hbc541c37tt3d",
         "email": "test@example.com",
         "mobile": "5555555555",
         "drupal_id": "123456",
         "birthdate": "12/17/91",
         "first_name": "First",
         "last_name": "Last",
      }
    }
  }
}
```

## Verify Credentials

This will verify the given credentials _without_ creating a new authentication token. This is useful for applications which manage their own sessions. If invalid credentials are provided, this endpoint will return a `401 Unauthorized` error.

```
POST /auth/verify
```

**Parameters:**

In addition to the password, either mobile number or email is required.
```js
// Content-Type: application/json
 
{
  /* Shortcut for either 'email' or 'mobile', inferred by format */
  username: String,

  /* Required if 'username' or 'mobile' are not provided */
  email: String,

  /* Required if 'username' or 'email' are not provided */
  mobile: String,

  /* Required */
  password: String,
}
```

**Example Request:**
```
curl -X POST \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com", "password": "${PASSWORD}"}' \
  https://northstar.dosomething.org/v1/auth/verify
```

**Example Response:**
```js
// 200 OK

{
  "data": {
    "id": "5430e850dt8hbc541c37tt3d",
    "email": "test@example.com",
    "mobile": "5555555555",
    "drupal_id": "123456",
    "birthdate": "12/17/91",
    "first_name": "First",
    "last_name": "Last",
  }
}
```


## Invalidate Token

```
POST /auth/invalidate
```

The `Authorization` header must include the authorization token received at login.

**Example Request:**
```
curl -X POST \
  -H "X-DS-Application-Id: ${APPLICATION_ID}" \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Authorization: Bearer ${AUTHENTICATION_TOKEN}"
  https://northstar.dosomething.org/v1/logout
```
**Additional Query Parameters:**

- `parse_installation_ids`: will remove whichever provided parse installation IDs match up with those IDs stored on the user profile. 

**Example Response:**
```js
// 200 OK

{
  "success": {
    "code": 200,
    "message": "User logged out successfully."
  }
}
```

## Register User

This will register a new user account and create an authentication token, which can be used to sign future requests on the user's behalf.

```
POST /auth/register
```

**Parameters:**

In addition to the password, either mobile number or email is required.
```js
// Content-Type: application/json
 
{
  /* Required if 'mobile' is not provided */
  email: String,

  /* Required if 'email' is not provided */
  mobile: String,

  /* Required */
  password: String,

  /* ...and optionally, any other user fields. */
}
```

**Example Request:**
```
curl -X POST \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com", "password": "${PASSWORD}"}' \
  https://northstar.dosomething.org/v1/auth/register
```

**Example Response:**
```js
// 200 OK

{
  "data": {
    "key": "FOf9C0lkY3wAQBCwdCqxPJrCX3XZDQ87",
    "user": {
      "data": {
         "id": "5430e850dt8hbc541c37tt3d",
         "email": "test@example.com",
         "mobile": "5555555555",
      }
    }
  }
}
```
