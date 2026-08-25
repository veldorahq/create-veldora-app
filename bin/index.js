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

// ── Colors & formatting (Clean modern developer palette) ─────────────────────
const reset = '\x1b[0m';
const bold = '\x1b[1m';
const dim = '\x1b[90m'; // muted silver-gray for descriptions
const white = '\x1b[37m';
const brightWhite = '\x1b[97m';
const cyan = '\x1b[36m';
const purple = '\x1b[35m';
const green = '\x1b[32m';
const yellow = '\x1b[33m';
const red = '\x1b[31m';

function ask(question, defaultValue = '') {
    const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout,
    });

    const displayPrompt = defaultValue
        ? `  ${cyan}?${reset} ${bold}${question}${reset} ${dim}(${defaultValue})${reset}: `
        : `  ${cyan}?${reset} ${bold}${question}${reset}: `;

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
    console.log(`${purple}${bold}  ▲ Veldora Framework${reset} ${dim}v0.4.0-beta.13${reset}`);
    console.log(`${dim}  PHP 8.2+ MVC framework — routing, auth, ORM, CLI, queues, mail, UI components.${reset}`);
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
        console.log(`    $ ${cyan}veldora${reset} ${brightWhite}[project-name]${reset}`);
        console.log(`    $ ${cyan}veldora new${reset} ${brightWhite}[project-name]${reset}`);
        console.log(`    $ ${cyan}npx create-veldora-app${reset} ${brightWhite}[project-name]${reset}`);
        console.log();
        console.log(`  ${bold}Project Creation:${reset}`);
        console.log(`    ${cyan}veldora [name]${reset}               ${dim}Scaffold a new Veldora project${reset}`);
        console.log(`    ${cyan}veldora new [name]${reset}           ${dim}Scaffold a new Veldora project${reset}`);
        console.log(`    ${cyan}veldora help${reset}                 ${dim}Display this guide${reset}`);
        console.log(`    ${cyan}veldora version${reset}              ${dim}Display installed version${reset}`);
        console.log();
        console.log(`  ${bold}Framework Commands ${dim}(inside your project)${reset}${bold}:${reset}`);
        console.log(`    ${purple}php veldora serve${reset}            ${dim}Start local development server on http://localhost:8000${reset}`);
        console.log(`    ${purple}php veldora make:controller${reset}  ${dim}Scaffold an HTTP Controller (e.g. ${brightWhite}make:controller PostController${dim})${reset}`);
        console.log(`    ${purple}php veldora make:model${reset}       ${dim}Scaffold an ActiveRecord Model (e.g. ${brightWhite}make:model Post${dim})${reset}`);
        console.log(`    ${purple}php veldora make:migration${reset}   ${dim}Create a new database migration file${reset}`);
        console.log(`    ${purple}php veldora make:auth${reset}        ${dim}Scaffold full authentication (Login, Register, Dashboard)${reset}`);
        console.log();
        console.log(`  ${bold}Database & Migrations:${reset}`);
        console.log(`    ${yellow}php veldora migrate${reset}          ${dim}Run all pending migrations${reset}`);
        console.log(`    ${yellow}php veldora migrate:rollback${reset} ${dim}Rollback the last migration batch${reset}`);
        console.log(`    ${yellow}php veldora migrate:fresh${reset}    ${dim}Drop all tables and re-run migrations${reset}`);
        console.log();
        console.log(`  ${bold}UI Component System ${dim}(21 Components)${reset}${bold}:${reset}`);
        console.log(`    ${green}php veldora ui:list${reset}          ${dim}List all 21 available UI components${reset}`);
        console.log(`    ${green}php veldora add [components]${reset} ${dim}Copy components into views/components (e.g. ${brightWhite}add button modal tabs${dim})${reset}`);
        console.log();
        console.log(`  ${bold}Quick Start Workflow:${reset}`);
        console.log(`    ${dim}1.${reset} ${cyan}veldora my-app${reset}`);
        console.log(`    ${dim}2.${reset} ${cyan}cd my-app${reset}`);
        console.log(`    ${dim}3.${reset} ${cyan}php veldora serve${reset}`);
        console.log();
        console.log(`  ${dim}Documentation: https://veldora.modrao.com • Author: Shahriyar Fahim${reset}\n`);
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
        console.log(`  ${brightWhite}v0.4.0-beta.13${reset}\n`);
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
        console.log(`\n  ${red}✖ Directory ${brightWhite}${projectName}${red} is not empty.${reset} Please choose another name or directory.\n`);
        process.exit(1);
    }

    console.log(`  ${cyan}Creating a new Veldora app in${reset} ${brightWhite}${bold}${projectPath}${reset}...\n`);

    const templateDir = path.resolve(__dirname, '../template');

    if (!fs.existsSync(templateDir)) {
        console.log(`  ${red}✖ Error: Template directory not found at ${templateDir}${reset}`);
        process.exit(1);
    }

    // 1. Copy template files
    const appKey = generateAppKey();
    const replacements = {
        '{{APP_NAME}}': projectName,
        'veldora/app': `${projectName}/app`,
    };

    copyRecursiveSync(templateDir, projectPath, replacements);

    // 2. Ensure both .env and .env.example exist with correct configuration
    const envPath = path.join(projectPath, '.env');
    const envExamplePath = path.join(projectPath, '.env.example');

    // Base template content from .env.example or .env
    let baseEnvContent = '';
    if (fs.existsSync(envExamplePath)) {
        baseEnvContent = fs.readFileSync(envExamplePath, 'utf-8');
    } else if (fs.existsSync(envPath)) {
        baseEnvContent = fs.readFileSync(envPath, 'utf-8');
    }

    if (baseEnvContent) {
        // Write .env with generated APP_KEY and APP_NAME
        let envContent = baseEnvContent;
        envContent = envContent.replace(/APP_NAME\s*=.*/g, `APP_NAME="${projectName}"`);
        envContent = envContent.replace(/APP_KEY\s*=.*/g, `APP_KEY=${appKey}`);
        fs.writeFileSync(envPath, envContent, 'utf-8');

        // Write .env.example with empty APP_KEY
        let exampleContent = baseEnvContent;
        exampleContent = exampleContent.replace(/APP_NAME\s*=.*/g, `APP_NAME="${projectName}"`);
        exampleContent = exampleContent.replace(/APP_KEY\s*=.*/g, 'APP_KEY=');
        fs.writeFileSync(envExamplePath, exampleContent, 'utf-8');
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
        process.stdout.write(`  ${dim}Installing dependencies via Composer...${reset} `);
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
    console.log(`  ${green}${bold}🎉 Success!${reset} Created ${brightWhite}${bold}${projectName}${reset} at ${dim}${projectPath}${reset}`);
    console.log();
    console.log(`  ${bold}Inside your new project, you can run:${reset}`);
    console.log();
    console.log(`    ${cyan}php veldora serve${reset}`);
    console.log(`    ${dim}Starts the local development server at http://localhost:8000${reset}`);
    console.log();
    console.log(`    ${cyan}php veldora make:controller${reset} ${brightWhite}PostController${reset}`);
    console.log(`    ${dim}Scaffolds a new HTTP controller${reset}`);
    console.log();
    console.log(`    ${cyan}php veldora add${reset} ${brightWhite}button card modal tabs${reset}`);
    console.log(`    ${dim}Copies production-ready UI components into resources/views/components/${reset}`);
    console.log();
    console.log(`  ${bold}To get started:${reset}`);
    console.log();
    if (path.relative(process.cwd(), projectPath) !== '') {
        console.log(`    ${purple}cd${reset} ${brightWhite}${targetDir}${reset}`);
    }
    if (!composerSuccess) {
        console.log(`    ${purple}composer install${reset}`);
    }
    console.log(`    ${purple}php veldora serve${reset}`);
    console.log();
    console.log(`  ${dim}Documentation: https://veldora.modrao.com • Author: Shahriyar Fahim${reset}\n`);
}

main().catch(err => {
    console.error(`\n  ${red}Error:${reset}`, err.message);
    process.exit(1);
});
