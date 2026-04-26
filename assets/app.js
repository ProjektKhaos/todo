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
