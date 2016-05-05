# Authentication

> :construction: This is under construction and not currently enabled on production. Stay tuned!

We handle authentication using __OAuth 2__, an [open standard](https://tools.ietf.org/html/rfc6749) for authorization. OAuth allows us to issue
access tokens (so a user's credentials don't need to be sent with every request) and refresh tokens (so that a user's credentials do not need to be stored on a device).
It also allows us to restrict abilities of different clients based on scopes (so that, for example, internal tools like [Aurora](https://aurora.dosomething.org/auth/login)
can delete users, but an "external" application like the mobile app cannot).

### Clients
When requesting an authentication token, credentials for a valid **client application** must be included. This includes a client ID
(for example, `phoenix` or `letsdothis-ios`) and a client secret, which is like a long "password" for that application.

### Scopes
Authentication tokens are granted **scopes** to limit their privileges. This allows us to minimize damage if a token is
compromised, and also limit the abilities of "untrusted" lcients that operate over a public network like the [mobile app](https://app.dosomething.org), and
limit the amount of damage that can be done if a client is compromised.

While you may request any scopes when creating a token, each client has a list of allowed scopes and so scopes that are
not "whitelisted" for the given client will be refused. So, for example, a malicious user with the mobile app client
credentials could never request the ability to delete users.

The allowed scopes for each client are listed on Aurora. A machine-friendly list of scopes and their descriptions can be
retrieved from the public [`scopes`](endpoints/keys.md#retrieving-all-api-key-scopes) endpoint.

### Access Tokens
The [Password Grant](endpoints/oauth.md#create-token-password-grant) may be used to request an **access token** so that requests can be
made on behalf of a particular user.

We authenticate requests to our APIs using [JSON Web Tokens](https://jwt.io), another [open standard](https://tools.ietf.org/html/rfc7519)
that allows us to issue cryptographically signed tokens. Each token includes information like the user's ID,
granted scopes, and the token expiration date. Because the tokens are signed, this data can't be tampered with without invalidating
the signature, and any (trusted) service can validate a token using Northstar's public key _without_ making an HTTP request!

If you're curious about more of the nerdy details on JWTs, check out the [official introduction](https://jwt.io/introduction/).

### Refresh Tokens
Access tokens are short-lived and expire after an hour. Since that's not a very long time, clients are also issued
**refresh tokens** which can be used (once!) to create another access token. Refresh tokens _never_ expire, but can be revoked
manually - for example, if a user "removes" an application or logs out from their account.

Once a refresh token is used to create a new access token through the [Refresh Token Grant](endpoints/oauth.md#create-token-refresh-token-grant),
a _new_ access token and refresh token are returned & the old refresh token cannot be used again.

### "Computer" Authentication
Some services don't authenticate on behalf of a user (for example, an internal batch-processing service). These clients can use the
[Client Credentials Grant](endpoints/oauth.md#create-token-client-credentials-grant) to request a signed authentication token
that securely identifies that particular application. Since user credentials are not involved, this does _not_ create a refresh
token.

### Making Authenticated Requests
When making requests to Northstar, the token should be be provided as the `Authorization` header of a request:
```sh
GET /v1/users
Authorization: Bearer xxxxxxx
```