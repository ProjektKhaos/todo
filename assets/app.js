/* app.js - drag & drop + sökfilter för Kanban-tavlan Ⓐ Style */
/* Uppdaterad: 2026-04-26 14:00 | av: KlⒶssⓔ & Ⓐberg */

(function () {
    const board = document.querySelector('.kanban');
    if (!board) return;
    const isAdmin = board.dataset.admin === '1';
    const csrf = board.dataset.csrf || '';

    if (isAdmin) {
        board.querySelectorAll('.card').forEach(card => {
            card.addEventListener('dragstart', onDragStart);
            card.addEventListener('dragend', onDragEnd);
        });
        board.querySelectorAll('.kanban-col').forEach(col => {
            col.addEventListener('dragover', onDragOver);
            col.addEventListener('dragleave', onDragLeave);
            col.addEventListener('drop', onDrop);
        });
    }

    let dragId = null;
    let sourceCol = null;
    let justDragged = false;

    function onDragStart(e) {
        dragId = this.dataset.id;
        sourceCol = this.closest('.kanban-col');
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragId);
    }
    function onDragEnd() {
        this.classList.remove('dragging');
        dragId = null;
        justDragged = true;
        setTimeout(() => { justDragged = false; }, 50);
        document.querySelectorAll('.kanban-col.drag-over').forEach(c => c.classList.remove('drag-over'));
    }
    function onDragOver(e) { e.preventDefault(); this.classList.add('drag-over'); e.dataTransfer.dropEffect = 'move'; }
    function onDragLeave() { this.classList.remove('drag-over'); }

    async function onDrop(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        const id = e.dataTransfer.getData('text/plain');
        const status = this.dataset.status;
        if (!id || !status) return;

        const card = document.querySelector('.card[data-id="' + cssEscape(id) + '"]');
        if (!card) return;

        // Optimistisk flytt
        const cards = this.querySelector('.col-cards');
        cards.appendChild(card);
        // Uppdatera kortets statusklass
        card.classList.forEach(c => { if (c.startsWith('status-')) card.classList.remove(c); });
        card.classList.add(statusToClass(status));
        updateCounts();

        try {
            const fd = new FormData();
            fd.append('id', id);
            fd.append('status', status);
            fd.append('_csrf', csrf);
            const r = await fetch('admin/set_status.php', { method: 'POST', body: fd, credentials: 'same-origin' });
            const j = await r.json();
            if (!j.ok) throw new Error('Server avvisade ändringen');
        } catch (err) {
            console.warn(err);
            // Återställ vid fel
            if (sourceCol) sourceCol.querySelector('.col-cards').appendChild(card);
            updateCounts();
            alert('Kunde inte uppdatera status: ' + err.message);
        }
    }

    function statusToClass(status) {
        const map = { 'ny': 'status-ny', 'pågår': 'status-pagar', 'väntar': 'status-vantar', 'klar': 'status-klar', 'pausad': 'status-pausad' };
        return map[status] || 'status-ny';
    }

    function updateCounts() {
        board.querySelectorAll('.kanban-col').forEach(col => {
            const n = col.querySelectorAll('.col-cards .card').length;
            const c = col.querySelector('.col-count');
            if (c) c.textContent = String(n);
        });
    }

    function cssEscape(s) {
        if (window.CSS && CSS.escape) return CSS.escape(s);
        return String(s).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
    }

    // Klick på kort → öppna modal
    const modal = document.getElementById('taskModal');
    if (modal) {
        board.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', (e) => {
                if (justDragged) return;
                if (e.target.closest('audio, video, a, button')) return;
                openModal(card);
            });
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(card); }
            });
        });
        modal.addEventListener('click', (e) => {
            if (e.target.matches('[data-close]')) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.hidden) closeModal();
        });
    }

    function openModal(card) {
        if (!modal) return;
        modal.querySelector('#taskModalTitle').textContent = card.dataset.rubrik || '';

        const status = modal.querySelector('[data-field="status"]');
        const statusClass = card.className.split(/\s+/).find(c => c.startsWith('status-')) || '';
        status.className = 'status-pill ' + statusClass;
        status.textContent = card.dataset.status || '';

        setChip(modal.querySelector('[data-field="kategori"]'), card.dataset.kategori);
        setChip(modal.querySelector('[data-field="plats"]'), card.dataset.plats ? '📍 ' + card.dataset.plats : '');

        modal.querySelector('[data-field="tid_kvar"]').textContent = card.dataset.tidKvar || '';

        const ds = card.dataset.datumStart, de = card.dataset.datumSlut;
        const datesParts = [];
        if (ds) datesParts.push('Start: ' + ds);
        if (de) datesParts.push('Slut: ' + de);
        modal.querySelector('[data-field="datum"]').textContent = datesParts.join(' · ');

        const textEl = modal.querySelector('[data-field="text"]');
        textEl.textContent = card.dataset.text || '';

        const mediaEl = modal.querySelector('[data-field="media"]');
        mediaEl.innerHTML = '';
        appendMedia(mediaEl, card.dataset.bilder, 'img');
        appendMedia(mediaEl, card.dataset.ljud,  'audio');
        appendMedia(mediaEl, card.dataset.film,  'video');

        const editBtn = modal.querySelector('[data-field="edit"]');
        if (card.dataset.editUrl) {
            editBtn.href = card.dataset.editUrl;
            editBtn.hidden = false;
        } else {
            editBtn.hidden = true;
        }

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        if (!modal) return;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        const mediaEl = modal.querySelector('[data-field="media"]');
        if (mediaEl) mediaEl.querySelectorAll('audio, video').forEach(m => m.pause());
    }

    function setChip(el, text) {
        if (!el) return;
        if (text) { el.textContent = text; el.hidden = false; }
        else { el.textContent = ''; el.hidden = true; }
    }

    function appendMedia(host, json, kind) {
        if (!json) return;
        let arr;
        try { arr = JSON.parse(json); } catch { return; }
        if (!Array.isArray(arr) || !arr.length) return;
        arr.forEach(src => {
            let el;
            if (kind === 'img') {
                el = document.createElement('img');
                el.src = src; el.alt = '';
            } else {
                el = document.createElement(kind);
                el.controls = true; el.src = src; el.preload = 'metadata';
            }
            host.appendChild(el);
        });
    }

    // Sök i tavlan
    window.filterCards = function (q) {
        q = (q || '').trim().toLowerCase();
        document.querySelectorAll('.card').forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = (q === '' || text.indexOf(q) !== -1) ? '' : 'none';
        });
        updateCounts();
    };
})();
