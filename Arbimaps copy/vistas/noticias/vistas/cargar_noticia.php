<style>
    .swal2-popup.swal2-modal.swal2-show {
        border-radius: 22px !important;
        border: 1px solid rgba(0, 47, 85, .10) !important;
        box-shadow: 0 28px 70px rgba(0, 0, 0, .22) !important;
    }

    .swal2-title {
        color: #002F55 !important;
        font-weight: 800 !important;
        letter-spacing: -.2px;
    }

    .swal2-html-container {
        margin-top: .35rem !important;
    }

    .swal2-actions {
        margin-top: 1rem !important;
    }

    .swal2-styled.swal2-confirm {
        border-radius: 14px !important;
        padding: .78rem 1.1rem !important;
        font-weight: 700 !important;
    }

    .swal2-styled.swal2-cancel {
        border-radius: 14px !important;
        padding: .78rem 1.1rem !important;
        font-weight: 700 !important;
    }

    .swal-icon-pro {
        border: 0 !important;
        width: auto !important;
        height: auto !important;
        margin: 0 auto .8rem auto !important;
    }

    .swal-icon-pro .swal2-icon-content {
        margin: 0 !important;
    }

    .orb {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        position: relative;
        background:
            radial-gradient(circle at 30% 22%, rgba(255, 255, 255, .95), rgba(255, 255, 255, 0) 40%),
            linear-gradient(135deg, rgba(15, 86, 153, .28), rgba(0, 47, 85, .32));
        border: 1px solid rgba(0, 47, 85, .14);
        box-shadow: 0 18px 34px rgba(0, 0, 0, .16);
        overflow: hidden;
    }

    .orb::before {
        content: "";
        position: absolute;
        inset: -14px;
        border-radius: 50%;
        border: 2px solid rgba(15, 86, 153, .22);
        filter: blur(.2px);
        animation: orbPulse 1.35s ease-out infinite;
    }

    .orb::after {
        content: "";
        position: absolute;
        inset: -26px;
        border-radius: 50%;
        border: 2px solid rgba(15, 86, 153, .12);
        animation: orbPulse 1.35s ease-out infinite;
        animation-delay: .35s;
    }

    @keyframes orbPulse {
        0% {
            transform: scale(.72);
            opacity: 0;
        }

        15% {
            opacity: .55;
        }

        100% {
            transform: scale(1.03);
            opacity: 0;
        }
    }

    .orb i {
        font-size: 38px;
        color: #002F55;
        transform-origin: 50% 60%;
        animation: orbTilt 1.15s ease-in-out infinite;
    }

    @keyframes orbTilt {
        0% {
            transform: translateY(0) rotate(-10deg);
        }

        50% {
            transform: translateY(-2px) rotate(10deg);
        }

        100% {
            transform: translateY(0) rotate(-10deg);
        }
    }

    .social-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        margin-bottom: .8rem;
    }

    .social-app {
        display: flex;
        align-items: center;
        gap: .65rem;
        min-width: 0;
    }

    .app-logo {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, rgba(15, 86, 153, .18), rgba(0, 47, 85, .14));
        border: 1px solid rgba(0, 47, 85, .14);
        box-shadow: 0 12px 24px rgba(0, 0, 0, .10);
        color: #002F55;
    }

    .app-name {
        font-weight: 800;
        color: #002F55;
        line-height: 1;
    }

    .app-sub {
        font-size: .82rem;
        color: rgba(0, 47, 85, .62);
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .65rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        color: #002F55;
        background: rgba(0, 47, 85, .04);
        border: 1px solid rgba(0, 47, 85, .12);
        white-space: nowrap;
    }

    .post-shell {
        border-radius: 18px;
        border: 1px solid rgba(0, 47, 85, .12);
        background: rgba(255, 255, 255, .75);
        overflow: hidden;
    }

    .post-head {
        padding: .85rem .95rem;
        display: flex;
        gap: .65rem;
        align-items: center;
        background: linear-gradient(135deg, rgba(15, 86, 153, .09), rgba(0, 47, 85, .05));
        border-bottom: 1px solid rgba(0, 47, 85, .08);
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, rgba(15, 86, 153, .22), rgba(0, 47, 85, .16));
        border: 1px solid rgba(0, 47, 85, .14);
        color: #002F55;
    }

    .meta {
        min-width: 0;
    }

    .meta .who {
        font-weight: 800;
        color: #002F55;
        line-height: 1.05;
    }

    .meta .when {
        font-size: .78rem;
        color: rgba(0, 47, 85, .62);
    }

    .post-body {
        padding: .9rem .95rem;
    }

    .link-card {
        border-radius: 16px;
        border: 1px solid rgba(0, 47, 85, .12);
        overflow: hidden;
        background: #fff;
    }

    .link-top {
        padding: .85rem;
        background: linear-gradient(135deg, rgba(15, 86, 153, .10), rgba(0, 47, 85, .06));
    }

    .domain {
        font-size: .78rem;
        color: rgba(0, 47, 85, .60);
    }

    .link-title {
        margin-top: .35rem;
        font-weight: 900;
        color: #002F55;
        letter-spacing: -.2px;
    }

    .link-desc {
        margin-top: .25rem;
        font-size: .88rem;
        color: rgba(0, 47, 85, .64);
    }

    .link-foot {
        padding: .8rem .85rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .65rem;
    }

    .url-text {
        font-size: .82rem;
        color: rgba(0, 47, 85, .60);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 360px;
    }

    .hint {
        margin-top: .75rem;
        font-size: .82rem;
        color: rgba(0, 47, 85, .58);
    }

    .card-especial-tres {
        position: relative;
        overflow: hidden;
    }

    #social-float-layer {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
    }

    .card-especial-tres>.row {
        position: relative;
        z-index: 1;
    }

    .social-float-icon {
        position: absolute;
        font-size: 34px;
        opacity: .12;
        filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .10));
        transform: translateZ(0);
    }
</style>

<div id="social-float-layer" aria-hidden="true"></div>
<div class="my-5 text-center">
    <h4 class=" mb-0 fw-bold mb-2" style="color: #002F55; font-weight: 700 !important ">CARGAR NOTICIAS</h4>
    <small>Módulo para cargar noticias en la vista principal</small>
</div>
<div class="row justify-content-center">
    <div class="col-12 col-lg-11 col-xl-12 col-xxl-11">
        <div class="card card-especial-tres shadow h-100 py-4 px-4 rounded-5"
            style="min-height: 70vh; border: 1px solid #002f553d;">
            <div class="row justify-content-center h-100 px-4">
                <div class="col-12">
                    <div class="card shadow-lg border border-primary-subtle bg-body-tertiary rounded-4">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="mb-1 fw-bold" style="color:#002F55;">Publicar enlace</h4>
                                <div class="text-muted small">Comparte un link con una descripción rápida (estilo tarjeta).</div>
                            </div>
                            <span class="badge rounded-pill text-bg-light border" style="color:#002F55;">
                                <i class="bi bi-link-45deg me-1"></i> Social
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-4">
                    <div class="row g-4">
                        <div class="col-12 col-lg-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3" style="color:#002F55;">
                                        <i class="bi bi-pencil-square me-2"></i>Datos del enlace
                                    </h6>
                                    <form class="row g-3" action="./vistas/noticias/acciones/publicar_noticia.php" method="POST" id="formLink" novalidate>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">URL *</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white rounded-start-3">
                                                    <i class="bi bi-globe2" style="color:#002F55;"></i>
                                                </span>
                                                <input type="url" class="form-control rounded-end-3" name="url" id="urlInput"
                                                    placeholder="https://..." required>
                                                <div class="invalid-feedback">Ingresa una URL válida.</div>
                                            </div>
                                            <div class="form-text">Ej: https://www.facebook.com/plugins/post.php</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label fw-semibold mb-0">Descripción *</label>
                                                <small class="text-muted" id="descCount">0 / 180</small>
                                            </div>
                                            <textarea class="form-control rounded-3" rows="4" name="descripcion" id="descInput"
                                                placeholder="Escribe una descripción corta y clara..." maxlength="180" required></textarea>
                                            <div class="invalid-feedback">La descripción es obligatoria (máx. 180).</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-2 flex-wrap pt-1">
                                                <button type="submit" class="btn btn-primary rounded-3 px-4"
                                                    style="background:#002F55; border-color:#002F55;">
                                                    <i class="bi bi-send-check me-1"></i> Publicar
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary rounded-3 px-4" id="btnLimpiar">
                                                    <i class="bi bi-eraser me-1"></i> Limpiar
                                                </button>
                                            </div>
                                            <div class="alert alert-light border mt-3 mb-0 rounded-4 small">
                                                <i class="bi bi-lightbulb me-1"></i>
                                                Tip: escribe en la descripción <b>qué es</b>, <b>para quién</b> y <b>qué acción tomar</b>.
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="position-sticky" style="top: 1rem;">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4">
                                        <h6 class="fw-bold mb-3" style="color:#002F55;">
                                            <i class="bi bi-eye me-2"></i>Vista previa
                                        </h6>
                                        <a href="#" class="text-decoration-none" id="previewLink" target="_blank" rel="noopener">
                                            <div class="border rounded-4 overflow-hidden">
                                                <div class="p-3"
                                                    style="background: linear-gradient(135deg, rgba(15,86,153,.10) 0%, rgba(0,47,85,.08) 100%);">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="badge rounded-pill text-bg-light border" id="previewTipo">Enlace</span>
                                                        <small class="text-muted" id="previewDominio">dominio.com</small>
                                                    </div>
                                                    <div class="mt-2 fw-bold" style="color:#002F55;" id="previewTitulo">
                                                        Enlace compartido
                                                    </div>
                                                    <div class="text-muted small mt-1" id="previewDesc">
                                                        Tu descripción aparecerá aquí...
                                                    </div>
                                                </div>
                                                <div class="p-3 bg-white d-flex align-items-center justify-content-between">
                                                    <div class="small text-muted d-flex align-items-center gap-2">
                                                        <i class="bi bi-link-45deg"></i>
                                                        <span id="previewUrlText">https://...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="mt-3 small text-muted">
                                            <i class="bi bi-shield-check me-1"></i>
                                            Vista previa local (sin scraping). Rápida y pro.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus,
    .form-select:focus {
        border-color: rgba(0, 47, 85, .35);
        box-shadow: 0 0 0 .2rem rgba(0, 47, 85, .10);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<script>
    const form = document.getElementById('formLink');

    function getDomain(u) {
        try {
            return new URL(u).hostname.replace('www.', '');
        } catch (e) {
            return 'dominio.com';
        }
    }
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) return;
        e.preventDefault();

        const url = (document.getElementById('urlInput').value || '').trim();
        const desc = (document.getElementById('descInput').value || '').trim();
        const domain = getDomain(url || 'https://dominio.com');

        Swal.fire({
            title: 'Confirmar publicación',
            icon: 'info',
            iconHtml: `<div class="orb"><i class="bi bi-broadcast"></i></div>`,
            iconColor: 'transparent',
            customClass: {
                icon: 'swal-icon-pro'
            },

            html: `
                <div class="text-start">
                <div class="social-topbar">
                    <div class="social-app">
                    <div class="app-logo"><i class="bi bi-share-fill"></i></div>
                    <div class="text-truncate">
                        <div class="app-name">Arbimaps Social</div>
                        <div class="app-sub">Publicación de enlace</div>
                    </div>
                    </div>
                    <span class="pill"><i class="bi bi-people-fill"></i> Audiencia interna</span>
                </div>
                <div class="post-shell">
                    <div class="post-head">
                    <div class="avatar"><i class="bi bi-person-fill"></i></div>
                    <div class="meta">
                        <div class="who">Publicación nueva</div>
                        <div class="when"><i class="bi bi-clock me-1"></i>Se publicará ahora</div>
                    </div>
                    <span class="pill ms-auto"><i class="bi bi-hash"></i> Social</span>
                    </div>
                    <div class="post-body">
                    <div class="small text-muted mb-2">Vista previa del enlace</div>
                    <a href="${url || '#'}" target="_blank" rel="noopener" class="text-decoration-none">
                        <div class="link-card">
                        <div class="link-top">
                            <div class="d-flex justify-content-between align-items-center">
                            <span class="pill" style="padding:.22rem .55rem; font-weight:800;">
                                <i class="bi bi-link-45deg"></i> Enlace
                            </span>
                            <div class="domain">${domain}</div>
                            </div>
                            <div class="link-title">${url ? `Ir a ${domain}` : 'Enlace compartido'}</div>
                            <div class="link-desc">${desc ? desc.slice(0,160) : 'Tu descripción aparecerá aquí…'}</div>
                        </div>
                        <div class="link-foot">
                            <div class="url-text"><i class="bi bi-globe2 me-1"></i>${url || 'https://...'}</div>
                            <span class="pill" style="background: rgba(255,193,7,.18); border-color: rgba(255,193,7,.28);">
                            <i class="bi bi-stars"></i> Destacado
                            </span>
                        </div>
                        </div>
                    </a>
                    <div class="hint">
                        <i class="bi bi-shield-check me-1"></i>
                        Se guardará en <b>noticias_arbimaps</b> y aparecerá en el módulo <b>Social</b>.
                    </div>
                    </div>
                </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-cloud-upload me-1"></i> Publicar',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancelar',
            confirmButtonColor: '#002F55',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            backdrop: 'rgba(0,47,85,.28)',
            width: 640,
            padding: '1.15rem'
        }).then((r) => {
            if (r.isConfirmed) form.submit();
        });
    });
</script>


<?php if (!empty($_GET['error'])): ?>
    <script>
        const err = "<?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>";
        let msg = 'Ocurrió un error al publicar.';
        if (err === 'campos') msg = 'Completa la URL y la descripción.';
        if (err === 'url') msg = 'La URL no es válida.';
        if (err === 'insert') msg = 'No se pudo guardar en la base de datos.';
        if (err === 'prepare') msg = 'Error preparando la consulta.';

        Swal.fire({
            title: 'Ups...',
            text: msg,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#002F55'
        });

        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('error');
            window.history.replaceState({}, document.title, url.toString());
        }
    </script>
<?php endif; ?>

<?php if (!empty($_GET['ok'])): ?>
    <script>
        Swal.fire({
            title: '¡Publicado!',
            html: `
                <div class="text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                    style="width:78px;height:78px;background:rgba(25,135,84,.12);border:1px solid rgba(25,135,84,.18);">
                    <i class="bi bi-check2-circle" style="font-size:42px;color:#198754;"></i>
                </div>
                <div class="small text-muted">Tu enlace ya aparece en <b>Arbimaps Social</b>.</div>
                </div>
            `,
            showConfirmButton: false,
            timer: 2200,
            backdrop: 'rgba(0,47,85,.22)'
        });
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('ok');
            window.history.replaceState({}, document.title, url.toString());
        }
    </script>
<?php endif; ?>

<script>
    (() => {
        const form = document.getElementById('formLink');
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    })();

    const urlInput = document.getElementById('urlInput');
    const descInput = document.getElementById('descInput');
    const descCount = document.getElementById('descCount');

    const previewLink = document.getElementById('previewLink');
    const previewTipo = document.getElementById('previewTipo');
    const previewDominio = document.getElementById('previewDominio');
    const previewTitulo = document.getElementById('previewTitulo');
    const previewDesc = document.getElementById('previewDesc');
    const previewUrlText = document.getElementById('previewUrlText');

    function getDomain(u) {
        try {
            return new URL(u).hostname.replace('www.', '');
        } catch (e) {
            return 'dominio.com';
        }
    }

    function updatePreview() {
        const url = urlInput.value.trim();
        const desc = descInput.value.trim();

        descCount.textContent = `${descInput.value.length} / 180`;

        previewTipo.textContent = 'Enlace';
        previewDominio.textContent = getDomain(url || 'https://dominio.com');
        previewTitulo.textContent = url ? `Ir a ${getDomain(url)}` : 'Enlace compartido';
        previewDesc.textContent = desc || 'Tu descripción aparecerá aquí...';
        previewUrlText.textContent = url || 'https://...';

        previewLink.href = url || '#';
    }

    urlInput.addEventListener('input', updatePreview);
    descInput.addEventListener('input', updatePreview);
    updatePreview();

    document.getElementById('btnLimpiar').addEventListener('click', () => {
        urlInput.value = '';
        descInput.value = '';
        document.getElementById('formLink').classList.remove('was-validated');
        updatePreview();
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/animejs@3.2.2/lib/anime.min.js"></script>
<script>
    (() => {
        const layer = document.getElementById('social-float-layer');
        if (!layer) return;
        const icons = [
            'bi-heart-fill', 'bi-chat-dots-fill', 'bi-share-fill', 'bi-send-fill',
            'bi-link-45deg', 'bi-hand-thumbs-up-fill', 'bi-hash', 'bi-globe2',
            'bi-bookmark-star-fill', 'bi-lightning-fill'
        ];

        const COUNT = 18;
        const rect = () => layer.getBoundingClientRect();
        const rand = (min, max) => Math.random() * (max - min) + min;
        layer.innerHTML = '';

        for (let i = 0; i < COUNT; i++) {
            const el = document.createElement('i');
            el.className = `bi ${icons[i % icons.length]} social-float-icon`;
            el.style.left = rand(-5, 95) + '%';
            el.style.top = rand(10, 95) + '%';
            el.style.fontSize = rand(26, 52) + 'px';
            el.style.opacity = rand(0.05, 0.12);
            el.style.color = (Math.random() > 0.35) ? '#002F55' : '#0F5699';

            layer.appendChild(el);
            anime({
                targets: el,
                translateY: [{
                    value: rand(-60, -140),
                    duration: rand(4200, 7800)
                }],
                translateX: [{
                    value: rand(-30, 30),
                    duration: rand(3800, 7200)
                }],
                rotate: [{
                    value: rand(-25, 25),
                    duration: rand(4500, 8500)
                }],
                opacity: [{
                        value: el.style.opacity,
                        duration: 0
                    },
                    {
                        value: rand(0.04, 0.18),
                        duration: rand(1800, 3200)
                    },
                    {
                        value: rand(0.03, 0.12),
                        duration: rand(1800, 3200)
                    }
                ],
                easing: 'easeInOutSine',
                direction: 'alternate',
                loop: true,
                delay: rand(0, 1200)
            });
        }
        let t;
        window.addEventListener('resize', () => {
            clearTimeout(t);
            t = setTimeout(() => {
                layer.querySelectorAll('.social-float-icon').forEach((el) => {
                    el.style.left = rand(-5, 95) + '%';
                    el.style.top = rand(10, 95) + '%';
                });
            }, 200);
        });
    })();
</script>