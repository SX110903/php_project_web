import { readdirSync, existsSync, statSync } from 'fs';
import { resolve } from 'path';

const cwd = process.cwd();
console.log("[v0] Current working directory:", cwd);
console.log("[v0] app dir exists:", existsSync(resolve(cwd, 'app')));
console.log("[v0] app is directory:", existsSync(resolve(cwd, 'app')) && statSync(resolve(cwd, 'app')).isDirectory());

if (existsSync(resolve(cwd, 'app'))) {
  console.log("[v0] app dir contents:", readdirSync(resolve(cwd, 'app')));
}

console.log("[v0] Root dir contents:", readdirSync(cwd).filter(f => !f.startsWith('.')));
