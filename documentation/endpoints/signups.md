# Signup Endpoints

## Retrieve All Signups
Get paginated signups, optionally filtered by campaign or user.

```
GET /signups?user=:northstar_id,:northstar_id&campaigns=:campaign_id,:campaign_id
```

This is a lightweight proxy for the `/signups` Phoenix endpoint. If a user ID is specified, it will be transformed into 
the appropriate `drupal_id` before the request is forwarded to Phoenix.

For more details, see the relevant [Phoenix API documentation](https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-signup-collection).


## Retrieve a Signup
Get details for a specific signup.

```
GET /signups/:signup_id
```

This is a lightweight proxy for the `/signups/:signup_id` Phoenix endpoint.

For more details, see the relevant [Phoenix API documentation](https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-specific-signup).

## Create a Signup
Create a new signup (or return an existing signup, if one exists for the given campaign & authenticated user).
This requires an authentication token & an API key with the `user` scope.

```
POST /signups
```

This is a lightweight proxy for the `/campaigns/:nid/signup` Phoenix endpoint, which will use the currently authenticated
user token. The campaign ID (e.g. `:nid` from the underlying Phoenix endpoint) should be provided as a `campaign_id` field in
the body of the request.

If the action is successful or a signup already exists, the signup will be returned in the response.

For more details, see the relevant [Phoenix API documentation](https://github.com/DoSomething/phoenix/wiki/API#campaign-signup).

