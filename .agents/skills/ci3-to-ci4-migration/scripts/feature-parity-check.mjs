#!/usr/bin/env node
// feature-parity-check.mjs — bandingkan struktur route CI3 vs CI4, laporkan yg belum termigrasi.

import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

export function parseCi3Routes(src) {
  const routes = [];
  // $route['x']['post'] = 'c/m';  (verb-specific, cek dulu sebelum simple)
  const verb = /\$route\[(['"])([^'"]+)\1\]\[(['"])(get|post|put|delete|patch)\3\]\s*=\s*(['"])([^'"]+)\5\s*;?/gi;
  let m;
  while ((m = verb.exec(src))) {
    routes.push({ method: m[4].toUpperCase(), route: m[2], handler: m[6] });
  }
  // $route['foo/bar'] = 'c/m';  (simple, skip default_controller / 404_override / translate_uri_dashes)
  const simple = /\$route\[(['"])([^'"]+)\1\]\s*=\s*(['"])([^'"]+)\3\s*;?/g;
  while ((m = simple.exec(src))) {
    if (['default_controller', '404_override', 'translate_uri_dashes'].includes(m[2])) continue;
    routes.push({ method: 'ANY', route: m[2], handler: m[4] });
  }
  return routes;
}

export function parseCi4Routes(src) {
  const routes = [];
  const re = /\$routes->(get|post|put|delete|patch|match|add)\(\s*(['"])([^'"]+)\2\s*,\s*(['"])([^'"]+)\4/gi;
  let m;
  while ((m = re.exec(src))) {
    let method = m[1].toLowerCase();
    if (method === 'add' || method === 'match') method = 'ANY';
    else method = method.toUpperCase();
    routes.push({ method, route: m[3], handler: m[5] });
  }
  return routes;
}

export function compareRoutes(ci3, ci4) {
  const ci4Any = new Set(ci4.filter(r => r.method === 'ANY').map(r => r.route));
  const ci4Set = new Set(ci4.map(r => `${r.method} ${r.route}`));
  const missing = ci3.filter(r => {
    if (ci4Any.has(r.route)) return false;        // CI4 ANY covers any verb
    return !ci4Set.has(`${r.method} ${r.route}`) && !ci4Set.has(`ANY ${r.route}`);
  });
  return { ci3Count: ci3.length, ci4Count: ci4.length, missing };
}

function main() {
  const args = process.argv.slice(2);
  const ci3RoutesFile = args.find(a => a.includes('routes.php') && !a.startsWith('--'));
  const ci4RoutesFile = args.find(a => a.includes('Routes.php') && !a.startsWith('--'));
  if (!ci3RoutesFile || !ci4RoutesFile) {
    console.error('Usage: node feature-parity-check.mjs <ci3-routes.php> <ci4-Routes.php>');
    process.exit(1);
  }
  const ci3 = parseCi3Routes(readFileSync(ci3RoutesFile, 'utf8'));
  const ci4 = parseCi4Routes(readFileSync(ci4RoutesFile, 'utf8'));
  const r = compareRoutes(ci3, ci4);
  console.log('=== FEATURE PARITY (route) ===');
  console.log(`CI3 routes: ${r.ci3Count} | CI4 routes: ${r.ci4Count} | missing: ${r.missing.length}`);
  if (r.missing.length) {
    console.log('\nRoute CI3 belum ada di CI4:');
    for (const m of r.missing) console.log(`  ${m.method} ${m.route}  ->  ${m.handler}`);
  } else {
    console.log('\n✓ Semua route CI3 sudah ada di CI4.');
  }
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
