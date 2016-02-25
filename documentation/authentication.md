# Authentication

### API Keys
A valid API key must be included with any requests to Northstar, in the `X-DS-REST-API-Key` HTTP
header. API keys can be managed in [Aurora](https://aurora.dosomething.org/keys) or [Aurora QA](https://qa-aurora.dosomething.org/keys)

Sorry, there's no public API access... yet!

### Scopes
API keys are granted scopes to limit their privileges. This allows us to differentiate "trusted" clients
(internal applications like [Phoenix](https://www.dosomething.org) or [Aurora](https://aurora.dosomething.org))
from "untrusted" clients that operate over a public network like the [mobile app](https://app.dosomething.org).

Scope   | Description
------- | -----------
`user`  | Allows actions to be made on a user's behalf.
`admin` | Allows "administrative" actions that should not be user-accessible, like deleting user records.

A machine-friendly list of scopes and their descriptions can be retrieved from the public
[`scopes`](endpoints/keys.md#retrieving-all-api-key-scopes) endpoint.

### Authorization
The [authorization endpoints](endpoints/auth.md) may be used to request an authorization token so that requests
can be made on behalf of a particular user. Endpoints which act on a user's behalf are restricted to `user` scoped keys.
