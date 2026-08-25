document.addEventListener('DOMContentLoaded', () => {
    const liveFilter = document.getElementById('liveFilter');
    const usersTable = document.getElementById('usersTable');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const rows = () => Array.from(usersTable.querySelectorAll('tbody tr'));

    liveFilter.addEventListener('input', () => {
        const term = liveFilter.value.toLowerCase().trim();
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
                const aVal = a.children[index].textContent.trim();
                const bVal = b.children[index].textContent.trim();
                return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            });

            const tbody = usersTable.querySelector('tbody');
            sortedRows.forEach(row => tbody.appendChild(row));
        });
    });

    document.querySelectorAll('.role-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            const select = form.querySelector('select[name="role"]');
            const newRole = select.value;
            const currentRole = form.dataset.currentRole;
            const username = form.dataset.username;

            if (newRole === currentRole) {
                e.preventDefault();
                return;
            }

            const msg = `Change ${username}'s role from "${currentRole}" to "${newRole}"?`;
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});