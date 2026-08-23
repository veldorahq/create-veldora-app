<div align="center">

<img src="assets/v-icon.png" width="80" height="80" alt="Veldora Logo">

# create-veldora-app

**Scaffold modern Veldora PHP applications in seconds with zero configuration.**

[![npm version](https://img.shields.io/npm/v/create-veldora-app?style=flat-square&logo=npm&color=CB3837)](https://www.npmjs.com/package/create-veldora-app)
[![npm downloads](https://img.shields.io/npm/dt/create-veldora-app?style=flat-square&logo=npm)](https://www.npmjs.com/package/create-veldora-app)
[![License: MIT](https://img.shields.io/badge/License-MIT-8b5cf6?style=flat-square)](LICENSE)
[![Docs](https://img.shields.io/badge/Documentation-veldora.modrao.com-10B981?style=flat-square)](https://veldora.modrao.com)

</div>

---

## 🚀 Quick Start

Create a new Veldora app using `npx`, `npm init`, `yarn create`, or `pnpm create`:

```bash
# Using npx (Recommended)
npx create-veldora-app my-app

# Using npm init
npm init veldora-app my-app

# Using yarn
yarn create veldora-app my-app

# Using pnpm
pnpm create veldora-app my-app
```

Then start the built-in development server:

```bash
cd my-app
php veldora serve
```

---

## ⚡ What's Included?

- **Zero-Composer Bootstrap** — Includes a built-in PSR-4 autoloader so projects run instantly out of the box.
- **41+ Built-in CLI Commands** — Full code generation (`make:*`), database migration manager (`migrate:*`), diagnostics (`doctor`, `about`), and optimization (`optimize`).
- **Pre-styled UI Components** — Ready-to-use button, badge, alert, and card components.
- **Ignition-style Developer Debug Page** — Interactive dark error screens with line-highlighted source preview and one-click stack trace copy.
- **Modern MVC Routing & Pipeline** — Flexible middleware pipeline, CSRF verification, and signed URL support.

---

## 🛠️ CLI Cheat Sheet

```bash
php veldora serve                    # Start local development server
php veldora doctor                   # Run system health diagnostics
php veldora about                    # Show environment & database info
php veldora make:controller PostController
php veldora make:model Post -m       # Create model + migration
php veldora make:auth                # Complete authentication scaffolding
php veldora add button card modal    # Add UI components into views
php veldora optimize                 # Cache config, routes, and views
```

---

## 📄 License & Author

- **Author**: Shahriyar Fahim
- **License**: [MIT](LICENSE)
- **Website**: [https://veldora.modrao.com](https://veldora.modrao.com) *(temporary — permanent domain coming soon)*
