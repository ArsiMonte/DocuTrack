// assets/js/main.js - DocuTrack Frontend Enhancements

document.addEventListener('DOMContentLoaded', () => {
    // 1. Dynamic Fee Calculation for Document Request Form
    const docSelect = document.getElementById('document_type_id');
    const copiesInput = document.getElementById('copies');
    const totalDisplay = document.getElementById('total_amount_display');
    const totalHidden = document.getElementById('total_amount');
    const requirementsBox = document.getElementById('doc_requirements_box');
    const requirementsText = document.getElementById('doc_requirements_text');

    function calculateTotal() {
        if (!docSelect || !copiesInput || !totalDisplay) return;

        const selectedOption = docSelect.options[docSelect.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            totalDisplay.textContent = '₱0.00';
            if (totalHidden) totalHidden.value = '0.00';
            if (requirementsBox) requirementsBox.style.display = 'none';
            return;
        }

        const price = parseFloat(selectedOption.getAttribute('data-price') || '0');
        const copies = parseInt(copiesInput.value, 10) || 1;
        const reqs = selectedOption.getAttribute('data-reqs') || 'None';

        const total = price * copies;
        totalDisplay.textContent = '₱' + total.toFixed(2);
        if (totalHidden) totalHidden.value = total.toFixed(2);

        if (requirementsBox && requirementsText) {
            requirementsText.textContent = reqs;
            requirementsBox.style.display = 'block';
        }
    }

    if (docSelect) {
        docSelect.addEventListener('change', calculateTotal);
    }
    if (copiesInput) {
        copiesInput.addEventListener('input', calculateTotal);
        calculateTotal();
    }

    // 2. Real-time Filter/Search in Tables
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // 3. Confirm Deletion / Critical Actions
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const message = btn.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
