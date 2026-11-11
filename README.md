# raqmix-hello-php

A minimal PHP API that deploys on **Raqmix** with one front controller
and one tiny composer package. It exists to prove the platform's PHP
contract end to end, in the smallest possible repo, and to look good
doing it.

```bash
git clone … && cd raqmix-hello-php
composer install            # optional locally; the platform runs it for you
php -S localhost:3000 -t public   # → http://localhost:3000
```

## What it serves

| Route       | What you get                                                       |
|-------------|--------------------------------------------------------------------|
| `/`         | The Raqmix banner: mint mark, greeting, host, uptime, visit count   |
| `/api/hello`| `{ app, message, poweredBy: "raqmix", php, host, uptimeSeconds, visits }` |
| `/healthz`  | `{ status: "ok", php, uptimeSeconds, visits }` — quick liveness probe |

The greeting comes from the `RQMIX_GREETING` environment variable and
defaults to `Salam from Raqmix`. Set it in the deploy modal to see the
encrypted env feature move a value from your keyboard to the pod.

## Why the repo is shaped this way (the deploy contract)

The Raqmix PHP template clones the repo, runs
`composer install --no-dev --no-scripts` (dependency lifecycle hooks
disabled, the same supply-chain rule as the Node template), and serves
the docroot with PHP's built-in web server on the platform's port
contract. Each file satisfies one rule of that contract:

- **`public/index.php`** — the front controller. The template looks for
  `public/index.php` first (the Laravel/Symfony convention), then
  `index.php` in the repository root. One of the two must exist.
- **`composer.json` + **`composer.lock` (committed!)** — `composer
  install` needs a lock for reproducible builds; without one every
  build resolves live versions (the PHP sibling of the committed
  package-lock rule, the single most common first-deploy failure).
- **zero framework** — nothing to configure, nothing to break; add
  Laravel/Symfony normally when your app needs one (the front
  controller convention already matches theirs).
- **PORT is the platform's** — the template binds the server to 3000
  and reserves the name; a PHP app never binds a port itself.
- `require: psr/log` — one tiny package with no transitive deps, there
  purely to prove the composer install path works on the cluster.

The built-in web server is the platform's prototype runtime: one
process, no fpm/nginx to assemble. The production php-fpm + nginx form
is on the readiness plan, behind the same contract.

## Deploying it on Raqmix

1. Push this repo to GitHub (private works: the platform clones
   private repos with your linked GitHub account).
2. Dashboard → Deploy tab → paste the repo URL (or pick it from the
   repository picker) → **Build**.
3. Pick a Ready cluster, set initial pods (and autoscale if you like),
   optionally add `RQMIX_GREETING` as an env var, choose **public**
   exposure, deploy.
4. Open the returned `https://raqmix-hello-php-….apps.localhost` URL —
   the banner should greet you, PHP version included.

---

Built in Algeria. Runs everywhere.
