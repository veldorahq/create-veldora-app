#!/usr/bin/env node

/**
 * create-veldora-app
 * Official project initializer for Veldora PHP Framework
 * Author: Shahriyar Fahim
 * License: MIT
 */

import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import readline from 'node:readline';
import { fileURLToPath } from 'node:url';
import { execSync } from 'node:child_process';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// ── Colors & formatting ──────────────────────────────────────────────────────
const reset = '\x1b[0m';
const bold = '\x1b[1m';
const dim = '\x1b[2m';
const green = '\x1b[32m';
const cyan = '\x1b[36m';
const purple = '\x1b[35m';
const yellow = '\x1b[33m';
const red = '\x1b[31m';

function ask(question, defaultValue = '') {
    const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout,
    });

    const displayPrompt = defaultValue
        ? `${cyan}?${reset} ${bold}${question}${reset} ${dim}(${defaultValue})${reset}: `
        : `${cyan}?${reset} ${bold}${question}${reset}: `;

    return new Promise(resolve => {
        rl.question(displayPrompt, answer => {
            rl.close();
            resolve(answer.trim() || defaultValue);
        });
    });
}

function copyRecursiveSync(src, dest, replacements = {}) {
    const exists = fs.existsSync(src);
    const stats = exists && fs.statSync(src);
    const isDirectory = exists && stats.isDirectory();

    if (isDirectory) {
        if (!fs.existsSync(dest)) {
            fs.mkdirSync(dest, { recursive: true });
        }
        fs.readdirSync(src).forEach(childItemName => {
            copyRecursiveSync(
                path.join(src, childItemName),
                path.join(dest, childItemName),
                replacements
            );
        });
    } else {
        let content = fs.readFileSync(src);
        // If it is a text file, do replacement
        const ext = path.extname(src).toLowerCase();
        const textExts = ['.json', '.php', '.env', '.example', '.md', '.txt', '.js', '.css', '.gitignore'];
        if (textExts.includes(ext) || path.basename(src).startsWith('.')) {
            let str = content.toString('utf-8');
            for (const [key, val] of Object.entries(replacements)) {
                str = str.split(key).join(val);
            }
            fs.writeFileSync(dest, str, 'utf-8');
        } else {
            fs.writeFileSync(dest, content);
        }
    }
}

function generateAppKey() {
    return 'base64:' + crypto.randomBytes(32).toString('base64');
}

async function main() {
    console.log();
    console.log(`${purple}${bold}  ▲ Veldora Framework${reset} ${dim}v0.4.0-beta${reset}`);
    console.log(`${dim}  The modern PHP framework you actually own.${reset}`);
    console.log();

    const args = process.argv.slice(2);
    const firstArg = (args[0] || '').toLowerCase().trim();

    // ── Help commands ────────────────────────────────────────────────────────
    if (
        args.includes('--help') ||
        args.includes('-h') ||
        firstArg === 'help' ||
        firstArg === '?' ||
        firstArg === '-h' ||
        firstArg === '--help'
    ) {
        console.log(`  ${bold}Usage:${reset}`);
        console.log(`    $ npx create-veldora-app [project-name]`);
        console.log(`    $ veldora [project-name]`);
        console.log(`    $ veldora new [project-name]`);
        console.log(`    $ veldora create [project-name]`);
        console.log();
        console.log(`  ${bold}Commands:${reset}`);
        console.log(`    ${cyan}new, create${reset}     Scaffold a new Veldora application`);
        console.log(`    ${cyan}help${reset}            Display this help message`);
        console.log(`    ${cyan}version${reset}         Display version number`);
        console.log();
        console.log(`  ${bold}Options:${reset}`);
        console.log(`    -h, --help       Display this help message`);
        console.log(`    -v, --version    Display version number`);
        console.log();
        console.log(`  ${dim}Author: Shahriyar Fahim • https://veldora.dev${reset}\n`);
        process.exit(0);
    }

    // ── Version commands ─────────────────────────────────────────────────────
    if (
        args.includes('--version') ||
        args.includes('-v') ||
        firstArg === 'version' ||
        firstArg === '-v' ||
        firstArg === '--version'
    ) {
        console.log(`  v0.4.0-beta.4\n`);
        process.exit(0);
    }

    // ── Determine project directory / name ───────────────────────────────────
    let targetDir = '';

    if (firstArg === 'new' || firstArg === 'create' || firstArg === 'init') {
        targetDir = args[1] || '';
    } else {
        targetDir = args[0] || '';
    }

    if (!targetDir) {
        targetDir = await ask('What is your project named?', 'my-veldora-app');
    }

    // Clean directory name
    const projectName = path.basename(targetDir.trim());
    const projectPath = path.resolve(process.cwd(), targetDir.trim());

    if (fs.existsSync(projectPath) && fs.readdirSync(projectPath).length > 0) {
        console.log(`\n${red}✖ Directory ${projectName} is not empty.${reset} Please choose another name or directory.\n`);
        process.exit(1);
    }

    console.log(`\n${cyan}Creating a new Veldora app in${reset} ${bold}${projectPath}${reset}...\n`);

    const templateDir = path.resolve(__dirname, '../template');

    if (!fs.existsSync(templateDir)) {
        console.log(`${red}✖ Error: Template directory not found at ${templateDir}${reset}`);
        process.exit(1);
    }

    // 1. Copy template files
    const appKey = generateAppKey();
    const replacements = {
        '{{APP_NAME}}': projectName,
        'veldora/app': `${projectName}/app`,
        'APP_NAME=Veldora': `APP_NAME="${projectName}"`,
        'APP_KEY=': `APP_KEY=${appKey}`,
    };

    copyRecursiveSync(templateDir, projectPath, replacements);

    // 2. Ensure .env exists from .env.example with secure random key
    const envPath = path.join(projectPath, '.env');
    const envExamplePath = path.join(projectPath, '.env.example');
    if (fs.existsSync(envExamplePath)) {
        let envContent = fs.readFileSync(envExamplePath, 'utf-8');
        envContent = envContent.replace(/APP_NAME\s*=.*/g, `APP_NAME="${projectName}"`);
        envContent = envContent.replace(/APP_KEY\s*=.*/g, `APP_KEY="${appKey}"`);
        fs.writeFileSync(envPath, envContent, 'utf-8');
    }

    // 3. Ensure storage subdirectories and .gitkeeps
    const storageDirs = [
        path.join(projectPath, 'storage/framework/views'),
        path.join(projectPath, 'storage/logs'),
        path.join(projectPath, 'storage/app'),
        path.join(projectPath, 'database'),
    ];
    for (const dir of storageDirs) {
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }
        const gitkeep = path.join(dir, '.gitkeep');
        if (!fs.existsSync(gitkeep)) {
            fs.writeFileSync(gitkeep, '', 'utf-8');
        }
    }

    // 4. Try running composer install
    let composerSuccess = false;
    try {
        process.stdout.write(`${dim}Installing dependencies via Composer...${reset} `);
        execSync('composer --version', { stdio: 'ignore' });
        execSync('composer install --no-interaction --quiet', { cwd: projectPath, stdio: 'ignore' });
        composerSuccess = true;
        console.log(`${green}✔ Done${reset}`);
    } catch {
        console.log(`${yellow}(composer install skipped or composer not found)${reset}`);
    }

    // 5. Try initializing git repository
    try {
        execSync('git init --quiet', { cwd: projectPath, stdio: 'ignore' });
        execSync('git add -A', { cwd: projectPath, stdio: 'ignore' });
        execSync('git commit -m "chore: initial commit from create-veldora-app" --quiet', { cwd: projectPath, stdio: 'ignore' });
    } catch {
        // Git initialization is optional
    }

    // 6. Success Output
    console.log();
    console.log(`${green}${bold}🎉 Success!${reset} Created ${bold}${projectName}${reset} at ${projectPath}`);
    console.log();
    console.log(`Inside that directory, you can run several commands:`);
    console.log();
    console.log(`  ${cyan}php veldora serve${reset}`);
    console.log(`    Starts the local development server on http://localhost:8000`);
    console.log();
    console.log(`  ${cyan}php veldora make:controller${reset} PostController`);
    console.log(`    Scaffolds a new controller with action methods`);
    console.log();
    console.log(`  ${cyan}php veldora add${reset} button card modal`);
    console.log(`    Copies production-ready UI components to resources/views/components/`);
    console.log();
    console.log(`We suggest that you begin by typing:`);
    console.log();
    if (path.relative(process.cwd(), projectPath) !== '') {
        console.log(`  ${purple}cd${reset} ${targetDir}`);
    }
    if (!composerSuccess) {
        console.log(`  ${purple}composer install${reset}`);
    }
    console.log(`  ${purple}php veldora serve${reset}`);
    console.log();
    console.log(`${dim}Created with love by Shahriyar Fahim • https://veldora.dev${reset}`);
    console.log();
}

main().catch(err => {
    console.error(`\n${red}Error:${reset}`, err.message);
    process.exit(1);
});
