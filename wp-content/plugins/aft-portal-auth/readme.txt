=== AFT Portal Auth ===
Contributors: accountantsfortomorrow
Tags: rest api, exam portal, tutor lms
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Read-only credential and enrolment verification endpoint used by the AFT exam portal.

== Description ==

The AFT exam portal (separate application) uses this plugin to validate a
learner's WordPress credentials and to confirm an active Tutor LMS enrolment
before it creates a local portal profile.

Security model:

* Single shared secret, sent in the `X-AFT-Portal-Secret` request header.
* The secret is read from the `AFT_PORTAL_SECRET` constant (wp-config.php) or
  the `aft_portal_secret` option. It is never committed to this repository.
* Only two read-only routes are registered. There are no write endpoints and
  the plugin never modifies users, posts, or options during a request.
* Per-IP rate limiting (10 attempts per minute).
* Credential lookups return the same error for unknown accounts and wrong
  passwords, and unknown accounts burn comparable CPU time.

== REST routes ==

GET  /wp-json/aft-portal/v1/ping
POST /wp-json/aft-portal/v1/ping expects the shared-secret header and reports
     the site name and plugin version.

POST /wp-json/aft-portal/v1/verify

    Headers: X-AFT-Portal-Secret: <secret>
    Body:    {"identity": "login-or-email", "password": "secret"}

    200 {"ok":true,"user":{...},"enrolment":{"active":true,"count":1,...}}
    400 missing_credentials
    401 invalid_credentials | invalid_secret
    403 not_enrolled
    429 rate_limited
    503 not_configured (no secret stored)

If pretty permalinks are disabled the same routes answer on
`/?rest_route=/aft-portal/v1/verify`.

== Installation ==

1. Upload the plugin and activate it.
2. Store the shared secret, chosen to match the exam portal configuration:

   wp option update aft_portal_secret 'change-me'

   or define it in wp-config.php:

   define( 'AFT_PORTAL_SECRET', 'change-me' );

3. Confirm it answers:

   curl -H 'X-AFT-Portal-Secret: change-me' https://example.com/wp-json/aft-portal/v1/ping

== Changelog ==

= 1.0.0 =
* First release: read-only verify and ping routes with rate limiting.
