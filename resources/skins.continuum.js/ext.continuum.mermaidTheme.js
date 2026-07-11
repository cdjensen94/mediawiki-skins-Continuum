// resources/ext.continuum.mermaidTheme.js
mw.loader.using('ext.mermaid').then(function () {
    // Mermaid is on window.mermaid
    const css = getComputedStyle(document.documentElement);
    const cv = (name, fallback = null) =>
        (css.getPropertyValue(name) || '').trim() || fallback;

    // Map your skin tokens into Mermaid's themeVariables
    window.mermaid.initialize({
        startOnLoad: true,
        theme: 'base',                  // must be 'base' to fully customize
        themeVariables: {
            background: cv('--cu-surface', '#0f1115'),
            primaryColor: cv('--cu-surface-alt', '#151925'),
            primaryTextColor: cv('--cu-text', '#e6ecff'),
            lineColor: cv('--cu-border', '#2a2f3c'),
            tertiaryColor: cv('--cu-accent', '#20c997'),
            noteBkgColor: cv('--cu-surface-alt', '#151925'),
            noteTextColor: cv('--cu-text', '#e6ecff'),
            fontFamily: cv('--cu-font-sans', 'EB Garamond, Georgia, serif'),
            // optional polish:
            clusterBkg: cv('--cu-surface-alt', '#151925'),
            clusterBorder: cv('--cu-border', '#2a2f3c'),
            edgeLabelBackground: cv('--cu-surface-alt', '#151925')
        }
    });
});
