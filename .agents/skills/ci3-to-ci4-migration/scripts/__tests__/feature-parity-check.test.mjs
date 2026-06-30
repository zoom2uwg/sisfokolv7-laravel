import { test } from 'node:test';
import assert from 'node:assert/strict';
import { parseCi3Routes, parseCi4Routes, compareRoutes } from '../feature-parity-check.mjs';

test('parse CI3 simple route', () => {
  const r = parseCi3Routes(`$route['foo/bar'] = 'foo/bar';`);
  assert.equal(r.length, 1);
  assert.equal(r[0].route, 'foo/bar');
  assert.equal(r[0].handler, 'foo/bar');
  assert.equal(r[0].method, 'ANY');
});
test('parse CI3 param route', () => {
  const r = parseCi3Routes(`$route['user/(:num)'] = 'user/view/$1';`);
  assert.equal(r[0].route, 'user/(:num)');
  assert.equal(r[0].handler, 'user/view/$1');
});
test('parse CI3 verb route', () => {
  const r = parseCi3Routes(`$route['x']['post'] = 'c/m';`);
  assert.equal(r[0].method, 'POST');
});
test('parse CI3 skips default_controller/404_override', () => {
  const r = parseCi3Routes(`$route['default_controller'] = 'welcome'; $route['foo'] = 'c/m';`);
  assert.equal(r.length, 1);
  assert.equal(r[0].route, 'foo');
});
test('parse CI4 get route', () => {
  const r = parseCi4Routes(`$routes->get('foo/bar', 'Foo::bar');`);
  assert.equal(r.length, 1);
  assert.equal(r[0].method, 'GET');
  assert.equal(r[0].route, 'foo/bar');
  assert.equal(r[0].handler, 'Foo::bar');
});
test('parse CI4 post route', () => {
  const r = parseCi4Routes(`$routes->post('x', 'C::m');`);
  assert.equal(r[0].method, 'POST');
});
test('parse CI4 add() maps to ANY', () => {
  const r = parseCi4Routes(`$routes->add('x', 'C::m');`);
  assert.equal(r[0].method, 'ANY');
});
test('compare finds missing routes', () => {
  const ci3 = [
    { method: 'ANY', route: 'a/b', handler: 'a@b' },
    { method: 'ANY', route: 'c/d', handler: 'c@d' },
  ];
  const ci4 = [{ method: 'ANY', route: 'a/b', handler: 'A::b' }];
  const r = compareRoutes(ci3, ci4);
  assert.equal(r.missing.length, 1);
  assert.equal(r.missing[0].route, 'c/d');
});
test('ANY in CI4 covers verb-specific CI3 route', () => {
  const ci3 = [{ method: 'POST', route: 'a/b', handler: 'a@b' }];
  const ci4 = [{ method: 'ANY', route: 'a/b', handler: 'A::b' }];
  assert.equal(compareRoutes(ci3, ci4).missing.length, 0);
});
test('counts correct', () => {
  const ci3 = [{ method: 'ANY', route: 'a', handler: 'x' }, { method: 'ANY', route: 'b', handler: 'y' }];
  const ci4 = [{ method: 'ANY', route: 'a', handler: 'X' }];
  const r = compareRoutes(ci3, ci4);
  assert.equal(r.ci3Count, 2);
  assert.equal(r.ci4Count, 1);
});
