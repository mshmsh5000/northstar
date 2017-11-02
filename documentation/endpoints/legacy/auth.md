# Authentication Endpoints

> :warning: The [OAuth2 authentication endpoints](../auth.md) are the preferred way to authenticate clients
> across all services. These legacy endpoints are deprecated and will be removed in the future.

## Verify Credentials

This will verify the given credentials _without_ creating a new authentication token. This is useful for applications which manage their own sessions. If invalid credentials are provided, this endpoint will return a `401 Unauthorized` error.

```
POST /v1/auth/verify
```

**Request Parameters:**

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
