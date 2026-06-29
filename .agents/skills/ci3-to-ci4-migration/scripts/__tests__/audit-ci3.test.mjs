import { test } from 'node:test';
import assert from 'node:assert/strict';
import { classifyFile, detectPatterns } from '../audit-ci3.mjs';

test('classify controller', () => {
  assert.equal(classifyFile('controllers/Auth.php'), 'controller');
});
test('classify model', () => {
  assert.equal(classifyFile('models/user_model.php'), 'model');
});
test('classify helper', () => {
  assert.equal(classifyFile('helpers/custom_helper.php'), 'helper');
});
test('classify config', () => {
  assert.equal(classifyFile('config/config.php'), 'config');
});
test('classify hook', () => {
  assert.equal(classifyFile('hooks/log.php'), 'hook');
});
test('classify third_party', () => {
  assert.equal(classifyFile('third_party/fpdf.php'), 'third_party');
});
test('classify view', () => {
  assert.equal(classifyFile('views/auth/login.php'), 'view');
});
test('detect extends CI_Controller', () => {
  assert.ok(detectPatterns(`class Auth extends CI_Controller {}`).includes('extends CI_Controller'));
});
test('detect extends CI_Model', () => {
  assert.ok(detectPatterns(`class User_model extends CI_Model {}`).includes('extends CI_Model'));
});
test('detect get_instance', () => {
  assert.ok(detectPatterns(`$this->CI = &get_instance();`).includes('get_instance'));
});
test('detect db-> Active Record', () => {
  assert.ok(detectPatterns(`$q = $this->db->get('users');`).includes('db-> (Active Record)'));
});
test('detect load->model', () => {
  assert.ok(detectPatterns(`$this->load->model('user_model');`).includes('load->model'));
});
test('detect load->library', () => {
  assert.ok(detectPatterns(`$this->load->library('session');`).includes('load->library'));
});
test('detect form_validation', () => {
  assert.ok(detectPatterns(`$this->form_validation->set_rules('f','L','required');`).includes('form_validation'));
});
test('detect upload->do_upload', () => {
  assert.ok(detectPatterns(`$this->upload->do_upload('f');`).includes('upload->do_upload'));
});
test('detect input->post', () => {
  assert.ok(detectPatterns(`$x = $this->input->post('x');`).includes('input->post'));
});
test('detect session->set_userdata', () => {
  assert.ok(detectPatterns(`$this->session->set_userdata('k', $v);`).includes('session->set_userdata'));
});
// --- pola tambahan dari sintesis ---
test('detect migration->', () => {
  assert.ok(detectPatterns(`$this->migration->latest();`).includes('migration->'));
});
test('detect dbforge->', () => {
  assert.ok(detectPatterns(`$this->dbforge->create_table('t');`).includes('dbforge->'));
});
test('detect pagination->', () => {
  assert.ok(detectPatterns(`$this->pagination->create_links();`).includes('pagination->'));
});
test('detect security->', () => {
  assert.ok(detectPatterns(`$this->security->xss_clean($v);`).includes('security->'));
});
test('detect config->item', () => {
  assert.ok(detectPatterns(`$v = $this->config->item('k');`).includes('config->item'));
});
test('detect parser->', () => {
  assert.ok(detectPatterns(`$this->parser->parse('t', $d);`).includes('parser->'));
});
test('no false positive on clean CI4 code', () => {
  const ci4 = `namespace App\\Controllers;\\nclass Auth extends BaseController {}`;
  assert.equal(detectPatterns(ci4).length, 0);
});
