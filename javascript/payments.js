document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.stat-value[data-count]').forEach(el => {
        const target = parseFloat(el.dataset.count) || 0;
        const decimals = parseInt(el.dataset.decimals || '0', 10);
        const duration = 800;
        const startTime = performance.now();

        function tick(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = eased * target;
            el.textContent = decimals > 0
                ? value.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
                : Math.round(value).toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(tick);
            } else {
                el.textContent = decimals > 0
                    ? target.toLocaleString(undefined, { minimumFractionDigits: decimals, maximumFractionDigits: decimals })
                    : target.toLocaleString();
            }
        }
        requestAnimationFrame(tick);
    });

    const liveFilter = document.getElementById('liveFilter');
    const paymentsTable = document.getElementById('paymentsTable');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const rows = () => Array.from(paymentsTable.querySelectorAll('tbody tr'));

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
                const aCell = a.children[index];
                const bCell = b.children[index];

                if (type === 'number' || type === 'date') {
                    const aVal = parseFloat(aCell.dataset.sortValue ?? aCell.textContent.replace(/,/g, '')) || 0;
                    const bVal = parseFloat(bCell.dataset.sortValue ?? bCell.textContent.replace(/,/g, '')) || 0;
                    return asc ? aVal - bVal : bVal - aVal;
                }

                const aVal = aCell.textContent.trim();
                const bVal = bCell.textContent.trim();
                return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            });

            const tbody = paymentsTable.querySelector('tbody');
            sortedRows.forEach(row => tbody.appendChild(row));
        });
    });
});