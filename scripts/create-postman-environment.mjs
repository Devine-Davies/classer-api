#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';

const [, , outputArg = 'postman-api-environment.json', baseUrlArg = 'http://localhost'] = process.argv;

const outputPath = path.resolve(process.cwd(), outputArg);

const environment = {
  id: 'classer-api-env-generated',
  name: 'Classer API Local',
  values: [
    {
      key: 'baseUrl',
      value: baseUrlArg,
      enabled: true,
      type: 'default',
    },
    {
      key: 'sanctumToken',
      value: '',
      enabled: true,
      type: 'secret',
    },
  ],
  _postman_variable_scope: 'environment',
  _postman_exported_at: new Date().toISOString(),
  _postman_exported_using: 'classer-api-script',
};

fs.writeFileSync(outputPath, JSON.stringify(environment, null, 2) + '\n', 'utf8');
console.log(`Wrote Postman environment to ${outputPath}`);
