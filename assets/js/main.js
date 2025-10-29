// Header interactions: search toggle and mobile drawer
(function(){
  const $ = (sel, ctx=document) => ctx.querySelector(sel);
  const $$ = (sel, ctx=document) => Array.from(ctx.querySelectorAll(sel));
  const body = document.body;

  // Search toggle (desktop bottom bar)
  $$(".yt-search").forEach(box => {
    const btn = $(".yt-search__toggle", box);
    const shell = box.closest(".yt-header-shell");
    const bar = $(".yt-searchbar", shell || document);
    if (!btn || !shell || !bar) return;
    btn.addEventListener("click", () => {
      const open = shell.classList.contains("is-search-open");
      if (!open){
        shell.classList.add("is-search-open");
        btn.setAttribute("aria-expanded", "true");
        bar.setAttribute("aria-hidden","false");
        const input = $(".yt-searchbar input[type='search']", shell);
        if (input) setTimeout(() => input.focus(), 10);
      } else {
        shell.classList.remove("is-search-open");
        btn.setAttribute("aria-expanded", "false");
        bar.setAttribute("aria-hidden","true");
      }
    });
  });

  // Close search when clicking outside
  document.addEventListener("click", (e) => {
    const shell = $(".yt-header-shell.is-search-open");
    if (!shell) return;
    const bar = $(".yt-searchbar", shell);
    const toggle = $(".yt-search__toggle", shell);
    if (!bar) return;
    const withinBar = bar.contains(e.target);
    const onToggle = toggle && toggle.contains(e.target);
    if (!withinBar && !onToggle){
      shell.classList.remove("is-search-open");
      if (toggle) toggle.setAttribute("aria-expanded","false");
      bar.setAttribute("aria-hidden","true");
    }
  });

  // Mobile drawer
  const drawer = $("#yt-mobile-drawer");
  if (drawer){
    const openers = $$(".yt-burger");
    let overlay = $(".yt-overlay");
    const closer = $(".yt-drawer__close", drawer);

    // Move drawer and overlay to body to avoid being affected by transformed/sticky ancestors
    if (drawer.parentElement !== document.body){
      document.body.appendChild(drawer);
    }
    if (!overlay){
      overlay = document.createElement('div');
      overlay.className = 'yt-overlay';
      overlay.hidden = true;
    }
    if (overlay.parentElement !== document.body){
      document.body.appendChild(overlay);
    }
    const setOpen = (state) => {
      if (!drawer) return;
      if (state){
        drawer.classList.add("is-open");
        drawer.setAttribute("aria-hidden", "false");
        // Prevent layout shift by compensating scrollbar width
        const sbw = window.innerWidth - document.documentElement.clientWidth;
        document.body.style.overflow = "hidden";
        if (sbw > 0){ document.body.style.paddingInlineEnd = sbw + 'px'; }
        openers.forEach(btn => btn.setAttribute("aria-expanded","true"));
        if (overlay){ overlay.hidden = false; }
      } else {
        drawer.classList.remove("is-open");
        drawer.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "";
        document.body.style.paddingInlineEnd = "";
        openers.forEach(btn => btn.setAttribute("aria-expanded","false"));
        if (overlay){ overlay.hidden = true; }
      }
    };

    openers.forEach(btn => btn.addEventListener("click", () => setOpen(true)));
    if (closer) closer.addEventListener("click", () => setOpen(false));
    if (overlay) overlay.addEventListener("click", () => setOpen(false));
    drawer.addEventListener("click", (e) => {
      const a = e.target.closest("a");
      if (a) setOpen(false);
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") setOpen(false);
    });

    // Close drawer on breakpoint change to desktop
    const closeOnDesktop = () => { if (window.innerWidth >= 992) setOpen(false); };
    window.addEventListener('resize', closeOnDesktop);
    // Initialize closed state on load for safety
    setOpen(false);
  }

  // Global ESC closes search bar
  document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    const shell = $(".yt-header-shell.is-search-open");
    if (!shell) return;
    const btn = $(".yt-search__toggle", shell);
    const bar = $(".yt-searchbar", shell);
    shell.classList.remove("is-search-open");
    if (btn) btn.setAttribute("aria-expanded","false");
    if (bar) bar.setAttribute("aria-hidden","true");
  });

  // Sticky: show until 30% viewport, then hide on scroll down; show on scroll up
  (function(){
    const shell = $(".yt-header-shell");
    if (!shell) return;
    let lastY = window.scrollY;
    const threshold = () => Math.round(window.innerHeight * 0.3);
    const onScroll = () => {
      const y = window.scrollY;
      const dirDown = y > lastY;
      // Add stuck state as soon as page is scrolled
      if (y > 0) shell.classList.add("is-stuck"); else shell.classList.remove("is-stuck");
      // Don't hide if search or drawer is open
      const drawerOpen = $(".yt-drawer.is-open");
      const searchOpen = shell.classList.contains("is-search-open");
      if (!drawerOpen && !searchOpen){
        if (y > threshold() && dirDown){
          shell.classList.add("is-hidden");
        } else {
          shell.classList.remove("is-hidden");
        }
      }
      lastY = y;
    };
    window.addEventListener("scroll", onScroll, { passive:true });
    window.addEventListener("resize", () => { lastY = window.scrollY; });
  })();

  // Tabs: support multiple sections and sorting param
  (function(){
    const sections = Array.from(document.querySelectorAll('.home-secondary'));
    if (!sections.length) return;
    const map = { latest:'تازه‌ترین', oldest:'قدیمی‌ترین', popular:'محبوب‌ترین' };sections.forEach((section, idx) => {
      const tabs = section.querySelector('.hs-tabs');
      if (!tabs) return;
      // normalize tab labels and existence
      let btns = Array.from(tabs.querySelectorAll('.hs-tab[data-sort]'));
      const sorts = ['latest','oldest','popular'];
      sorts.forEach(s => {
        let b = btns.find(x => x.dataset.sort === s);
        if (!b){ b = document.createElement('button'); b.type='button'; b.className='hs-tab'; b.dataset.sort=s; b.setAttribute('role','tab'); b.setAttribute('aria-selected','false'); tabs.appendChild(b); }
        b.textContent = map[s];
      });
      btns = Array.from(tabs.querySelectorAll('.hs-tab[data-sort]'));

      let postsBox = section.querySelector('.hs-posts');
      const primary = tabs.querySelector('.hs-tab--primary');
      if (primary){
        const current = (primary.textContent || '').trim();
        if (!current){
          const slug = section.getAttribute('data-cat');
          primary.textContent = (slug === 'sales-tips') ? 'ترفند فروش' : 'آموزش ویترو';
        }
      }
      const getCat = () => { const id = section.getAttribute('data-cat-id'); if (id) return id; const attr = section.getAttribute('data-cat'); if (attr) return attr; if (idx === 0) return 'educational'; if (idx === 1) return 'sales-tips'; return 'educational'; };const setActive = (s) => btns.forEach(b => b.classList.toggle('is-active', b.dataset.sort === s));
      setActive('latest');

      btns.forEach(b => b.addEventListener('click', async (ev) => {
        ev.preventDefault();
        const sort = b.dataset.sort;
        setActive(sort);
        if (!postsBox) return;
        postsBox.classList.add('is-loading');
        try{
          const ajaxUrl = (window.yourthemeAjax && yourthemeAjax.url) || (window.wp && wp.ajax && wp.ajax.settings && wp.ajax.settings.url) || '/wp-admin/admin-ajax.php';
          const form = new FormData();
          form.append('action','yourtheme_load_posts');
          form.append('cat', getCat());
          const titleEl = tabs.querySelector('.hs-tab--primary');
          if (titleEl) form.append('cat_name', (titleEl.textContent||'').trim());
          form.append('sort', sort);
          if (window.yourthemeAjax && yourthemeAjax.nonce){ form.append('_ajax_nonce', yourthemeAjax.nonce); }
          const res = await fetch(ajaxUrl, { method:'POST', body: form, credentials:'same-origin', headers: { 'X-Requested-With':'XMLHttpRequest' } });
          const data = await res.json();
          if (data && data.success && data.data && data.data.html){
            // Replace the whole posts container
            const wrapper = document.createElement('div');
            wrapper.innerHTML = data.data.html;
            const newBox = wrapper.querySelector('.hs-posts');
            if (newBox){ postsBox.replaceWith(newBox); postsBox = newBox; }
          }
        } catch(e){ console.error('Load posts failed', e); }
      }));
    });
  })();

  // Listing section AJAX sort (no reload)
  (function(){
    const sec = document.querySelector('.home-listing');
    if (!sec) return;
    const tabs = sec.querySelector('.hl-tabs');
    const postsBox = sec.querySelector('.hl-posts');
    if (!tabs || !postsBox) return;
    const btns = Array.from(tabs.querySelectorAll('.hl-tab[data-sort]'));
    const setActive = (s) => btns.forEach(b => b.classList.toggle('is-active', b.dataset.sort === s));
    setActive('latest');
    btns.forEach(b => b.addEventListener('click', async (ev) => {
      ev.preventDefault();
      const sort = b.dataset.sort;
      setActive(sort);
      postsBox.classList.add('is-loading');
      try{
        const ajaxUrl = (window.yourthemeAjax && yourthemeAjax.url) || '/wp-admin/admin-ajax.php';
        const form = new FormData();
        form.append('action','yourtheme_load_posts');
        form.append('layout','listing');
        form.append('sort', sort);
        if (window.yourthemeAjax && yourthemeAjax.nonce){ form.append('_ajax_nonce', yourthemeAjax.nonce); }
        const res = await fetch(ajaxUrl, { method:'POST', body: form, credentials:'same-origin' });
        const data = await res.json();
        if (data && data.success && data.data && data.data.html){
          const wrapper = document.createElement('div');
          wrapper.innerHTML = data.data.html;
          const newBox = wrapper.querySelector('.hl-posts');
          if (newBox){ postsBox.replaceWith(newBox); }
        }
      } catch(e){ console.error(e); }
    }));

    // AJAX pagination: intercept clicks on listing pagination
    document.addEventListener('click', async (e) => {
      const a = e.target.closest('.hl-pagination a');
      if (!a || !sec.contains(a)) return;
      e.preventDefault();
      const url = new URL(a.href, location.origin);
      const paged = url.searchParams.get('paged') || url.searchParams.get('page') || a.getAttribute('data-page') || '1';
      const active = tabs.querySelector('.hl-tab.is-active');
      const sort = active ? active.dataset.sort : 'latest';
      try{
        const ajaxUrl = (window.yourthemeAjax && yourthemeAjax.url) || '/wp-admin/admin-ajax.php';
        const form = new FormData();
        form.append('action','yourtheme_load_posts');
        form.append('layout','listing');
        form.append('sort', sort);
        form.append('paged', paged);
        if (window.yourthemeAjax && yourthemeAjax.nonce){ form.append('_ajax_nonce', yourthemeAjax.nonce); }
        const res = await fetch(ajaxUrl, { method:'POST', body: form, credentials:'same-origin' });
        const data = await res.json();
        if (data && data.success && data.data && data.data.html){
          const wrapper = document.createElement('div');
          wrapper.innerHTML = data.data.html;
          const newBox = wrapper.querySelector('.hl-posts');
          const current = sec.querySelector('.hl-posts');
          if (newBox && current){ current.replaceWith(newBox); window.scrollTo({ top: sec.offsetTop - 24, behavior:'smooth' }); }
        }
      } catch(err){ console.error(err); }
    });
  })();
})();

