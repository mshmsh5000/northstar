# Reportback Endpoints

## Retrieve All Reportbacks
Get paginated reportbacks, optionally filtered by campaign or user.

```
GET /reportbacks?user=:northstar_id,:northstar_id&campaigns=:campaign_id,:campaign_id
```

This is a lightweight proxy for the `/reportbacks` Phoenix endpoint. If a user ID is specified, it will be transformed into 
the appropriate `drupal_id` before the request is forwarded to Phoenix.

For more implementation details, see the relevant [Phoenix API documentation](https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-reportback-collection).

## Retrieve a Reportback
Get details for a specific reportback.

```
GET /reportbacks/:reportback_id
```

This is a lightweight proxy for the `/reportbacks/:reportback_id` Phoenix endpoint.

For more details, see the relevant [Phoenix API documentation](https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-specific-reportback).

## Create a Reportback
Create a new reportback (or update an existing reportback, if one exists for the given campaign & authenticated user).
This requires an authentication token & an API key with the `user` scope.

```
POST /reportbacks
```

This is a lightweight proxy for the `/campaigns/:nid/reportback` Phoenix endpoint, which will use the currently authenticated
user token. The campaign ID (e.g. `:nid` from the underlying Phoenix endpoint) should be provided as a `campaign_id` field in
the body of the request.

If the action is successful, the created reportback will be returned in the response.

For more details, see the relevant [Phoenix API documentation](https://github.com/DoSomething/phoenix/wiki/API#campaign-reportback).

