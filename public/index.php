<?php
/**
 * hello-raqmix (PHP) - the minimal PHP API that deploys on Raqmix.
 *
 * One front controller, zero configuration: the platform's PHP template
 * installs composer dependencies (this one has a single tiny package,
 * just to prove the composer path), then serves the docroot with PHP's
 * built-in web server on the platform's PORT contract (3000). Nothing
 * here needs a framework to say hello.
 *
 * Contract with the platform:
 *   - the front controller is public/index.php (the framework
 *     convention) or index.php in the repository root
 *   - PORT is fixed to 3000 by the template and cannot be overridden
 *   - reads one optional app env var (RQMIX_GREETING) to demonstrate
 *     the encrypted env feature - everything degrades to a default.
 */

declare(strict_types=1);

const MINT = '#34D59A';

$greeting = getenv('RQMIX_GREETING') ?: 'Salam from Raqmix';

/**
 * Process uptime via a start marker: the built-in server may serve with
 * several workers, so "when this pod first answered" is the honest line.
 */
function startedAt(): int {
    $marker = sys_get_temp_dir() . '/hello-raqmix-php.started';
    if (!is_file($marker)) touch($marker);
    return filemtime($marker) ?: time();
}

/** Visit counter: one file, an exclusive lock, no dependencies. */
function bumpVisits(): int {
    $file = sys_get_temp_dir() . '/hello-raqmix-php.visits';
    $fp = fopen($file, 'c+');
    if (!$fp) return 0;
    flock($fp, LOCK_EX);
    $n = (int) fgets($fp);
    $n += 1;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, (string) $n);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $n;
}

function respondJson(int $status, array $body): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Powered-By: raqmix');
    echo json_encode($body, JSON_UNESCAPED_SLASHES);
    exit;
}

function banner(string $greeting, int $visits): string {
    $up = max(0, time() - startedAt());
    $host = gethostname() ?: 'unknown';
    $logo = 'M48.5544 0.192993C58.7354 -1.87186 71.306 12.8174 80.2868 35.4635C104.8 33.272 123.711 38.0069 126.144 48.2418L126.213 48.5533C128.278 58.7345 113.587 71.3059 90.9402 80.2867C93.1316 104.8 88.3979 123.71 78.1628 126.143C67.9275 128.576 55.1905 113.818 46.1179 90.9391C21.6044 93.1305 2.69413 88.3969 0.261444 78.1617C-2.17088 67.9266 12.5867 55.1894 35.4646 46.1168C33.2734 21.6037 38.008 2.69315 48.2429 0.260376L48.5544 0.192993ZM83.1462 83.1266C78.621 84.6375 73.8539 86.0021 68.9011 87.1793C63.9483 88.3565 59.0772 89.2826 54.3562 89.9694C57.1634 96.7257 60.2681 102.574 63.4411 107.239C66.6435 111.947 69.6711 115.087 72.1999 116.833C74.7302 118.579 75.979 118.439 76.3132 118.36C76.6478 118.28 77.8261 117.843 79.2995 115.145C80.7724 112.448 82.0627 108.282 82.8044 102.636C83.5392 97.0429 83.6791 90.4234 83.1462 83.1266ZM36.4343 54.3561C29.6787 57.1631 23.8311 60.2673 19.1667 63.4401C14.4588 66.6425 11.318 69.6699 9.57199 72.1989C7.82524 74.7291 7.96525 75.9779 8.04465 76.3121C8.12411 76.6463 8.56091 77.8247 11.2595 79.2985C13.9566 80.7713 18.1231 82.0617 23.7683 82.8033C29.3614 83.5381 35.9808 83.6789 43.2771 83.1461C41.7663 78.6213 40.4024 73.8544 39.2253 68.902C38.0481 63.949 37.1211 59.0773 36.4343 54.3561ZM74.9646 44.1051C69.9598 44.7717 64.7245 45.7308 59.3532 47.0074C53.9819 48.2841 48.8746 49.7825 44.1052 51.4391C44.7718 56.4442 45.7318 61.6797 47.0085 67.0514C48.2851 72.4223 49.7838 77.5293 51.4402 82.2985C56.4448 81.6319 61.6802 80.6728 67.0515 79.3961C72.4229 78.1194 77.53 76.6201 82.2995 74.9635C81.633 69.9592 80.6737 64.724 79.3972 59.3531C78.1205 53.9817 76.6212 48.8747 74.9646 44.1051ZM102.637 43.6012C97.0439 42.8663 90.4237 42.7245 83.1267 43.2574C84.6377 47.783 86.0031 52.5502 87.1804 57.5035C88.3575 62.4559 89.2837 67.3268 89.9704 72.0475C96.7265 69.2403 102.574 66.1364 107.239 62.9635C111.947 59.7613 115.087 56.7355 116.833 54.2067C118.579 51.6767 118.44 50.4271 118.361 50.0924C118.282 49.7582 117.844 48.58 115.145 47.1061C112.448 45.6333 108.283 44.3429 102.637 43.6012ZM63.8435 54.5621C68.2616 54.5623 71.8435 58.1439 71.8435 62.5621C71.8433 66.9802 68.2615 70.562 63.8435 70.5621C59.4253 70.5621 55.8437 66.9802 55.8435 62.5621C55.8435 58.1439 59.4252 54.5621 63.8435 54.5621ZM63.8435 59.5621C62.1866 59.5621 60.8435 60.9053 60.8435 62.5621C60.8437 64.2188 62.1867 65.5621 63.8435 65.5621C65.5001 65.562 66.8433 64.2187 66.8435 62.5621C66.8435 60.9054 65.5002 59.5623 63.8435 59.5621ZM54.2068 9.57288C51.6758 7.82549 50.4266 7.96418 50.0925 8.04358C49.758 8.12318 48.5808 8.56187 47.1071 11.2604C45.6344 13.9575 44.3429 18.1231 43.6013 23.7682C42.8666 29.3611 42.7257 35.98 43.2585 43.276C47.7836 41.7652 52.551 40.4014 57.5036 39.2242C62.4564 38.0471 67.3275 37.12 72.0486 36.4332C69.2415 29.6776 66.1373 23.8299 62.9646 19.1656C59.7624 14.4581 56.7356 11.3189 54.2068 9.57288Z"';
    $esc = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES);
    return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>hello-raqmix (php)</title>
<style>
  :root { color-scheme: dark; }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: grid; place-items: center;
    background: #060b09; color: #eafff7;
    font-family: ui-monospace, "SF Mono", Menlo, monospace;
    text-align: center; padding: 24px; position: relative; overflow: hidden;
  }
  /* The Raqmix cobra texture (the login panel's dot-field): dense gradient
     dots revealed in a radial band, glowing at the centre, melting into the
     dark canvas at the edges. Pure SVG, straight from DotField.tsx. */
  .cobra { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
  .card { position: relative; z-index: 1; }
  .star {
    width: 72px; height: 72px; margin: 0 auto 18px; color: #34D59A;
    filter: drop-shadow(0 0 18px rgba(52, 213, 154, .45));
    animation: pulse 3.2s ease-in-out infinite;
  }
  /* Scale only: the four-pointed star reads as skewed under any rotation. */
  @keyframes pulse { 50% { transform: scale(1.07); } }
  h1 { font-size: 20px; letter-spacing: .12em; margin: 0 0 10px; color: #fff; }
  p  { margin: 4px 0; font-size: 13px; color: #9fc7ba; }
  b  { color: #34D59A; font-weight: 600; }
  .tag {
    margin-top: 20px; display: inline-block; padding: 6px 14px; border-radius: 999px;
    border: 1px solid rgba(52, 213, 154, .35); color: #34D59A; font-size: 11px; letter-spacing: .14em;
  }
</style>
</head>
<body>
  <svg class="cobra" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
    <defs>
      <pattern id="rq-dots" x="0" y="0" width="15" height="15" patternUnits="userSpaceOnUse">
        <rect width="15" height="15" fill="#000000"></rect>
        <circle cx="7.5" cy="7.5" r="7.5" fill="#ffffff"></circle>
      </pattern>
      <mask id="rq-dot"><rect width="100%" height="100%" fill="url(#rq-dots)"></rect></mask>
      <linearGradient id="rq-grad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#00FFCC"></stop>
        <stop offset="100%" stop-color="#FF9900"></stop>
      </linearGradient>
      <radialGradient id="rq-radial" cx="50%" cy="50%" r="50%">
        <stop offset="0%" stop-color="#000000" stop-opacity="1"></stop>
        <stop offset="66%" stop-color="#000000" stop-opacity="0.91"></stop>
        <stop offset="100%" stop-color="#000000" stop-opacity="0.30"></stop>
      </radialGradient>
      <radialGradient id="rq-edge" cx="50%" cy="50%" r="50%">
        <stop offset="55%" stop-color="#000000" stop-opacity="0"></stop>
        <stop offset="100%" stop-color="#000000" stop-opacity="1"></stop>
      </radialGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#rq-grad)" mask="url(#rq-dot)"></rect>
    <rect width="100%" height="100%" fill="url(#rq-radial)"></rect>
    <rect width="100%" height="100%" fill="url(#rq-edge)"></rect>
  </svg>
  <div class="card">
    <svg class="star" viewBox="0 0 127 127" aria-hidden="true"><path fill="currentColor" d="{$logo}"></path></svg>
    <h1>{$esc($greeting)}</h1>
    <p>this tiny PHP {$esc(PHP_VERSION)} API is running on <b>raqmix</b></p>
    <p>host <b>{$esc($host)}</b> &middot; up <b>{$upM}m {$upS}s</b> &middot; visits <b>{$visits}</b></p>
    <span class="tag">BUILT IN ALGERIA &middot; RUNS EVERYWHERE</span>
  </div>
</body>
</html>
HTML;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$visits = bumpVisits();
$host = gethostname() ?: 'unknown';
$up = max(0, time() - startedAt());

if ($path === '/' || $path === '/index.html') {
    header('Content-Type: text/html; charset=utf-8');
    header('X-Powered-By: raqmix');
    echo banner($greeting, $visits);
    return;
}
if ($path === '/healthz') {
    respondJson(200, ['status' => 'ok', 'php' => PHP_VERSION, 'uptimeSeconds' => $up, 'visits' => $visits]);
}
// The platform-branded identity endpoint: what a deploy verification
// curls when it lands on a cluster.
if ($path === '/api' || $path === '/api/hello') {
    respondJson(200, [
        'app' => 'raqmix-hello-php',
        'message' => $greeting,
        'poweredBy' => 'raqmix',
        'php' => PHP_VERSION,
        'host' => $host,
        'uptimeSeconds' => $up,
        'visits' => $visits,
    ]);
}
respondJson(404, ['error' => 'not found', 'hint' => 'try / , /api/hello or /healthz']);
