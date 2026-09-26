import './bootstrap';

const themeKey = 'devfolio-theme';
const root = document.documentElement;

try {
    if (localStorage.getItem(themeKey) === 'dark') {
        root.dataset.theme = 'dark';
    }
} catch {
    // Theme preference is optional when storage is unavailable.
}

const updateThemeLabels = () => {
    const dark = root.dataset.theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
        const icon = toggle.querySelector('[data-theme-icon]');
        const label = toggle.querySelector('[data-theme-label]');

        if (icon) icon.textContent = dark ? '☀' : '☾';
        if (label) label.textContent = dark ? 'Light mode' : 'Dark mode';
    });
};

updateThemeLabels();
document.addEventListener('click', (event) => {
    const toggle = event.target instanceof Element ? event.target.closest('[data-theme-toggle]') : null;
    if (!toggle) return;

    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    try {
        localStorage.setItem(themeKey, root.dataset.theme);
    } catch {
        // Theme changes still apply for the current page.
    }

    updateThemeLabels();
});
document.addEventListener('livewire:navigated', updateThemeLabels);

const updateCvPreview = (form) => {
    const layout = form.closest('.cv-builder-layout');
    const preview = layout?.querySelector('[data-cv-preview]');
    if (!preview) return;

    const role = form.querySelector('[data-cv-target-role]');
    const previewRole = preview.querySelector('[data-cv-preview-role]');
    if (role && previewRole) {
        previewRole.textContent = role.value.trim();
        previewRole.hidden = !role.value.trim();
    }

    const language = form.querySelector('[data-cv-language]:checked')?.value;
    if (language === 'en' || language === 'ar') {
        preview.lang = language;
        preview.dir = language === 'ar' ? 'rtl' : 'ltr';
        preview.querySelectorAll('[data-cv-label]').forEach((label) => {
            label.textContent = language === 'ar' ? label.dataset.labelAr : label.dataset.labelEn;
        });
        const languageLabel = layout.querySelector('[data-cv-preview-language]');
        if (languageLabel) languageLabel.textContent = language === 'ar' ? 'العربية' : 'English';
    }

    const template = form.querySelector('[data-cv-template]:checked')?.value;
    if (['classic', 'two-column', 'ats'].includes(template)) {
        preview.classList.remove('cv-template-classic', 'cv-template-two-column', 'cv-template-ats');
        preview.classList.add(`cv-template-${template}`);
    }

    const projects = form.querySelectorAll('[data-cv-project-toggle]');
    let selectedCount = 0;
    projects.forEach((checkbox) => {
        const project = preview.querySelector(`[data-cv-project="${checkbox.value}"]`);
        if (project) project.hidden = !checkbox.checked;
        if (checkbox.checked) selectedCount += 1;
    });
    const projectsSection = preview.querySelector('[data-cv-section="projects"]');
    if (projectsSection) projectsSection.hidden = selectedCount === 0;
    const projectCount = layout.querySelector('[data-cv-project-count]');
    if (projectCount) projectCount.textContent = `${selectedCount} selected`;
};

document.querySelectorAll('[data-cv-controls]').forEach((form) => {
    updateCvPreview(form);
    form.addEventListener('input', () => updateCvPreview(form));
    form.addEventListener('change', () => updateCvPreview(form));
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-ai-generation]')) return;

    const button = form.querySelector('button[type="submit"]');
    if (!button || button.disabled) return;

    button.disabled = true;
    button.textContent = button.dataset.loadingText || 'Generating...';
    button.setAttribute('aria-busy', 'true');
});

document.addEventListener('click', async (event) => {
    const button = event.target instanceof Element ? event.target.closest('[data-copy-url], [data-copy-text]') : null;
    if (!(button instanceof HTMLButtonElement)) return;

    const value = button.dataset.copyUrl || button.dataset.copyText;
    if (!value) return;

    try {
        await navigator.clipboard.writeText(value);
        button.textContent = button.dataset.copySuccess || 'Link copied';
    } catch {
        button.textContent = button.dataset.copyFallback || value;
        button.setAttribute('aria-label', `Copy this value manually: ${value}`);
    }
});
