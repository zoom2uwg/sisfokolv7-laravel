#!/usr/bin/env node
// audit-ci3.mjs — scan codebase CI3 (application/), laporkan ~17 pola + estimasi effort.

import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';
import { fileURLToPath } from 'node:url';

export function classifyFile(relPath) {
  const norm = relPath.replace(/\\/g, '/');
  if (norm.startsWith('controllers/'))  return 'controller';
  if (norm.startsWith('models/'))       return 'model';
  if (norm.startsWith('libraries/'))    return 'library';
  if (norm.startsWith('helpers/'))      return 'helper';
  if (norm.startsWith('config/'))       return 'config';
  if (norm.startsWith('hooks/'))        return 'hook';
  if (norm.startsWith('third_party/'))  return 'third_party';
  if (norm.startsWith('views/'))        return 'view';
  return 'other';
}

export function detectPatterns(src) {
  const checks = [
    { name: 'extends CI_Controller', re: /extends\s+CI_Controller/ },
    { name: 'extends CI_Model',      re: /extends\s+CI_Model/ },
    { name: 'load->model',           re: /\$this->load->model\(/ },
    { name: 'load->library',         re: /\$this->load->library\(/ },
    { name: 'load->view',            re: /\$this->load->view\(/ },
    { name: 'input->post',           re: /\$this->input->post\(/ },
    { name: 'session->set_userdata', re: /\$this->session->set_userdata\(/ },
    { name: 'form_validation',       re: /\$this->form_validation/ },
    { name: 'get_instance',          re: /get_instance\(\)/ },
    { name: 'db-> (Active Record)',  re: /\$this->db->/ },
    { name: 'upload->do_upload',     re: /\$this->upload->do_upload\(/ },
    // --- pola tambahan dari sintesis ---
    { name: 'migration->',           re: /\$this->migration->/ },
    { name: 'dbforge->',             re: /\$this->dbforge->/ },
    { name: 'pagination->',          re: /\$this->pagination->/ },
    { name: 'security->',            re: /\$this->security->/ },
    { name: 'config->item',          re: /\$this->config->item\(/ },
    { name: 'parser->',              re: /\$this->parser->/ },
  ];
  return checks.filter(c => c.re.test(src)).map(c => c.name);
}

export function auditProject(applicationDir) {
  const files = [];
  (function walk(dir) {
    for (const e of readdirSync(dir)) {
      const full = join(dir, e);
      if (statSync(full).isDirectory()) walk(full);
      else if (extname(e) === '.php') files.push(full);
    }
  })(applicationDir);

  const report = { files: [], byType: {}, patterns: {}, customMy: [], thirdParty: [] };
  for (const f of files) {
    const src = readFileSync(f, 'utf8');
    const rel = f.slice(applicationDir.length + 1).replace(/\\/g, '/');
    const type = classifyFile(rel);
    const pats = detectPatterns(src);
    report.byType[type] = (report.byType[type] || 0) + 1;
    for (const p of pats) report.patterns[p] = (report.patterns[p] || 0) + 1;
    if (/\bclass\s+MY_/.test(src)) report.customMy.push(rel);
    if (/third_party/.test(rel)) report.thirdParty.push(rel);
    report.files.push({ rel, type, patterns: pats });
  }
  const ctrl = report.byType.controller || 0;
  report.effort = ctrl < 10 ? 'kecil' : ctrl < 25 ? 'sedang' : 'besar';
  return report;
}

function main() {
  const target = process.argv.slice(2).find(a => !a.startsWith('--'));
  if (!target) {
    console.error('Usage: node audit-ci3.mjs <ci3-application-dir>');
    process.exit(1);
  }
  const r = auditProject(target);
  console.log('=== AUDIT CI3 ===');
  console.log(`Total file PHP: ${r.files.length}`);
  console.log('Per type:', r.byType);
  console.log('Pola terdeteksi:', r.patterns);
  if (r.customMy.length) console.log('Custom MY_*:', r.customMy);
  if (r.thirdParty.length) console.log('Third-party:', r.thirdParty);
  console.log(`Estimasi effort: ${r.effort} (${r.byType.controller || 0} controller)`);
  console.log('\n--- JSON ---');
  console.log(JSON.stringify(r, null, 2));
}

if (process.argv[1] === fileURLToPath(import.meta.url)) main();
