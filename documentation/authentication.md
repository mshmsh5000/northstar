# Authentication

> :construction: Updated [OAuth2 authentication](authentication_oauth.md) is under construction and will be the preferred way to authenticate clients across all services in the future. Stay tuned!

### API Keys
A valid API key must be included with any requests to Northstar, in the `X-DS-REST-API-Key` HTTP
header. API keys can be managed in [Aurora](https://aurora.dosomething.org/keys) or [Aurora QA](https://qa-aurora.dosomething.org/keys)

Sorry, there's no public API access... yet!

### Client Scopes
Clients are granted scopes to limit their privileges. This allows us to differentiate "trusted" clients
(internal applications like [Phoenix](https://www.dosomething.org) or [Aurora](https://aurora.dosomething.org))
from "untrusted" clients that operate over a public network like the [mobile app](https://app.dosomething.org), and
limit the amount of damage that can be done if a client is compromised.

Scope   | Description
------- | -----------
`user`  | Allows actions to be made on a user's behalf.
`admin` | Allows "administrative" actions that should not be user-accessible, like deleting user records.

A machine-friendly list of scopes and their descriptions can be retrieved from the public
[`scopes`](endpoints/keys.md#retrieving-all-api-key-scopes) endpoint.

### Authorization
The [authorization endpoints](endpoints/auth.md) may be used to request an authorization token so that requests can be
made on behalf of a particular user. Endpoints which act on a user's behalf are restricted to `user` scoped API keys.

> :construction: New [OAuth endpoints](endpoints/oauth.md) are under construction and will be the preferred way to authenticate clients
> across all services once they're shipped. Stay tuned!

Authorization can be provided as the `Authorization` header of a request, or optionally as a query parameter:
```sh
# preferred (Authorization header)
GET /v1/users
Authorization: Bearer xxxxxxx

# quick n' easy (as a query string)
GET /v1/users?token=xxxxxxx

# deprecated (don't use me!)
GET /v1/users
Session: xxxxx
```
