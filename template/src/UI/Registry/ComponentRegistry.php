<?php

namespace Veldora\UI\Registry;

class ComponentRegistry
{
    /** @var array<string, array{description: string, usage: string, template: string}> */
    private array $components;

    public function __construct()
    {
        $this->components = $this->loadComponents();
    }

    public function has(string $name): bool
    {
        return isset($this->components[$name]);
    }

    /**
     * @return array{description: string, usage: string, template: string}|null
     */
    public function get(string $name): ?array
    {
        return $this->components[$name] ?? null;
    }

    /**
     * @return array<string, array{description: string, usage: string, template: string}>
     */
    public function all(): array
    {
        return $this->components;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Component definitions
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array<string, array{description: string, usage: string, template: string}>
     */
    private function loadComponents(): array
    {
        return [
            'button'   => $this->button(),
            'input'    => $this->input(),
            'textarea' => $this->textarea(),
            'select'   => $this->select(),
            'checkbox' => $this->checkbox(),
            'radio'    => $this->radio(),
            'badge'    => $this->badge(),
            'alert'    => $this->alert(),
            'card'     => $this->card(),
            'modal'    => $this->modal(),
            'spinner'  => $this->spinner(),
            'avatar'   => $this->avatar(),
            'dropdown'   => $this->dropdown(),
            'navbar'     => $this->navbar(),
            'toast'      => $this->toast(),
            'tabs'       => $this->tabs(),
            'accordion'  => $this->accordion(),
            'progress'   => $this->progress(),
            'tooltip'    => $this->tooltip(),
            'breadcrumb' => $this->breadcrumb(),
            'table'      => $this->table(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function button(): array
    {
        return [
            'description' => 'Clickable button — variant, size, disabled support',
            'usage'       => '<x-button variant="primary" size="md">Click me</x-button>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Button Component
// Props: variant (primary|secondary|ghost|danger), size (sm|md|lg), disabled (bool), type (button|submit|reset)
$variant  = $variant  ?? 'primary';
$size     = $size     ?? 'md';
$disabled = $disabled ?? false;
$type     = $type     ?? 'button';

$variants = [
    'primary'   => 'vui-btn vui-btn-primary',
    'secondary' => 'vui-btn vui-btn-secondary',
    'ghost'     => 'vui-btn vui-btn-ghost',
    'danger'    => 'vui-btn vui-btn-danger',
];
$sizes = [
    'sm' => 'vui-btn-sm',
    'md' => 'vui-btn-md',
    'lg' => 'vui-btn-lg',
];

$classes  = ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
$disabledAttr = $disabled ? 'disabled aria-disabled="true"' : '';
?>
<button type="<?= htmlspecialchars($type) ?>" class="<?= $classes ?>" <?= $disabledAttr ?>>
    <?= $slot ?>
</button>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function input(): array
    {
        return [
            'description' => 'Text input with label, error, and helper text support',
            'usage'       => '<x-input name="email" label="Email Address" type="email" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Input Component
// Props: name, label, type, value, placeholder, error, helper, required, disabled, id
$type        = $type        ?? 'text';
$value       = $value       ?? '';
$placeholder = $placeholder ?? '';
$required    = $required    ?? false;
$disabled    = $disabled    ?? false;
$error       = $error       ?? null;
$helper      = $helper      ?? null;
$id          = $id          ?? ($name ?? 'input_' . uniqid());
$label       = $label       ?? null;

$inputClass  = 'vui-input' . ($error ? ' vui-input-error' : '');
$requiredAttr = $required ? 'required' : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <input
        id="<?= htmlspecialchars($id) ?>"
        type="<?= htmlspecialchars($type) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        value="<?= htmlspecialchars($value) ?>"
        placeholder="<?= htmlspecialchars($placeholder) ?>"
        class="<?= $inputClass ?>"
        <?= $requiredAttr ?> <?= $disabledAttr ?>
        <?php if ($error): ?>aria-invalid="true" aria-describedby="<?= $id ?>-error"<?php endif; ?>
    >

    <?php if ($error): ?>
        <p id="<?= $id ?>-error" class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($helper): ?>
        <p class="vui-field-helper"><?= htmlspecialchars($helper) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function textarea(): array
    {
        return [
            'description' => 'Multi-line text area with label and error support',
            'usage'       => '<x-textarea name="bio" label="About You" rows="4" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Textarea Component
// Props: name, label, rows, placeholder, error, helper, required, disabled, id
$rows        = $rows        ?? 4;
$placeholder = $placeholder ?? '';
$required    = $required    ?? false;
$disabled    = $disabled    ?? false;
$error       = $error       ?? null;
$helper      = $helper      ?? null;
$id          = $id          ?? ($name ?? 'textarea_' . uniqid());
$label       = $label       ?? null;
$content     = $slot        ?? '';

$areaClass    = 'vui-textarea' . ($error ? ' vui-input-error' : '');
$requiredAttr = $required ? 'required' : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <textarea
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        rows="<?= (int) $rows ?>"
        placeholder="<?= htmlspecialchars($placeholder) ?>"
        class="<?= $areaClass ?>"
        <?= $requiredAttr ?> <?= $disabledAttr ?>
        <?php if ($error): ?>aria-invalid="true" aria-describedby="<?= $id ?>-error"<?php endif; ?>
    ><?= htmlspecialchars($content) ?></textarea>

    <?php if ($error): ?>
        <p id="<?= $id ?>-error" class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($helper): ?>
        <p class="vui-field-helper"><?= htmlspecialchars($helper) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function select(): array
    {
        return [
            'description' => 'Dropdown select with label, options array, and error',
            'usage'       => '<x-select name="role" label="Role" :options="$roles" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Select Component
// Props: name, label, options (assoc or indexed), selected, placeholder, error, required, disabled, id
$options     = $options     ?? [];
$selected    = $selected    ?? '';
$placeholder = $placeholder ?? 'Select an option';
$required    = $required    ?? false;
$disabled    = $disabled    ?? false;
$error       = $error       ?? null;
$id          = $id          ?? ($name ?? 'select_' . uniqid());
$label       = $label       ?? null;

$selectClass  = 'vui-select' . ($error ? ' vui-input-error' : '');
$requiredAttr = $required ? 'required' : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-field">
    <?php if ($label): ?>
        <label class="vui-label" for="<?= htmlspecialchars($id) ?>">
            <?= htmlspecialchars($label) ?>
            <?php if ($required): ?><span class="vui-required" aria-hidden="true">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <select
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        class="<?= $selectClass ?>"
        <?= $requiredAttr ?> <?= $disabledAttr ?>
        <?php if ($error): ?>aria-invalid="true"<?php endif; ?>
    >
        <?php if ($placeholder): ?>
            <option value="" disabled <?= $selected === '' ? 'selected' : '' ?>><?= htmlspecialchars($placeholder) ?></option>
        <?php endif; ?>
        <?php foreach ($options as $val => $label): ?>
            <option value="<?= htmlspecialchars((string) $val) ?>" <?= (string) $val === (string) $selected ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) $label) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($error): ?>
        <p class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function checkbox(): array
    {
        return [
            'description' => 'Checkbox input with label and checked state',
            'usage'       => '<x-checkbox name="agree" label="I agree to the terms" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Checkbox Component
// Props: name, label, value, checked, disabled, error, id
$value    = $value    ?? '1';
$checked  = $checked  ?? false;
$disabled = $disabled ?? false;
$error    = $error    ?? null;
$id       = $id       ?? ($name ?? 'checkbox_' . uniqid());
$label    = $label    ?? null;

$checkedAttr  = $checked  ? 'checked'   : '';
$disabledAttr = $disabled ? 'disabled'  : '';
?>
<div class="vui-checkbox-wrap">
    <input
        type="checkbox"
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        value="<?= htmlspecialchars($value) ?>"
        class="vui-checkbox"
        <?= $checkedAttr ?> <?= $disabledAttr ?>
    >
    <?php if ($label): ?>
        <label class="vui-checkbox-label" for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?></label>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="vui-field-error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function radio(): array
    {
        return [
            'description' => 'Radio input with label and checked state',
            'usage'       => '<x-radio name="gender" value="male" label="Male" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Radio Component
// Props: name, label, value, checked, disabled, id
$checked  = $checked  ?? false;
$disabled = $disabled ?? false;
$id       = $id       ?? ($name ?? 'radio') . '_' . ($value ?? uniqid());
$label    = $label    ?? null;

$checkedAttr  = $checked  ? 'checked'  : '';
$disabledAttr = $disabled ? 'disabled' : '';
?>
<div class="vui-radio-wrap">
    <input
        type="radio"
        id="<?= htmlspecialchars($id) ?>"
        name="<?= htmlspecialchars($name ?? '') ?>"
        value="<?= htmlspecialchars($value ?? '') ?>"
        class="vui-radio"
        <?= $checkedAttr ?> <?= $disabledAttr ?>
    >
    <?php if ($label): ?>
        <label class="vui-radio-label" for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($label) ?></label>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function badge(): array
    {
        return [
            'description' => 'Inline status badge — variant and dot indicator support',
            'usage'       => '<x-badge variant="success">Active</x-badge>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Badge Component
// Props: variant (default|success|warning|danger|info|purple), dot (bool)
$variant = $variant ?? 'default';
$dot     = $dot     ?? false;

$variants = [
    'default' => 'vui-badge vui-badge-default',
    'success' => 'vui-badge vui-badge-success',
    'warning' => 'vui-badge vui-badge-warning',
    'danger'  => 'vui-badge vui-badge-danger',
    'info'    => 'vui-badge vui-badge-info',
    'purple'  => 'vui-badge vui-badge-purple',
];
$class = $variants[$variant] ?? $variants['default'];
?>
<span class="<?= $class ?>">
    <?php if ($dot): ?><span class="vui-badge-dot" aria-hidden="true"></span><?php endif; ?>
    <?= $slot ?>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function alert(): array
    {
        return [
            'description' => 'Alert box — success, warning, danger, info variants',
            'usage'       => '<x-alert variant="success" title="Done!">Record saved.</x-alert>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Alert Component
// Props: variant (success|warning|danger|info), title, dismissible
$variant     = $variant     ?? 'info';
$title       = $title       ?? null;
$dismissible = $dismissible ?? false;

$icons = [
    'success' => '✓',
    'warning' => '⚠',
    'danger'  => '✕',
    'info'    => 'ℹ',
];

$variants = [
    'success' => 'vui-alert vui-alert-success',
    'warning' => 'vui-alert vui-alert-warning',
    'danger'  => 'vui-alert vui-alert-danger',
    'info'    => 'vui-alert vui-alert-info',
];

$class = $variants[$variant] ?? $variants['info'];
$icon  = $icons[$variant]    ?? $icons['info'];
?>
<div class="<?= $class ?>" role="alert">
    <span class="vui-alert-icon" aria-hidden="true"><?= $icon ?></span>
    <div class="vui-alert-body">
        <?php if ($title): ?>
            <p class="vui-alert-title"><?= htmlspecialchars($title) ?></p>
        <?php endif; ?>
        <p class="vui-alert-message"><?= $slot ?></p>
    </div>
    <?php if ($dismissible): ?>
        <button type="button" class="vui-alert-close" onclick="this.closest('.vui-alert').remove()" aria-label="Dismiss">✕</button>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function card(): array
    {
        return [
            'description' => 'Content card with optional header, footer, and padding',
            'usage'       => '<x-card title="My Card"><p>Content here</p></x-card>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Card Component
// Props: title, subtitle, padding (bool, default true)
$title    = $title    ?? null;
$subtitle = $subtitle ?? null;
$padding  = $padding  ?? true;
$class    = 'vui-card' . (!$padding ? ' vui-card-flush' : '');
?>
<div class="<?= $class ?>">
    <?php if ($title || $subtitle): ?>
        <div class="vui-card-header">
            <?php if ($title): ?>
                <h3 class="vui-card-title"><?= htmlspecialchars($title) ?></h3>
            <?php endif; ?>
            <?php if ($subtitle): ?>
                <p class="vui-card-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="vui-card-body">
        <?= $slot ?>
    </div>

    <?php if (isset($footer)): ?>
        <div class="vui-card-footer"><?= $footer ?></div>
    <?php endif; ?>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function modal(): array
    {
        return [
            'description' => 'Accessible dialog modal with open/close JS',
            'usage'       => '<x-modal id="confirm-modal" title="Confirm Action">...</x-modal>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Modal Component
// Props: id (required), title, size (sm|md|lg|xl)
$id    = $id    ?? 'vui-modal-' . uniqid();
$title = $title ?? null;
$size  = $size  ?? 'md';

$sizes = [
    'sm' => 'vui-modal-sm',
    'md' => 'vui-modal-md',
    'lg' => 'vui-modal-lg',
    'xl' => 'vui-modal-xl',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
?>
<div id="<?= htmlspecialchars($id) ?>" class="vui-modal-overlay" role="dialog" aria-modal="true" aria-hidden="true" <?php if ($title): ?>aria-labelledby="<?= $id ?>-title"<?php endif; ?>>
    <div class="vui-modal-container <?= $sizeClass ?>">
        <div class="vui-modal-header">
            <?php if ($title): ?>
                <h2 id="<?= $id ?>-title" class="vui-modal-title"><?= htmlspecialchars($title) ?></h2>
            <?php endif; ?>
            <button type="button" class="vui-modal-close" onclick="document.getElementById('<?= $id ?>').setAttribute('aria-hidden','true')" aria-label="Close">✕</button>
        </div>
        <div class="vui-modal-body">
            <?= $slot ?>
        </div>
        <?php if (isset($footer)): ?>
            <div class="vui-modal-footer"><?= $footer ?></div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('<?= $id ?>');
    if (!modal) return;
    // Close on overlay click
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.setAttribute('aria-hidden', 'true');
    });
    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') modal.setAttribute('aria-hidden', 'true');
    });
})();
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function spinner(): array
    {
        return [
            'description' => 'Animated loading spinner with size variants',
            'usage'       => '<x-spinner size="md" label="Loading..." />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Spinner Component
// Props: size (sm|md|lg), label (screen-reader text)
$size  = $size  ?? 'md';
$label = $label ?? 'Loading...';

$sizes = ['sm' => 'vui-spinner-sm', 'md' => 'vui-spinner-md', 'lg' => 'vui-spinner-lg'];
$class = 'vui-spinner ' . ($sizes[$size] ?? $sizes['md']);
?>
<span class="<?= $class ?>" role="status" aria-label="<?= htmlspecialchars($label) ?>">
    <span class="vui-spinner-ring"></span>
    <span class="vui-sr-only"><?= htmlspecialchars($label) ?></span>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function avatar(): array
    {
        return [
            'description' => 'User avatar — image or initials fallback, size variants',
            'usage'       => '<x-avatar src="/img/user.jpg" name="Jane Doe" size="md" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Avatar Component
// Props: src, name, size (xs|sm|md|lg|xl), shape (circle|square)
$src   = $src   ?? null;
$name  = $name  ?? '';
$size  = $size  ?? 'md';
$shape = $shape ?? 'circle';

$sizes  = ['xs' => 'vui-avatar-xs', 'sm' => 'vui-avatar-sm', 'md' => 'vui-avatar-md', 'lg' => 'vui-avatar-lg', 'xl' => 'vui-avatar-xl'];
$shapes = ['circle' => 'vui-avatar-circle', 'square' => 'vui-avatar-square'];
$class  = 'vui-avatar ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($shapes[$shape] ?? $shapes['circle']);

// Generate initials from name
$initials = '';
if ($name) {
    $parts = explode(' ', trim($name));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
}
?>
<span class="<?= $class ?>" aria-label="<?= htmlspecialchars($name) ?>">
    <?php if ($src): ?>
        <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($name) ?>" class="vui-avatar-img">
    <?php else: ?>
        <span class="vui-avatar-initials" aria-hidden="true"><?= htmlspecialchars($initials) ?></span>
    <?php endif; ?>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function dropdown(): array
    {
        return [
            'description' => 'Click-triggered dropdown menu with item slot',
            'usage'       => '<x-dropdown label="Options">...</x-dropdown>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Dropdown Component
// Props: label, align (left|right), id
$label = $label ?? 'Options';
$align = $align ?? 'left';
$id    = $id    ?? 'vui-dropdown-' . uniqid();

$menuClass = 'vui-dropdown-menu' . ($align === 'right' ? ' vui-dropdown-right' : '');
?>
<div class="vui-dropdown" id="<?= htmlspecialchars($id) ?>">
    <button
        type="button"
        class="vui-dropdown-trigger"
        aria-haspopup="true"
        aria-expanded="false"
        onclick="(function(el){var open=el.getAttribute('aria-expanded')==='true';el.setAttribute('aria-expanded',!open);el.nextElementSibling.classList.toggle('vui-dropdown-open',!open);})(this)"
    >
        <?= htmlspecialchars($label) ?>
        <span class="vui-dropdown-caret" aria-hidden="true">▾</span>
    </button>
    <ul class="<?= $menuClass ?>" role="menu">
        <?= $slot ?>
    </ul>
</div>

<script>
document.addEventListener('click', function(e) {
    var d = document.getElementById('<?= $id ?>');
    if (d && !d.contains(e.target)) {
        var btn = d.querySelector('.vui-dropdown-trigger');
        var menu = d.querySelector('.vui-dropdown-menu');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (menu) menu.classList.remove('vui-dropdown-open');
    }
});
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function navbar(): array
    {
        return [
            'description' => 'Responsive top navigation bar with brand and slot',
            'usage'       => '<x-navbar brand="Veldora">...</x-navbar>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Navbar Component
// Props: brand (text or HTML), brandHref, sticky (bool)
$brand     = $brand     ?? '';
$brandHref = $brandHref ?? '/';
$sticky    = $sticky    ?? false;
$class     = 'vui-navbar' . ($sticky ? ' vui-navbar-sticky' : '');
$navId     = 'vui-nav-' . substr(md5($brand), 0, 6);
?>
<nav class="<?= $class ?>" role="navigation" aria-label="Main navigation">
    <div class="vui-navbar-inner">
        <a href="<?= htmlspecialchars($brandHref) ?>" class="vui-navbar-brand">
            <?= $brand ?>
        </a>

        <button
            type="button"
            class="vui-navbar-toggle"
            aria-controls="<?= $navId ?>"
            aria-expanded="false"
            onclick="(function(btn){var open=btn.getAttribute('aria-expanded')==='true';btn.setAttribute('aria-expanded',!open);document.getElementById('<?= $navId ?>').classList.toggle('vui-navbar-open',!open);})(this)"
            aria-label="Toggle navigation"
        >
            <span class="vui-navbar-burger"></span>
        </button>

        <div id="<?= $navId ?>" class="vui-navbar-menu">
            <?= $slot ?>
        </div>
    </div>
</nav>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function toast(): array
    {
        return [
            'description' => 'Auto-dismissing toast notification, JS-driven',
            'usage'       => '<x-toast id="save-toast" variant="success" message="Saved!" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Toast Component
// Props: id, variant (success|warning|danger|info), message, duration (ms, default 3500)
$id       = $id       ?? 'vui-toast-' . uniqid();
$variant  = $variant  ?? 'info';
$message  = $message  ?? ($slot ?? '');
$duration = $duration ?? 3500;

$icons    = ['success' => '✓', 'warning' => '⚠', 'danger' => '✕', 'info' => 'ℹ'];
$variants = ['success' => 'vui-toast-success', 'warning' => 'vui-toast-warning', 'danger' => 'vui-toast-danger', 'info' => 'vui-toast-info'];
$class    = 'vui-toast ' . ($variants[$variant] ?? $variants['info']);
$icon     = $icons[$variant] ?? $icons['info'];
?>
<div id="<?= htmlspecialchars($id) ?>" class="<?= $class ?>" role="status" aria-live="polite" aria-atomic="true">
    <span class="vui-toast-icon" aria-hidden="true"><?= $icon ?></span>
    <span class="vui-toast-message"><?= htmlspecialchars($message) ?></span>
    <button type="button" class="vui-toast-close" onclick="document.getElementById('<?= $id ?>').remove()" aria-label="Dismiss">✕</button>
</div>

<script>
(function () {
    var el = document.getElementById('<?= htmlspecialchars($id) ?>');
    if (!el) return;
    setTimeout(function () {
        el.classList.add('vui-toast-fade');
        setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 400);
    }, <?= (int) $duration ?>);
})();
</script>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function tabs(): array
    {
        return [
            'description' => 'Interactive tabbed navigation with animated active pills and content panels',
            'usage'       => '<x-tabs id="profile-tabs" :tabs="[\'account\' => \'Account\', \'security\' => \'Security\']"> ... </x-tabs>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Tabs Component
// Props: id, tabs (array: key => Label), active (default first key)
$tabId  = $id ?? 'vui-tabs-' . uniqid();
$tabs   = $tabs ?? [];
$active = $active ?? (!empty($tabs) ? array_key_first($tabs) : '');
?>
<div id="<?= htmlspecialchars($tabId) ?>" class="vui-tabs-container">
    <div class="vui-tabs-list" role="tablist" aria-label="Tabs navigation">
        <?php foreach ($tabs as $key => $label): ?>
            <?php $isActive = ($key === $active); ?>
            <button
                type="button"
                role="tab"
                class="vui-tab-btn <?= $isActive ? 'vui-tab-active' : '' ?>"
                id="tab-btn-<?= htmlspecialchars($tabId . '-' . $key) ?>"
                aria-controls="tab-pane-<?= htmlspecialchars($tabId . '-' . $key) ?>"
                aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                onclick="(function(btn){
                    var root = document.getElementById('<?= htmlspecialchars($tabId) ?>');
                    root.querySelectorAll('.vui-tab-btn').forEach(function(b){ b.classList.remove('vui-tab-active'); b.setAttribute('aria-selected','false'); });
                    root.querySelectorAll('.vui-tab-pane').forEach(function(p){ p.classList.remove('vui-tab-pane-active'); });
                    btn.classList.add('vui-tab-active');
                    btn.setAttribute('aria-selected','true');
                    var target = document.getElementById('tab-pane-<?= htmlspecialchars($tabId . '-') ?>' + '<?= htmlspecialchars($key) ?>');
                    if(target) target.classList.add('vui-tab-pane-active');
                })(this)"
            >
                <?= htmlspecialchars($label) ?>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="vui-tabs-content">
        <?= $slot ?? '' ?>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function accordion(): array
    {
        return [
            'description' => 'Smooth collapsible accordion / disclosure card with chevron toggle',
            'usage'       => '<x-accordion title="What is Veldora?" :open="true"> ... </x-accordion>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Accordion Component
// Props: id, title, open (bool, default false)
$accId  = $id ?? 'vui-acc-' . uniqid();
$title  = $title ?? 'Accordion Title';
$isOpen = !empty($open);
?>
<div id="<?= htmlspecialchars($accId) ?>" class="vui-accordion <?= $isOpen ? 'vui-accordion-open' : '' ?>">
    <button
        type="button"
        class="vui-accordion-header"
        aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
        onclick="(function(btn){
            var item = document.getElementById('<?= htmlspecialchars($accId) ?>');
            var open = item.classList.toggle('vui-accordion-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        })(this)"
    >
        <span class="vui-accordion-title"><?= htmlspecialchars($title) ?></span>
        <svg class="vui-accordion-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>
    <div class="vui-accordion-body" role="region">
        <div class="vui-accordion-inner">
            <?= $slot ?? '' ?>
        </div>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function progress(): array
    {
        return [
            'description' => 'Visual progress bar with color variants, striped gradient, and percentage label',
            'usage'       => '<x-progress :value="75" variant="primary" :striped="true" :showLabel="true" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Progress Component
// Props: value (0-100), max (default 100), variant (primary|success|warning|danger), size (sm|md|lg), striped (bool), animated (bool), showLabel (bool)
$val       = (int) ($value ?? 0);
$maxVal    = (int) ($max ?? 100);
$pct       = $maxVal > 0 ? min(100, max(0, round(($val / $maxVal) * 100))) : 0;
$variant   = $variant ?? 'primary';
$size      = $size ?? 'md';
$isStriped = !empty($striped);
$isAnim    = !empty($animated);
$showLbl   = !empty($showLabel);

$barClasses = 'vui-progress-bar vui-progress-' . htmlspecialchars($variant);
if ($isStriped) $barClasses .= ' vui-progress-striped';
if ($isAnim)    $barClasses .= ' vui-progress-animated';
?>
<div class="vui-progress vui-progress-<?= htmlspecialchars($size) ?>" role="progressbar" aria-valuenow="<?= $val ?>" aria-valuemin="0" aria-valuemax="<?= $maxVal ?>" aria-label="<?= $showLbl ? $pct . '%' : 'Progress bar' ?>">
    <div class="<?= $barClasses ?>" style="width: <?= $pct ?>%;">
        <?php if ($showLbl): ?>
            <span class="vui-progress-label"><?= $pct ?>%</span>
        <?php endif; ?>
    </div>
</div>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function tooltip(): array
    {
        return [
            'description' => 'Contextual hover tooltip bubble with smooth arrow positioning',
            'usage'       => '<x-tooltip text="Copied to clipboard!" position="top"><x-button>Hover me</x-button></x-tooltip>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Tooltip Component
// Props: text, position (top|bottom|left|right, default top)
$text = $text ?? '';
$pos  = $position ?? 'top';
?>
<span class="vui-tooltip-wrapper vui-tooltip-<?= htmlspecialchars($pos) ?>">
    <?= $slot ?? '' ?>
    <span class="vui-tooltip-bubble" role="tooltip" aria-hidden="true">
        <?= htmlspecialchars($text) ?>
    </span>
</span>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function breadcrumb(): array
    {
        return [
            'description' => 'Navigation trail breadcrumb with SVG chevron separators',
            'usage'       => '<x-breadcrumb :items="[[\'label\' => \'Home\', \'href\' => \'/\'], [\'label\' => \'Docs\', \'href\' => \'/docs\'], [\'label\' => \'Buttons\']]" />',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Breadcrumb Component
// Props: items (array of ['label' => string, 'href' => ?string])
$items = $items ?? [];
?>
<nav class="vui-breadcrumb" aria-label="Breadcrumb">
    <ol class="vui-breadcrumb-list">
        <?php foreach ($items as $idx => $item): ?>
            <?php $isLast = ($idx === count($items) - 1); ?>
            <li class="vui-breadcrumb-item <?= $isLast ? 'vui-breadcrumb-active' : '' ?>">
                <?php if (!$isLast && !empty($item['href'])): ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>" class="vui-breadcrumb-link"><?= htmlspecialchars($item['label']) ?></a>
                    <svg class="vui-breadcrumb-sep" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                <?php else: ?>
                    <span aria-current="page"><?= htmlspecialchars($item['label']) ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
TEMPLATE,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @return array{description: string, usage: string, template: string}
     */
    private function table(): array
    {
        return [
            'description' => 'Modern responsive data table with zebra striping, border styling, and hover elevation',
            'usage'       => '<x-table :striped="true" :hover="true"> <thead>...</thead> <tbody>...</tbody> </x-table>',
            'template'    => <<<'TEMPLATE'
<?php
// Veldora UI — Table Component
// Props: striped (bool), hover (bool), bordered (bool), compact (bool)
$isStriped  = !empty($striped);
$isHover    = !empty($hover);
$isBordered = !empty($bordered);
$isCompact  = !empty($compact);

$tableClasses = 'vui-table';
if ($isStriped)  $tableClasses .= ' vui-table-striped';
if ($isHover)    $tableClasses .= ' vui-table-hover';
if ($isBordered) $tableClasses .= ' vui-table-bordered';
if ($isCompact)  $tableClasses .= ' vui-table-compact';
?>
<div class="vui-table-responsive">
    <table class="<?= $tableClasses ?>">
        <?= $slot ?? '' ?>
    </table>
</div>
TEMPLATE,
        ];
    }
}
