document.addEventListener('DOMContentLoaded', () => {
    const dueSearch = document.getElementById('dueSearch');
    const duesTable = document.getElementById('duesTable');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const rows = () => Array.from(duesTable.querySelectorAll('tbody tr'));

    dueSearch.addEventListener('input', () => {
        const term = dueSearch.value.toLowerCase().trim();
        let visibleCount = 0;

        rows().forEach(row => {
            const matches = row.textContent.toLowerCase().includes(term);
            row.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        noResultsMsg.style.display = (visibleCount === 0 && term !== '') ? '' : 'none';
    });

    let sortDirections = {};

    document.querySelectorAll('.sortable').forEach((header, index) => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const type = header.dataset.type;
            const asc = sortDirections[index] = !sortDirections[index];

            const sortedRows = rows().sort((a, b) => {
                let aVal = a.children[index].textContent.trim();
                let bVal = b.children[index].textContent.trim();

                if (type === 'number') {
                    aVal = parseFloat(aVal.replace(/,/g, '')) || 0;
                    bVal = parseFloat(bVal.replace(/,/g, '')) || 0;
                    return asc ? aVal - bVal : bVal - aVal;
                }
                return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            });

            const tbody = duesTable.querySelector('tbody');
            sortedRows.forEach(row => tbody.appendChild(row));
        });
    });

    document.querySelectorAll('.toggle-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            const msg = form.dataset.confirmMsg;
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    const dueForm = document.getElementById('dueForm');
    const dueSubmitBtn = document.getElementById('dueSubmitBtn');
    dueForm.addEventListener('submit', () => {
        dueSubmitBtn.disabled = true;
        dueSubmitBtn.innerHTML = '<span class="spinner"></span>Saving...';
    });
});