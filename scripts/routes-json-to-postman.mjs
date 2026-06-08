#!/usr/bin/env node

import fs from 'node:fs';
import path from 'node:path';

const [, , inputArg = 'api-routes.json', outputArg = 'postman-api-collection.json'] = process.argv;

const inputPath = path.resolve(process.cwd(), inputArg);
const outputPath = path.resolve(process.cwd(), outputArg);

const raw = fs.readFileSync(inputPath, 'utf8');
const routes = JSON.parse(raw);

if (!Array.isArray(routes)) {
  throw new Error('Input must be a JSON array from "php artisan route:list --json".');
}

const groups = new Map();

for (const route of routes) {
  const uri = String(route?.uri ?? '').replace(/^\//, '');
  if (!uri.startsWith('api/')) {
    continue;
  }

  const methods = String(route?.method ?? '')
    .split('|')
    .map((m) => m.trim().toUpperCase())
    .filter(Boolean)
    .filter((m) => m !== 'HEAD' && m !== 'OPTIONS');

  if (methods.length === 0) {
    continue;
  }

  const segments = uri.split('/');
  const folderName = segments[1] ?? 'misc';

  if (!groups.has(folderName)) {
    groups.set(folderName, []);
  }

  for (const method of methods) {
    const action = String(route?.action ?? 'UnknownAction');
    const name = route?.name ? String(route.name) : `${method} ${uri}`;
    const middleware = Array.isArray(route?.middleware) ? route.middleware.map(String) : [];

    const pathSegments = uri.split('/').map((segment) => {
      const paramMatch = segment.match(/^\{(.+)\}$/);
      if (!paramMatch) {
        return segment;
      }

      const paramName = paramMatch[1].replace(/\?$/, '');
      return `:${paramName}`;
    });

    const rawUrl = `{{baseUrl}}/${pathSegments.join('/')}`;
    const pathVariables = [];

    for (const segment of pathSegments) {
      if (!segment.startsWith(':')) {
        continue;
      }

      const key = segment.slice(1);
      pathVariables.push({
        key,
        value: `sample_${key}`,
      });
    }

    const headers = [
      { key: 'Accept', value: 'application/json' },
    ];

    if (['POST', 'PUT', 'PATCH'].includes(method)) {
      headers.push({ key: 'Content-Type', value: 'application/json' });
    }

    const needsAuth = middleware.some((m) => m.includes('Authenticate:sanctum'));
    if (needsAuth) {
      headers.push({ key: 'Authorization', value: 'Bearer {{sanctumToken}}' });
    }

    const request = {
      name,
      request: {
        method,
        header: headers,
        url: {
          raw: rawUrl,
          host: ['{{baseUrl}}'],
          path: pathSegments,
          variable: pathVariables,
        },
        description: `Action: ${action}\nMiddleware: ${middleware.join(', ') || 'none'}`,
      },
      response: [],
    };

    if (['POST', 'PUT', 'PATCH'].includes(method)) {
      request.request.body = {
        mode: 'raw',
        raw: JSON.stringify({
          TODO: 'Add request payload',
        }, null, 2),
        options: {
          raw: {
            language: 'json',
          },
        },
      };
    }

    groups.get(folderName).push(request);
  }
}

const collection = {
  info: {
    name: 'Classer API Routes',
    schema: 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    _postman_id: 'classer-api-routes-generated',
  },
  item: Array.from(groups.entries())
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([folderName, items]) => ({
      name: folderName,
      item: items,
    })),
  variable: [
    {
      key: 'baseUrl',
      value: 'http://localhost',
      type: 'string',
    },
    {
      key: 'sanctumToken',
      value: '',
      type: 'string',
    },
  ],
};

fs.writeFileSync(outputPath, JSON.stringify(collection, null, 2) + '\n', 'utf8');

console.log(`Wrote Postman collection to ${outputPath}`);
