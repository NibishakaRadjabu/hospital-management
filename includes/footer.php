        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    var loader = document.getElementById('page-loader');
    function hide(){ if(loader){ loader.classList.add('loader-done'); setTimeout(function(){ loader.style.display='none'; }, 400); } }
    if (document.readyState === 'complete') { hide(); }
    else { window.addEventListener('load', hide); }
    // also hide on nav links so it shows on next page
    document.addEventListener('click', function(e){
        var a = e.target.closest('a[href]');
        if (a && !a.target && a.href && !a.href.startsWith('javascript') && a.hostname === location.hostname) {
            if(loader){ loader.style.display='flex'; loader.classList.remove('loader-done'); }
        }
    });
})();
</script>
<script>
(() => {
    const menu = document.getElementById('workspace-menu');
    const toggle = document.querySelector('.mobile-menu-toggle');
    if (!menu || !toggle) return;

    const closeMenu = () => {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };
    toggle.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMenu();
    });
})();
(() => {
    let activeDocument = null;
    const finishPrint = () => {
        if (!activeDocument) return;
        activeDocument.classList.remove('print-document-active');
        activeDocument.hidden = true;
        activeDocument = null;
        document.body.classList.remove('print-document-mode');
    };
    document.querySelectorAll('[data-print-document]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.dataset.printDocument);
            if (!target) return;
            finishPrint();
            activeDocument = target;
            target.hidden = false;
            target.classList.add('print-document-active');
            document.body.classList.add('print-document-mode');
            window.print();
            window.setTimeout(finishPrint, 1000);
        });
    });
    window.addEventListener('afterprint', finishPrint);
})();
(() => {
    const editBtn = document.getElementById('brand-edit-btn');
    const brandWrap = document.querySelector('.brand-wrap');
    const brandLink = brandWrap && brandWrap.querySelector('a.brand');
    const form = document.getElementById('brand-name-form');
    const input = document.getElementById('brand-name-input');
    if (!editBtn || !form || !brandLink) return;
    editBtn.addEventListener('click', () => {
        brandWrap.style.display = 'none';
        form.style.display = 'flex';
        input.focus(); input.select();
    });
    form.querySelector('.brand-edit-cancel').addEventListener('click', () => {
        form.style.display = 'none';
        brandWrap.style.display = '';
    });
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(form);
        const res = await fetch('/hospital%20management/admin/settings.php', { method: 'POST', body: data, redirect: 'error' });
        if (res.ok) {
            const name = input.value.trim();
            brandLink.querySelector('span:last-child').firstChild.textContent = name;
            brandLink.querySelector('.brand-mark').textContent = name.charAt(0).toUpperCase();
            document.title = document.title.replace(/\|.*$/, '| ' + name);
        }
        form.style.display = 'none';
        brandWrap.style.display = '';
    });
})();
(() => {
    const bell = document.getElementById('notifBell');
    if (!bell) return;
    const open  = () => { bell.classList.add('is-open');    bell.setAttribute('aria-expanded', 'true'); };
    const close = () => { bell.classList.remove('is-open'); bell.setAttribute('aria-expanded', 'false'); };
    const toggle = () => bell.classList.contains('is-open') ? close() : open();
    bell.addEventListener('click', (e) => { e.stopPropagation(); toggle(); });
    bell.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
    document.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();
</script>
</body>
</html>
