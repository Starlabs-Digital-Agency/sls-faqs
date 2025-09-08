/*!
 * Starlabs FAQ — Tabs + Filtering + ARIA Keyboard Nav
 * - Supports multiple instances on a page
 * - Deep-linking via #category
 * - Roving tabindex for tabs (Left/Right/Home/End)
 * - Robust bootstrap for defer/async loaders
 */
(function () {
  'use strict';

  function initContainer(root) {
    var tabsWrap = root.querySelector('.faq-tabs');
    var tabs = tabsWrap ? Array.prototype.slice.call(tabsWrap.querySelectorAll('.faq-tab')) : [];
    var list = root.querySelector('.faq-list');
    var items = list ? Array.prototype.slice.call(list.querySelectorAll('.faq-item')) : [];
    var emptyCatMsg = root.querySelector('.faq-empty-category');
    var updateHash = (root.getAttribute('data-update-hash') || 'true') !== 'false';

    if (!tabs.length || !list) return;

    function setActiveTab(target) {
      tabs.forEach(function (t) {
        var on = t === target;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.setAttribute('tabindex', on ? '0' : '-1');
      });
      try { target.focus({ preventScroll: true }); } catch (e) {}
    }

    function applyFilter(slug) {
      var visibleCount = 0;
      items.forEach(function (el) {
        var match = (slug === 'all') ? true : el.classList.contains(slug);
        el.classList.toggle('is-hidden', !match);
        if (match) visibleCount++;
      });
      if (emptyCatMsg) emptyCatMsg.hidden = visibleCount !== 0;

      if (updateHash) {
        if (slug === 'all') {
          try { history.replaceState(null, '', location.pathname + location.search); } catch (e) {}
        } else {
          try { location.hash = slug; } catch (e) {}
        }
      }
    }

    tabs.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setActiveTab(btn);
        applyFilter(btn.dataset.filter);
      });
    });

    tabsWrap.addEventListener('keydown', function (e) {
      var key = e.key;
      var current = document.activeElement;
      if (!current || tabs.indexOf(current) === -1) return;

      var idx = tabs.indexOf(current);
      var nextIndex = idx;

      if (key === 'ArrowRight') nextIndex = (idx + 1) % tabs.length;
      else if (key === 'ArrowLeft') nextIndex = (idx - 1 + tabs.length) % tabs.length;
      else if (key === 'Home') nextIndex = 0;
      else if (key === 'End') nextIndex = tabs.length - 1;
      else if (key === 'Enter' || key === ' ') {
        e.preventDefault();
        current.click();
        return;
      } else return;

      e.preventDefault();
      var target = tabs[nextIndex];
      setActiveTab(target);
      applyFilter(target.dataset.filter);
    });

    var initial = (location.hash || '').replace('#', '') || 'all';
    var initialTab = tabs.find(function (t) { return t.dataset.filter === initial; }) || tabs[0];
    setActiveTab(initialTab);
    applyFilter(initialTab.dataset.filter);
  }

  function bootstrap() {
    var roots = document.querySelectorAll('.starlabs-faqs');
    Array.prototype.forEach.call(roots, initContainer);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrap);
  } else {
    bootstrap();
  }
})();
