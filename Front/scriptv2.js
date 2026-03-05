document.addEventListener('DOMContentLoaded', function() {
    let swiper = null;
    const swiperContainer = document.getElementById('pendinailsSwiper');
    const wrapper = document.getElementById('swiperWrapper');
    const inputBusqueda = document.getElementById('buscarNombre');
    const selectCat = document.getElementById('filtroCategoria');
    const btnTodos = document.getElementById('btnTodos');
    const noResults = document.getElementById('noResults');
    const searchToast = new bootstrap.Toast(document.getElementById('searchToast'));

    // 1. Guardar copia de los slides originales antes de cualquier manipulación
    const slidesOriginales = Array.from(document.querySelectorAll('.swiper-slide'));

    // Función para normalizar texto (tildes, espacios y minúsculas)
    function limpiarTexto(texto) {
        if (!texto) return "";
        return texto.toLowerCase()
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "")
                    .trim();
    }

    // 2. Función Maestra: Controla el Carrusel y el Diseño
    function gestionarLayout(cantidad) {
        // Destruir swiper previo si existe para resetear el DOM
        if (swiper) {
            swiper.destroy(true, true);
            swiper = null;
            window.swiper = null;
        }

        // Resetear clases de diseño del contenedor
        wrapper.classList.remove('justify-content-center', 'd-flex', 'flex-wrap');
        const slidesActuales = wrapper.querySelectorAll('.swiper-slide');
        
        slidesActuales.forEach(s => {
            s.style.width = ''; 
            s.style.margin = '';
        });

        if (cantidad >= 4) {
            // --- MODO CARRUSEL (4 o más productos) ---
            swiperContainer.classList.remove('carrusel-desactivado');
            swiper = new Swiper('#pendinailsSwiper', {
                effect: 'coverflow',
                slidesPerView: 'auto',
                spaceBetween: 30,
                centeredSlides: true,
                loop: true,
                autoplay: { delay: 2000, disableOnInteraction: false },
                coverflowEffect: {
                    rotate: 20,
                    stretch: 0,
                    depth: 200,
                    modifier: 1,
                    slideShadows: false,
                },
                navigation: { 
                    nextEl: '.swiper-button-next', 
                    prevEl: '.swiper-button-prev' 
                },
                pagination: { 
                    el: '.swiper-pagination', 
                    clickable: true 
                },
                breakpoints: {
                    320: { slidesPerView: 1.2 },
                    768: { slidesPerView: 3 }
                }
            });

            // Hacer accesible el swiper globalmente
            window.swiper = swiper;

        } else if (cantidad > 0) {
            // --- MODO CUADRÍCULA (1 a 3 productos) ---
            swiperContainer.classList.add('carrusel-desactivado');
            wrapper.classList.add('justify-content-center', 'd-flex', 'flex-wrap');
            slidesActuales.forEach(s => {
                s.style.width = '300px'; 
                s.style.margin = '15px';
            });
        }
    }

    // 3. Función de Búsqueda Dinámica
    function filtrar() {
        const query = limpiarTexto(inputBusqueda.value);
        const selectedCat = selectCat.value;

        wrapper.style.opacity = '0';

        setTimeout(() => {
            wrapper.innerHTML = '';

            const filtrados = slidesOriginales.filter(slide => {
                const desc = limpiarTexto(slide.querySelector('.text-muted').textContent);
                const nombre = limpiarTexto(slide.getAttribute('data-nombre'));
                const cat = slide.getAttribute('data-categoria');

                const matchTexto = desc.includes(query) || nombre.includes(query);
                const matchCat = (selectedCat === 'todos' || cat === selectedCat);

                return matchTexto && matchCat;
            });

            if (filtrados.length > 0) {
                filtrados.forEach(s => {
                    const clon = s.cloneNode(true);
                    wrapper.appendChild(clon);
                });
                noResults.classList.add('d-none');
                gestionarLayout(filtrados.length);
            } else {
                noResults.classList.remove('d-none');
                if (query !== "") searchToast.show();
                gestionarLayout(0);
            }
            wrapper.style.opacity = '1';
        }, 200);
    }

    // 4. Listeners
    inputBusqueda.addEventListener('input', filtrar);
    selectCat.addEventListener('change', filtrar);
    
    btnTodos.addEventListener('click', () => {
        inputBusqueda.value = '';
        selectCat.value = 'todos';
        filtrar();
    });

    // 5. Inicialización
    gestionarLayout(slidesOriginales.length);

    // Scroll Top
    const scrollTopBtn = document.getElementById("scrollTopBtn");
    window.addEventListener("scroll", () => {
        scrollTopBtn.style.display = window.scrollY > 300 ? "block" : "none";
    });
    scrollTopBtn.onclick = () => window.scrollTo({ top: 0, behavior: "smooth" });
});


// -----------------------------
//  MODAL + PAUSA DEL CARRUSEL
// -----------------------------

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("pendinail-img")) {
        const imgSrc = e.target.getAttribute("src");
        document.getElementById("modalImage").setAttribute("src", imgSrc);

        const modal = new bootstrap.Modal(document.getElementById("imageModal"));
        modal.show();
    }
});

// Pausar y reanudar Swiper al abrir/cerrar modal
const modalEl = document.getElementById("imageModal");

modalEl.addEventListener("show.bs.modal", () => {
    if (window.swiper && window.swiper.autoplay) {
        window.swiper.autoplay.stop();
    }
});

modalEl.addEventListener("hidden.bs.modal", () => {
    if (window.swiper && window.swiper.autoplay) {
        window.swiper.autoplay.start();
    }
});
