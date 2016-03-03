# User Endpoints
## Retrieve All Users
Get data for all users in a paginated format.

```
GET /users
```

**Additional Query Parameters:**

- `limit`: Set the number of results you want to receive per page. Default is 20.
- `page`: Set the page number to get results from.
- `filter`: Filter the collection to include _only_ users matching the following comma-separated values. For example, `/v1/users?filter[drupal_id]=10123,10124,10125` would return users whose Drupal ID is either 10123, 10124, or 10125. You can filter by one or more indexed fields.
- `search`: Search the collection for users with fields whose value match the query. For example, `/v1/users?search[_id]=test@example.com&search[email]=test@example.org` would return all users with either an ID or email address matching `test@example.org`. You can search by one or more indexed fields. This is limited to admin-scoped API keys!

**Example Request:**

```sh
curl -X GET \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  https://northstar.dosomething.org/v1/users?limit=15&page=1
```

**Example Response:**
```js
// 200 OK

{
    "data": [
        {
            "id": "5480c950bffebc651c8b456f",
            "email": "test@dosomething.org",
            // ...the rest of the user data...
        },
        // etc...
    ],
    "meta": {
        "pagination": [
            "total": 65,
            "count": 20,
            "per_page": 15,
            "current_page": 1,
            "total_pages": 5,
            "links": {            
                "next": "https://northstar.dosomething.org/v1/users?page=2",
            }
        ]
    }
}
```
**Example Request:**

```sh
curl -X GET \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
    https://northstar.dosomething.org/v1/users?filter[drupal_id]=10010
```

**Example Response:**
```js
// 200 OK

{
    "data": [
        {
            "id": "5480c950bffebc651c8b456f",
            "drupal_id": "10010",
            // ...the rest of the user data...
        }
    ],
    "meta": {
        "pagination": [
            "total": 1,
            "count": 1,
            "per_page": 20,
            "current_page": 1,
            "total_pages": 1,
            "links": {}
        ]
    }
}
```

## Create a User
Create a new user. This is performed as an "[upsert](https://docs.mongodb.org/v2.6/reference/glossary/#term-upsert)", so
if a user with a matching identifier is found, new/changed properties will be merged into the existing document. This means making the same request multiple times will _not_ create duplicate accounts.

This endpoint requires an API key with `admin` scope. For registering a user, consider using the
[`auth/register`](#register-user) endpoint, which will also create and return a new authentication token.

```
POST /users
```

**Body Parameters:**

Either a mobile number or email is required.
```js
// Content-Type: application/json

{
  // Required if 'mobile' is not provided
  email: String

  // Required if 'email' is not provided
  mobile: String

  // Optional, but required for user to be able to log in!
  password: String

  // Optional:
  birthdate: Date
  first_name: String
  last_name: String
  addr_street1: String
  addr_street2: String
  addr_city: String
  addr_state: String
  addr_zip: String
  country: String // two character country code
  language: String
  agg_id: Number
  cgg_id: Number
  drupal_id: String
  parse_installation_ids: String // CSV values or array will be appended to existing interests
  interests: String, Array // CSV values or array will be appended to existing interests
  source: String // Immutable (can only be set if existing value is `null`)
  
  // Hidden fields (optional):
  race: String
  religion: String
  college_name: String
  degree_type: String
  major_name: String
  hs_gradyear: String
  hs_name: String
  sat_math: Number
  sat_verbal: Number
  sat_writing: Number
}
```

**Additional Query Parameters:**

- `create_drupal_user`: Will send a request to create a drupal user in the main DS app.

**Example Request:**  

```sh
curl -X POST \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "test@example.com", "password": "test123", "birthdate": "10/29/1990", "first_name": "test_fname", "interests": "hockeys,kickballs"}' \
  https://northstar.dosomething.org/v1/users?create_drupal_user=1
```

**Example Response:**

```js
// 200 Okay

{
    "data": {
        "id": "555b9225bffebc31068b4567",
        "_id": "555b9225bffebc31068b4567",
        "email": "test",
        "birthdate": "10/29/1990",
        "first_name": "test_fname",
        "interests": [
            "hockeys",
            "kickballs"
        ],
        "updated_at": "2016-02-25T19:33:24+0000",
        "created_at": "2016-02-25T18:33:24+0000"
    }
}
```

## Retrieve a User
Get profile data for a specific user. This can be retrieved with either the user's Northstar ID (which is automatically
generated when a new database record is created), a mobile phone number, an email address, or the user's Drupal ID.

```
GET /users/_id/<user_id>
GET /users/mobile/<mobile>
GET /users/email/<email>
GET /users/drupal_id/<drupal_id>
```

**Example Request:**  
```sh
curl -X GET \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v1/users/mobile/5555555555
```

**Example Response:**  
```js
// 200 OK

{
    "data": {
        "_id": "5430e850dt8hbc541c37tt3d",
        "id": "5430e850dt8hbc541c37tt3d",
        "email": "test@example.com",
        "mobile": "5555555555",
        "drupal_id": "123456",
        "addr_street1": "123",
        "addr_street2": "456",
        "addr_city": "Paris",
        "addr_state": "Florida",
        "addr_zip": "555555",
        "country": "US",
        "birthdate": "12/17/91",
        "first_name": "First",
        "last_name": "Last",
        "updated_at": "2016-02-25T19:33:24+0000",
        "created_at": "2016-02-25T19:33:24+0000"
    }
}
```

## Update a User
Update a user resource. This can be retrieved with the user's Northstar ID or the source ID (`drupal_id`). This endpoint requires an API key with `admin` scope.

```
PUT /users/_id/<user_id>
PUT /users/drupal_id/<drupal_id>
```

**Parameters:**   
POST /users
```

**Body Parameters:**

Either a mobile number or email is required.
```js
// Content-Type: application/json

{
  email: String
  mobile: String
  password: String
  birthdate: Date
  first_name: String
  last_name: String
  addr_street1: String
  addr_street2: String
  addr_city: String
  addr_state: String
  addr_zip: String
  country: String // two character country code
  language: String
  agg_id: Number
  cgg_id: Number
  drupal_id: String
  parse_installation_ids: String // CSV values or array will be appended to existing interests
  interests: String, Array // CSV values or array will be appended to existing interests
  source: String // Immutable (can only be set if existing value is `null`)
  
  // Hidden fields (optional):
  race: String
  religion: String
  college_name: String
  degree_type: String
  major_name: String
  hs_gradyear: String
  hs_name: String
  sat_math: Number
  sat_verbal: Number
  sat_writing: Number
}
```

**Example Request:**

```sh
curl -X PUT \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -d '{"first_name": "New First name"}' \
  https://northstar.dosomething.org/v1/_id/5430e850dt8hbc541c37tt3d
```

**Example Response:**

```js
200 Okay

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "first_name": "New First Name",
        // the rest of the profile...
    }
}
```

## Delete a User
Destroy a user resource. The  `user_id` property of the user to delete must be provided in the URL path, and refers to the user's Northstar ID. This endpoint requires an API key with `admin` scope.

```
DELETE /users/:user_id
```

**Example Request:**
```sh
curl -X DELETE \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  https://northstar.dosomething.org/v1/users/555b9ca8bffebc30068b456e
```

**Example Response:**
```js
// 200 OK

{
    "success": {
        "message": "No Content."
    }
}
```

## Set User Avatar
Save an avatar to the user's Northstar profile. Accepts a file or Base64 string in the data request. This will return
the updated User profile document, with a `photo` attribute pointing to the newly created image.

```
POST /users/:user_id/avatar
```

**Parameters:**
```js
// Content-Type: multipart/form-data --or-- application/json
// Accept: application/json

{
  /* Required */
  photo: File or String
}
```

**Example Request:**
```sh
curl -X POST \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \ 
  -H "Content-Type: multipart-form-data: \
  -H "Accept: application/json" \
  -d '{"photo": "profile_pic.jpeg"}' \
  https://northstar.dosomething.org/v1/users/{id}/avatar
```

**Example Response:**
```js
// 200 OK

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "photo": "https://avatar.dosomething.org/uploads/avatars/55566327bffebc0b3e8b45a5-1456498835.jpeg"
        // the rest of the user object...
        "updated_at": "2016-02-25T18:33:25+0000"
    }
}
```
