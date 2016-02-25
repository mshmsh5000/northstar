# Profile Endpoints

## Get Authenticated User's Profile
Get profile data for the [currently authenticated user](../authentication.md). This must be done using an API key with `user` scope.

```
GET /profile
```

**Example Request:**  
```sh
curl -X GET \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -H "Accept: application/json"
  https://northstar.dosomething.org/v1/profile
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
        "updated_at": "2015-05-19 19:03:21",
        "created_at": "2015-05-19 15:47:08"
    }
}
```

## Update Authenticated User's Profile
Update the profile data for the [currently authenticated user](../authentication.md). This must be done using an API key with `user` scope.

```
POST /profile
```

**Parameters:**   
```js
// Content-Type: application/json

{
  /* Email address */
  email: String

  /* Mobile phone number */
  mobile: String

  /* Drupal ID */
  drupal_id: String

  /* Athletes Gone Good ID */
  agg_id: Number

  /* Celebs Gone Good ID */
  cgg_id: Number

  /* Mailing address */
  addr_street1: String
  addr_street2: String
  addr_city: String
  addr_state: String
  addr_zip: String

  /* Country */
  country: String

  /* Date of birth */
  birthdate: Date
 
  /* First name */
  first_name: String

  /* Last name */
  last_name: String

  /* Installation ID from Parse for push notifications */
  parse_installation_ids: String

  /* And more... */
  race: String
  religion: String
  college_name: String
  degree_type: String
  major_name: String
  hs_gradyear: String
  hs_name: String
  interests: String
  sat_math: Int
  sat_verbal: Int
  sat_writing: Int
  source: String
}
```

**Example Request:**

```sh
curl -X POST \
  -H "X-DS-REST-API-Key: ${REST_API_KEY}" \
  -d '{"first_name": "New First name"}' \
  https://northstar.dosomething.org/v1/profile
```

**Example Response:**

```js
202 Accepted

{
    "data": {
        "id": "5430e850dt8hbc541c37tt3d",
        "first_name": "New First Name",
        // the rest of the profile...
    }
}
```

## Get Authenticated User's Signups
Hmm, documentation is coming soon! :construction:

## Get Authenticated User's Reportbacks
Hmm, documentation is coming soon! :construction:
