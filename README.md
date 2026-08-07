# local_easywize_loginlinks

Minimal Moodle local plugin providing a single web service function:

**`local_easywize_loginlinks_get_login_links`** — create or extend auth_userkey
one-time login links for a batch of users (by userid or email).

## Why a separate plugin

Login links delivered via SMS or email need a lifetime of days, while
`auth_userkey` is typically configured with a `keylifetime` of seconds for its
own flows. This service therefore **ignores auth/userkey keylifetime and sets
the lifetime by parameter (default is 7 days)**. To keep the administration
honest, both the default and the maximum lifetime are visible admin settings
of this plugin, placed in the *Authentication* settings category next to
auth_userkey. `keylifetime` itself keeps governing auth_userkey's own
functions (e.g. `auth_userkey_request_login_url`) unchanged.

## Behaviour

- Lifetime: web service parameter `validhours` wins; `0`/omitted uses the
  configured default (7 days); everything is capped at the configured
  maximum. The maximum itself is hard-limited to 60 days in code — the
  admin setting can only lower that bound, never raise it.
- Users with administrative privileges (site admins or anyone with
  `moodle/site:config`) never get a link; such requests are reported as a
  per-item warning.
- Every issued or extended link triggers the
  `\local_easywize_loginlinks\event\login_link_requested` event, so the
  standard log records who requested a link for which user and when
  (including the expiry timestamp).
- One key per user (`script = auth/userkey`, `instance = userid`). An existing
  key is reused and its validity only ever extended, never shortened — every
  delivered link of a user is the same link.
- auth_userkey deletes all keys of a user on the first successful login, so
  the first click invalidates all outstanding links of that user. This is an
  intended security property (used links cannot be replayed from old
  messages).
- Per-user problems (unknown email, ambiguous email, suspended user) are
  reported as per-item warnings; the batch never fails as a whole.

## Requirements

- Moodle 4.5+ (tested on 4.5 Workplace and 5.0)
- auth_userkey installed and enabled
- Caller needs `auth/userkey:generatekey` in system context; the function must
  be added to the external service the caller's token belongs to.
