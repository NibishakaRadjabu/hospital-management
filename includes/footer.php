        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
