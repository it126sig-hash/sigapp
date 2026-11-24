(function (window, document) {
    'use strict';

    var STORAGE_UI_KEY = 'sigapp.display.uiScale';
    var STORAGE_FONT_KEY = 'sigapp.display.fontScale';

    var UI_MIN = 0.75;
    var UI_MAX = 1.15;
    var FONT_MIN = 0.85;
    var FONT_MAX = 1.25;
    var RELAYOUT_DEBOUNCE_MS = 60;

    var DEFAULTS = Object.freeze({
        uiScale: 1,
        fontScale: 1
    });

    var currentState = {
        uiScale: DEFAULTS.uiScale,
        fontScale: DEFAULTS.fontScale
    };

    var relayoutTimer = null;
    var relayoutRaf = null;
    var controlsBound = false;

    function clampScale(value, min, max, fallback) {
        var parsed = typeof value === 'number' ? value : parseFloat(value);
        if (!isFinite(parsed)) {
            return fallback;
        }
        var clamped = Math.min(max, Math.max(min, parsed));
        return Math.round(clamped * 100) / 100;
    }

    function readStorage() {
        var ui = DEFAULTS.uiScale;
        var font = DEFAULTS.fontScale;
        try {
            if (window.localStorage) {
                var rawUi = window.localStorage.getItem(STORAGE_UI_KEY);
                var rawFont = window.localStorage.getItem(STORAGE_FONT_KEY);
                if (rawUi !== null && rawUi !== '') {
                    ui = clampScale(rawUi, UI_MIN, UI_MAX, DEFAULTS.uiScale);
                }
                if (rawFont !== null && rawFont !== '') {
                    font = clampScale(rawFont, FONT_MIN, FONT_MAX, DEFAULTS.fontScale);
                }
            }
        } catch (err) {
            // Ignore storage access errors (private browsing / restricted storage)
        }
        return { uiScale: ui, fontScale: font };
    }

    function writeStorage(uiScale, fontScale) {
        try {
            if (window.localStorage) {
                window.localStorage.setItem(STORAGE_UI_KEY, String(uiScale));
                window.localStorage.setItem(STORAGE_FONT_KEY, String(fontScale));
            }
        } catch (err) {
            // Ignore storage access errors
        }
    }

    function clearStorage() {
        try {
            if (window.localStorage) {
                window.localStorage.removeItem(STORAGE_UI_KEY);
                window.localStorage.removeItem(STORAGE_FONT_KEY);
            }
        } catch (err) {
            // Ignore storage access errors
        }
    }

    function applyRootVariables(uiScale, fontScale) {
        var root = document.documentElement;
        if (!root) {
            return;
        }

        var isDefault = Math.abs(uiScale - 1) <= 0.0001 && Math.abs(fontScale - 1) <= 0.0001;
        if (isDefault) {
            root.removeAttribute('data-sigapp-custom-scale');
            root.style.setProperty('--sigapp-ui-scale', '1');
            root.style.setProperty('--sigapp-font-scale', '1');
            root.style.setProperty('--sigapp-space-ratio', '1');
        } else {
            root.setAttribute('data-sigapp-custom-scale', '1');
            root.style.setProperty('--sigapp-ui-scale', String(uiScale));
            root.style.setProperty('--sigapp-font-scale', String(fontScale));
            root.style.setProperty('--sigapp-space-ratio', (uiScale / fontScale).toFixed(6));
        }
    }

    function performRelayout() {
        try {
            var $ = window.jQuery;
            if ($ && $.fn && $.fn.dataTable && typeof $.fn.dataTable.tables === 'function') {
                var tablesApi = $.fn.dataTable.tables({ visible: true, api: true });
                if (tablesApi && typeof tablesApi.tables === 'function') {
                    tablesApi.tables().every(function () {
                        var tableApi = this;
                        if (tableApi && tableApi.columns && typeof tableApi.columns.adjust === 'function') {
                            tableApi.columns.adjust();
                        }
                        if (tableApi && typeof tableApi.fixedColumns === 'function') {
                            var fc = tableApi.fixedColumns();
                            if (fc && typeof fc.relayout === 'function') {
                                fc.relayout();
                            }
                        }
                    });
                }
            }
        } catch (err) {
            // Ignore DataTables relayout edge errors on uninitialized tables
        }

        try {
            if (typeof window.dispatchEvent === 'function' && typeof window.Event === 'function') {
                window.dispatchEvent(new window.Event('resize'));
            } else if (window.jQuery) {
                window.jQuery(window).trigger('resize');
            }
        } catch (err) {
            // Ignore resize dispatch errors
        }
    }

    function scheduleRelayout() {
        if (relayoutTimer !== null) {
            window.clearTimeout(relayoutTimer);
            relayoutTimer = null;
        }
        if (relayoutRaf !== null && typeof window.cancelAnimationFrame === 'function') {
            window.cancelAnimationFrame(relayoutRaf);
            relayoutRaf = null;
        }

        relayoutTimer = window.setTimeout(function () {
            relayoutTimer = null;
            if (typeof window.requestAnimationFrame === 'function') {
                relayoutRaf = window.requestAnimationFrame(function () {
                    relayoutRaf = null;
                    performRelayout();
                });
            } else {
                performRelayout();
            }
        }, RELAYOUT_DEBOUNCE_MS);
    }

    function dispatchScaleChanged(uiScale, fontScale) {
        try {
            if (typeof window.CustomEvent === 'function') {
                window.dispatchEvent(new window.CustomEvent('sigapp:scale-changed', {
                    detail: {
                        uiScale: uiScale,
                        fontScale: fontScale
                    }
                }));
            }
        } catch (err) {
            // Ignore event dispatch errors
        }
    }

    function formatPercent(scale) {
        return Math.round(scale * 100) + '%';
    }

    function syncModalUI() {
        var modal = document.getElementById('sigapp-display-scale-modal');
        if (!modal) {
            return;
        }

        var uiRange = document.getElementById('sigapp-ui-scale-range');
        var fontRange = document.getElementById('sigapp-font-scale-range');
        var uiBadge = document.getElementById('sigapp-ui-scale-badge');
        var fontBadge = document.getElementById('sigapp-font-scale-badge');

        if (uiRange) {
            uiRange.value = String(currentState.uiScale);
        }
        if (fontRange) {
            fontRange.value = String(currentState.fontScale);
        }
        if (uiBadge) {
            uiBadge.textContent = formatPercent(currentState.uiScale);
        }
        if (fontBadge) {
            fontBadge.textContent = formatPercent(currentState.fontScale);
        }

        var presets = modal.querySelectorAll('.sigapp-scale-preset-btn[data-scale-target][data-scale-value]');
        for (var i = 0; i < presets.length; i++) {
            var btn = presets[i];
            var target = btn.getAttribute('data-scale-target');
            var val = parseFloat(btn.getAttribute('data-scale-value'));
            var activeVal = target === 'font' ? currentState.fontScale : currentState.uiScale;
            var isMatch = isFinite(val) && Math.abs(activeVal - val) < 0.001;
            btn.classList.toggle('is-active', isMatch);
            btn.setAttribute('aria-pressed', isMatch ? 'true' : 'false');
        }

        var uiDec = document.getElementById('sigapp-ui-scale-dec');
        var uiInc = document.getElementById('sigapp-ui-scale-inc');
        var fontDec = document.getElementById('sigapp-font-scale-dec');
        var fontInc = document.getElementById('sigapp-font-scale-inc');

        if (uiDec) {
            uiDec.disabled = currentState.uiScale <= UI_MIN + 0.0001;
        }
        if (uiInc) {
            uiInc.disabled = currentState.uiScale >= UI_MAX - 0.0001;
        }
        if (fontDec) {
            fontDec.disabled = currentState.fontScale <= FONT_MIN + 0.0001;
        }
        if (fontInc) {
            fontInc.disabled = currentState.fontScale >= FONT_MAX - 0.0001;
        }
    }

    function applyState(nextUi, nextFont, options) {
        var opts = options || {};
        var uiScale = clampScale(nextUi, UI_MIN, UI_MAX, currentState.uiScale);
        var fontScale = clampScale(nextFont, FONT_MIN, FONT_MAX, currentState.fontScale);

        currentState.uiScale = uiScale;
        currentState.fontScale = fontScale;

        if (opts.clearStorage) {
            clearStorage();
        } else if (opts.persist !== false) {
            writeStorage(uiScale, fontScale);
        }

        applyRootVariables(uiScale, fontScale);
        syncModalUI();

        if (opts.notify !== false) {
            dispatchScaleChanged(uiScale, fontScale);
        }
        if (opts.relayout !== false) {
            scheduleRelayout();
        }

        return getScale();
    }

    function getScale() {
        return {
            uiScale: currentState.uiScale,
            fontScale: currentState.fontScale
        };
    }

    function setScale(nextOrUi, maybeFont) {
        var nextUi = currentState.uiScale;
        var nextFont = currentState.fontScale;

        if (nextOrUi && typeof nextOrUi === 'object') {
            if (typeof nextOrUi.uiScale !== 'undefined') {
                nextUi = nextOrUi.uiScale;
            }
            if (typeof nextOrUi.fontScale !== 'undefined') {
                nextFont = nextOrUi.fontScale;
            }
        } else {
            if (typeof nextOrUi !== 'undefined' && nextOrUi !== null) {
                nextUi = nextOrUi;
            }
            if (typeof maybeFont !== 'undefined' && maybeFont !== null) {
                nextFont = maybeFont;
            }
        }

        return applyState(nextUi, nextFont, { persist: true, notify: true, relayout: true });
    }

    function resetScale() {
        return applyState(DEFAULTS.uiScale, DEFAULTS.fontScale, {
            clearStorage: true,
            notify: true,
            relayout: true
        });
    }

    function refreshFeatherIcons() {
        if (window.feather && typeof window.feather.replace === 'function') {
            try {
                window.feather.replace({ width: 14, height: 14 });
            } catch (err) {
                // Ignore feather icon rendering errors
            }
        }
    }

    function bindControls() {
        if (controlsBound) {
            return;
        }
        var modal = document.getElementById('sigapp-display-scale-modal');
        if (!modal) {
            return;
        }
        controlsBound = true;

        var uiRange = document.getElementById('sigapp-ui-scale-range');
        var fontRange = document.getElementById('sigapp-font-scale-range');
        var resetBtn = document.getElementById('sigapp-display-scale-reset');

        if (uiRange) {
            uiRange.addEventListener('input', function () {
                setScale({ uiScale: this.value });
            });
            uiRange.addEventListener('change', function () {
                setScale({ uiScale: this.value });
            });
        }

        if (fontRange) {
            fontRange.addEventListener('input', function () {
                setScale({ fontScale: this.value });
            });
            fontRange.addEventListener('change', function () {
                setScale({ fontScale: this.value });
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                resetScale();
            });
        }

        modal.addEventListener('click', function (event) {
            var targetEl = event.target;
            if (!targetEl || !targetEl.closest) {
                return;
            }

            var presetBtn = targetEl.closest('.sigapp-scale-preset-btn[data-scale-target][data-scale-value]');
            if (presetBtn && modal.contains(presetBtn)) {
                var target = presetBtn.getAttribute('data-scale-target');
                var value = parseFloat(presetBtn.getAttribute('data-scale-value'));
                if (target === 'font') {
                    setScale({ fontScale: value });
                } else {
                    setScale({ uiScale: value });
                }
                return;
            }

            var stepBtn = targetEl.closest('.sigapp-scale-step-btn[data-scale-target][data-scale-step]');
            if (stepBtn && modal.contains(stepBtn)) {
                var stepTarget = stepBtn.getAttribute('data-scale-target');
                var stepDelta = parseFloat(stepBtn.getAttribute('data-scale-step'));
                if (!isFinite(stepDelta)) {
                    return;
                }
                if (stepTarget === 'font') {
                    setScale({ fontScale: currentState.fontScale + stepDelta });
                } else {
                    setScale({ uiScale: currentState.uiScale + stepDelta });
                }
            }
        });

        if (window.jQuery) {
            window.jQuery(document).on('show.bs.modal', '#sigapp-display-scale-modal', function () {
                syncModalUI();
                refreshFeatherIcons();
            });
        }
    }

    function init() {
        var initial = readStorage();
        currentState.uiScale = initial.uiScale;
        currentState.fontScale = initial.fontScale;
        applyRootVariables(currentState.uiScale, currentState.fontScale);
        bindControls();
        syncModalUI();
    }

    window.SIGAPP_DISPLAY_SCALE = {
        get: getScale,
        set: setScale,
        reset: resetScale,
        defaults: DEFAULTS
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
