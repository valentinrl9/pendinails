document.addEventListener('DOMContentLoaded', function () {
    const menuBtn = document.querySelector('.menu-btn');
    const navLinks = document.querySelector('.nav-links');
    if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', () => navLinks.classList.toggle('open'));
        navLinks.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', () => navLinks.classList.remove('open'));
        });
    }

    const filterBtns = document.querySelectorAll('.filters button');
    const cards = Array.from(document.querySelectorAll('.card'));
    const noResults = document.getElementById('noResults');
    const searchInput = document.getElementById('buscarNombre');
    let categoriaActiva = 'todos';

    function limpiar(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function filtrar() {
        const query = limpiar(searchInput ? searchInput.value : '');
        let visibles = 0;
        cards.forEach(card => {
            const cat = card.dataset.categoria;
            const nombre = limpiar(card.dataset.nombre || card.querySelector('h3')?.textContent);
            const desc = limpiar(card.querySelector('p')?.textContent);
            const matchCat = categoriaActiva === 'todos' || cat === categoriaActiva;
            const matchTexto = !query || nombre.includes(query) || desc.includes(query);
            const show = matchCat && matchTexto;
            card.classList.toggle('is-hidden', !show);
            if (show) visibles++;
        });
        if (noResults) noResults.classList.toggle('is-visible', visibles === 0);
    }

    filterBtns.forEach(button => {
        button.addEventListener('click', () => {
            categoriaActiva = button.dataset.filter;
            filterBtns.forEach(b => b.classList.remove('is-active'));
            button.classList.add('is-active');
            filtrar();
        });
    });
    if (searchInput) searchInput.addEventListener('input', filtrar);

    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('pendinail-img') && lightbox && lightboxImage) {
            lightboxImage.src = e.target.getAttribute('src');
            lightbox.hidden = false;
            lightbox.classList.add('is-open');
        }
    });
    if (lightbox) {
        lightbox.addEventListener('click', () => {
            lightbox.classList.remove('is-open');
            lightbox.hidden = true;
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                lightbox.classList.remove('is-open');
                lightbox.hidden = true;
            }
        });
    }

    const form = document.getElementById('formContacto');
    const mensajeExito = document.getElementById('mensajeExito');
    const mensajeError = document.getElementById('mensajeError');
    const btnEnviar = document.getElementById('btnEnviar');
    const campoMensaje = document.getElementById('cMensaje');

    document.querySelectorAll('.btn-encargar').forEach(btn => {
        btn.addEventListener('click', () => {
            const pieza = btn.dataset.pieza || 'una pieza';
            if (campoMensaje) {
                campoMensaje.value = 'Hola, me interesa encargar la pieza «' + pieza + '». ¿Sigue disponible?';
            }
            document.getElementById('contacto')?.scrollIntoView({ behavior: 'smooth' });
            document.getElementById('cNombre')?.focus();
        });
    });

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            mensajeExito?.classList.remove('is-visible');
            mensajeError?.classList.remove('is-visible');
            if (btnEnviar) {
                btnEnviar.disabled = true;
                btnEnviar.textContent = 'Enviando…';
            }
            const wa = window.PENDINAILS_WA;
            const quiereWhatsapp = document.getElementById('enviarWhatsapp')?.checked;
            let waWin = null;
            try {
                const nombre = form.nombre?.value.trim() || '';
                const email = form.email?.value.trim() || '';
                const mensaje = form.mensaje?.value.trim() || '';
                if (wa && quiereWhatsapp) {
                    waWin = window.open('about:blank', 'pendinails-wa');
                }

                const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
                const text = (await res.text()).trim();
                if (text === 'OK') {
                    form.reset();
                    mensajeExito?.classList.add('is-visible');
                    if (wa && waWin && quiereWhatsapp) {
                        const cuerpo = [
                            'Nuevo mensaje desde PendiNails',
                            '',
                            'Nombre: ' + nombre,
                            'Email: ' + email,
                            '',
                            mensaje
                        ].join('\n');
                        waWin.location = 'https://wa.me/' + wa + '?text=' + encodeURIComponent(cuerpo);
                    }
                } else {
                    if (waWin) waWin.close();
                    mensajeError?.classList.add('is-visible');
                }
            } catch (err) {
                if (waWin) waWin.close();
                mensajeError?.classList.add('is-visible');
            } finally {
                if (btnEnviar) {
                    btnEnviar.disabled = false;
                    btnEnviar.textContent = 'Enviar mensaje';
                }
            }
        });
    }

    const COOKIE_KEY = 'pendinails_cookies';
    const banner = document.getElementById('cookieBanner');
    const consent = localStorage.getItem(COOKIE_KEY);

    function loadAnalytics() {
        if (window.__pnAnalytics) return;
        window.__pnAnalytics = true;
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        window.gtag = gtag;
        const ga = document.createElement('script');
        ga.async = true;
        ga.src = 'https://www.googletagmanager.com/gtag/js?id=G-XVQYDV4NY3';
        document.head.appendChild(ga);
        gtag('js', new Date());
        gtag('config', 'G-XVQYDV4NY3');

        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
            const f = d.getElementsByTagName(s)[0];
            const j = d.createElement(s);
            const dl = l !== 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-NHJWBSP8');
    }

    if (consent === 'accepted') {
        loadAnalytics();
    } else if (!consent && banner) {
        banner.classList.add('is-visible');
    }

    document.getElementById('cookieAccept')?.addEventListener('click', () => {
        localStorage.setItem(COOKIE_KEY, 'accepted');
        banner?.classList.remove('is-visible');
        loadAnalytics();
    });
    document.getElementById('cookieReject')?.addEventListener('click', () => {
        localStorage.setItem(COOKIE_KEY, 'rejected');
        banner?.classList.remove('is-visible');
    });
});
