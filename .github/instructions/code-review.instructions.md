---
applyTo: "**"
excludeAgent: "cloud-agent"
---

# Code review instructions

This is a photography portfolio, journal and print store built on Kirby (flat-file CMS, no database) with PHP and Stripe Checkout. Merging to `master` deploys to production with no test gate and there are no automated tests, so read behaviour changes carefully.

## What to review

In priority order:

1. Security vulnerabilities and data loss.
2. Correctness: bugs, unhandled edge cases, race conditions.
3. Regressions: changed behaviour of existing callers, API shapes or stored data.
4. Performance: repeated or unbounded work, queries, requests or memory use.
5. Documentation that no longer matches the code.
6. Readability that is likely to cause a future mistake.

## What to report

- Report an issue only if you can name the input or sequence of events that triggers it and the resulting wrong behaviour. For documentation, quote the statement and name the code that contradicts it.
- If an issue depends on something you could not verify, say what you assumed.
- State the impact and suggest a concrete fix.
- Skip formatting, naming preferences, style that doesn't hurt readability, and speculative refactors.
- Don't report problems in code the pull request doesn't touch or affect.
- Finding nothing is an acceptable result.

## Documentation alignment

For every behaviour change, check the code comments and `README.md`, `infra/README.md`, `infra/env.example` and `site/config/config.base`. Flag:

- Comments or docs that now describe old behaviour, including in unchanged lines.
- Renamed or removed files, routes, scripts, settings or environment variables still referenced by their old name.
- New environment variables, routes, scripts or scheduled work that are undocumented.
- Changes to `infra/` files that need a matching manual step which the docs don't mention.

Don't ask for comments on self-explanatory code.

## Project rules

Security:

- Cart and order input is untrusted. Actions that change state check the CSRF token.
- Never trust a price, quantity, stock level or payment result from the client.
- `kirby()->impersonate('kirby')` is needed for anonymous cart writes. Check that nothing user-controlled can write outside the visitor's own order.
- Escape content in templates and emails.
- Never commit or log keys, addresses or other customer data.

Orders and money:

- An order is finalised only after the session id matches and the payment is confirmed. It must not be finalised, deducted or emailed twice, so keep the per-order lock.
- All order lines are validated before any inventory is written.
- Blank stock means unlimited and is never deducted.
- Prices are strings in CAD and Stripe amounts are integer cents. Round before casting.
- A cart change expires the open Stripe session.
- A failed currency or geolocation lookup must not break the page.

Caching, content and operations:

- Cached pages are served by nginx without running PHP. Never cache a page that depends on the session, cart or geolocation.
- The `page.update:after` hook lives in each environment's config. New config keys must be added to `config.base`.
- Blueprints must be valid Symfony YAML for the Kirby version in use.
- After CSS or JavaScript changes, `assets/dist/` must be rebuilt and committed. PurgeCSS drops classes it can't find in templates or scripts.
- Writable or persistent paths must survive a deploy.
- Don't review or suggest edits inside `kirby/`, `vendor/`, submodule plugins or `assets/dist/`.
- Use pnpm for JavaScript.
